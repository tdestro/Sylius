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

namespace Sylius\Bundle\CoreBundle\Form\Type\Checkout;

use Sylius\Bundle\PaymentBundle\Form\Type\PaymentMethodChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

final class PaymentType extends AbstractType
{
    /**
     * @var string
     */
    private $dataClass;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @var AuthorizationCheckerInterface
     */
    private $authorizationChecker;

    /**
     * @param string $dataClass
     * @param TokenStorageInterface $tokenStorage
     * @param AuthorizationCheckerInterface $authorizationChecker
     */
    public function __construct(string $dataClass, TokenStorageInterface $tokenStorage, AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->dataClass = $dataClass;
        $this->tokenStorage = $tokenStorage;
        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event): void {
            $form = $event->getForm();
            $payment = $event->getData();

            $form->add('method', PaymentMethodChoiceType::class, [
                'label' => 'sylius.form.checkout.payment_method',
                'subject' => $payment,
                'expanded' => true,
            ])->add('stripeToken', HiddenType::class, [
                'mapped' => false,
                'required' => false
            ]);

        })->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event): void {
            $form = $event->getForm();
            $payment = $event->getData();

            if($payment['method'] ==  'stripe_ach'){

                if (!$this->authorizationChecker->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
                    throw new AccessDeniedException('You have to be registered user to access this section.');
                }

                $user = $this->tokenStorage->getToken()->getUser();
                $stripeCustomerID = $user->getStripeCustomer();

                if (empty($stripeCustomerID)) {
                    throw new AccessDeniedException('No customer id on the user .');
                } else {
                    if (!isset($payment['details'])) $payment['details'] = array();
                    $payment['details']['setcustomer'] = $stripeCustomerID;

                    $form->add('details', HiddenType::class,[
                        'mapped' => true,
                    ]);

                    $event->setData($payment);
                }

            } else if ($payment['method'] == 'stripe_js_code'){
                if (!isset($payment['stripeToken'])) return;
                if (!isset($payment['details'])) $payment['details'] = array();

                $payment['details']['setcard'] = $payment['stripeToken'];

                $form->add('details', HiddenType::class,[
                    'mapped' => true,
                ]);

                $event->setData($payment);
            }
        });
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => $this->dataClass,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix(): string
    {
        return 'sylius_checkout_payment';
    }
}
