<?php

/*
 * This file is part of the Sylius package.
 *
 * (c) Paweł Jędrzejewski
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace DestroBundle\Controller;

use Sylius\Bundle\CoreBundle\Form\Type\ContactType;
use Sylius\Bundle\ShopBundle\EmailManager\ContactEmailManagerInterface;
use Sylius\Component\Channel\Context\ChannelContextInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Customer\Context\CustomerContextInterface;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Routing\RouterInterface;
use Webmozart\Assert\Assert;
use DestroBundle\Entity\Contact;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

final class ContactController extends Controller
{
    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var FormFactoryInterface
     */
    private $formFactory;

    /**
     * @var EngineInterface
     */
    private $templatingEngine;

    /**
     * @var ChannelContextInterface
     */
    private $channelContext;

    /**
     * @var CustomerContextInterface
     */
    private $customerContext;

    /**
     * @var ContactEmailManagerInterface
     */
    private $contactEmailManager;

    /**
     * @param RouterInterface $router
     * @param FormFactoryInterface $formFactory
     * @param EngineInterface $templatingEngine
     * @param ChannelContextInterface $channelContext
     * @param CustomerContextInterface $customerContext
     * @param ContactEmailManagerInterface $contactEmailManager
     */
    public function __construct(
        RouterInterface $router,
        FormFactoryInterface $formFactory,
        EngineInterface $templatingEngine,
        ChannelContextInterface $channelContext,
        CustomerContextInterface $customerContext,
        ContactEmailManagerInterface $contactEmailManager
    ) {
        $this->router = $router;
        $this->formFactory = $formFactory;
        $this->templatingEngine = $templatingEngine;
        $this->channelContext = $channelContext;
        $this->customerContext = $customerContext;
        $this->contactEmailManager = $contactEmailManager;
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function requestAction(Request $request): Response
    {
        $formType = $this->getSyliusAttribute($request, 'form', ContactType::class);
        $form = $this->formFactory->create($formType, null, $this->getFormOptions());

        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $data = $form->getData();

            /** @var ChannelInterface $channel */
            $channel = $this->channelContext->getChannel();
            Assert::isInstanceOf($channel, ChannelInterface::class);

            $contactEmail = $channel->getContactEmail();

            if (null === $contactEmail) {
                $errorMessage = $this->getSyliusAttribute(
                    $request,
                    'error_flash',
                    'sylius.contact.request_error'
                );

                /** @var FlashBagInterface $flashBag */
                $flashBag = $request->getSession()->getBag('flashes');
                $flashBag->add('error', $errorMessage);

                return new RedirectResponse($request->headers->get('referer'));
            }

            $contact = new Contact();
            $contact->setEmail($data['email']);
            $contact->setBody($data['message']);
            $em = $this->getDoctrine()->getManager();
            $em->persist($contact);
            $em->flush();

            $this->contactEmailManager->sendContactRequest($data, [$contactEmail]);

            $successMessage = $this->getSyliusAttribute(
                $request,
                'success_flash',
                'sylius.contact.request_success'
            );

            /** @var FlashBagInterface $flashBag */
            $flashBag = $request->getSession()->getBag('flashes');
            $flashBag->add('success', $successMessage);

            $redirectRoute = $this->getSyliusAttribute($request, 'redirect', 'referer');

            return new RedirectResponse($this->router->generate($redirectRoute));
        }

        $template = $this->getSyliusAttribute($request, 'template', '@SyliusShop/Contact/request.html.twig');

        return $this->templatingEngine->renderResponse($template, ['form' => $form->createView()]);
    }

    /**
     * @param Request $request
     * @param string $attributeName
     * @param string|null $default
     *
     * @return string|null
     */
    private function getSyliusAttribute(Request $request, string $attributeName, ?string $default): ?string
    {
        $attributes = $request->attributes->get('_sylius');

        return $attributes[$attributeName] ?? $default;
    }

    /**
     * @return array
     */
    private function getFormOptions(): array
    {
        $customer = $this->customerContext->getCustomer();

        if (null === $customer) {
            return [];
        }

        return ['email' => $customer->getEmail()];
    }
}
