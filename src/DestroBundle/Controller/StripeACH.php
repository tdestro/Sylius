<?php

namespace DestroBundle\Controller;

use DestroBundle\Form\Type\MicroDepositsType;
use DestroBundle\Form\Type\StripeACHType;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sylius\Component\Core\Repository\PaymentMethodRepositoryInterface;
use Sylius\Component\Mailer\Sender\SenderInterface;
use Stripe\Customer;
use Stripe\Stripe;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Stripe\Error\Base;

class StripeACH extends Controller
{
    /**
     * @var SenderInterface
     */
    private $emailSender;

    /**
     * @var PaymentMethodRepositoryInterface
     */
    private $paymentMethodRepository;

    /**
     * @var String
     */
    private $publishableKey;

    /**
     * @var EngineInterface
     */
    private $templatingEngine;

    /**
     * @param PaymentMethodRepositoryInterface $paymentMethodRepository
     * @param SenderInterface $emailSender
     * @param EngineInterface $templatingEngine
     */
    public function __construct(PaymentMethodRepositoryInterface $paymentMethodRepository, SenderInterface $emailSender, EngineInterface $templatingEngine)
    {
        $this->templatingEngine = $templatingEngine;
        $this->paymentMethodRepository = $paymentMethodRepository;
        $this->emailSender = $emailSender;

        //
        // Get the stripe publishable key by the code.
        //
        $returnedPaymentMethods = $this->paymentMethodRepository->createQueryBuilder('o')
            ->andWhere('o.code = :code')
            ->setParameter('code', 'stripe_ach')
            ->getQuery()
            ->getResult();

        $this->publishableKey = reset($returnedPaymentMethods)->getGatewayConfig()->getConfig()["publishable_key"];
        $secretKey = reset($returnedPaymentMethods)->getGatewayConfig()->getConfig()["secret_key"];
        Stripe::setApiKey($secretKey);
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function paymentMethodsAction(Request $request): Response
    {

        if (!$this->container->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            throw new AccessDeniedException('You have to be registered user to access this section.');
        }

        $user = $this->container->get('security.token_storage')->getToken()->getUser();
        $template = $request->attributes->get('template');
        $confirmTemplate = $request->attributes->get('confirm_template');
        $microDepositsTemplate = $request->attributes->get('micro_deposits_template');

        $form = $this->get('form.factory')->create(StripeACHType::class);

        $stripeCustomerID = $user->getStripeCustomer();
        $entityManager = $this->getDoctrine()->getManager();

        if (empty($stripeCustomerID)) {
            if ($request->request->has("sylius_stripe_ach") && array_key_exists("stripe_result", $request->request->get("sylius_stripe_ach"))) {
                $stripeResult = json_decode(urldecode($request->request->get("sylius_stripe_ach")["stripe_result"]));
                // Create stripe customer with new payment source.

                try {
                    $cu = Customer::create(array(
                        "source" => $stripeResult->token->id,
                        "description" => "Example customer"
                    ));
                    $cu->save();
                } catch (Exception | Base $e) {
                    return $this->templatingEngine->renderResponse($template, array('form' => $form->createView(), 'publishableKey' => $this->publishableKey, 'error' => $e->getMessage()));
                }
                //
                // After customer is added to stripe, add to db
                //
                $stripeCustomerID = $cu->id;

                try {
                    $user->setStripeCustomer($stripeCustomerID);
                    $entityManager->persist($user);
                    $entityManager->flush();
                } catch (Exception | Base $e) {
                    //
                    // Attempt to roll back the creation of a customer.
                    //
                    try {
                        $cu->delete();
                    } catch (Exception | Base $e0) {
                        return $this->templatingEngine->renderResponse($template, array('form' => $form->createView(), 'publishableKey' => $this->publishableKey, 'error' => $e0->getMessage() . " " . $e->getMessage()));
                    }
                    return $this->templatingEngine->renderResponse($template, array('form' => $form->createView(), 'publishableKey' => $this->publishableKey, 'error' => $e->getMessage()));
                }

                $this->emailSender->send('confirm_deposits', [$user->getEmail()], ['firstName' => $user->getCustomer()->getFullName(), 'last4' => $stripeResult->token->bank_account->last4, 'bank_name' => $stripeResult->token->bank_account->bank_name]);
                return $this->templatingEngine->renderResponse($confirmTemplate, ['last4' => $stripeResult->token->bank_account->last4, 'bank_name' => $stripeResult->token->bank_account->bank_name]);
            } else {
                return $this->templatingEngine->renderResponse($template, ['form' => $form->createView(), 'publishableKey' => $this->publishableKey]);
            }
        } else {
            try {
                $cu = Customer::retrieve($stripeCustomerID);
            } catch (Exception | Base $e) {
                return $this->templatingEngine->renderResponse($template, ['form' => $form->createView(), 'publishableKey' => $this->publishableKey]);
            }

            if ($request->request->has("sylius_stripe_ach") && array_key_exists("stripe_result", $request->request->get("sylius_stripe_ach"))) {
                $stripeResult = json_decode(urldecode($request->request->get("sylius_stripe_ach")["stripe_result"]));
                // update stripe customer with new payment source
                $cu->source = $stripeResult->token->id;
                $cu->save();

                $this->emailSender->send('confirm_deposits', [$user->getEmail()], ['firstName' => $user->getCustomer()->getFullName(), 'last4' => $stripeResult->token->bank_account->last4, 'bank_name' => $stripeResult->token->bank_account->bank_name]);
                return $this->templatingEngine->renderResponse($confirmTemplate, ['last4' => $stripeResult->token->bank_account->last4, 'bank_name' => $stripeResult->token->bank_account->bank_name]);
            } else {
                $sources = $cu->sources->all();
                foreach ($sources->data as $source) {
                    if ($source->className() == "bankaccount") {
                        $status = $source->status;

                        if ($status == "new" || $status == "validated" || $status == "verification_failed") {
                            $microDepositsForm = $this->get('form.factory')->create(MicroDepositsType::class);

                            if ($request->request->has("sylius_micro_deposits") && array_key_exists("first_deposit", $request->request->get("sylius_micro_deposits"))) {
                                $microDepositsForm->handleRequest($request);
                                if ($microDepositsForm->isSubmitted() && $microDepositsForm->isValid()) {

                                    $data = $microDepositsForm->getData();
                                    try {
                                        $source->verify(array('amounts' => array($data["first_deposit"], $data["second_deposit"])));
                                    } catch (Exception | Base $e) {
                                        return $this->templatingEngine->renderResponse(
                                            $microDepositsTemplate,
                                            ['form' => $microDepositsForm->createView(), 'publishableKey' => $this->publishableKey, 'error' => $e->getMessage()]);
                                    }
                                    return $this->templatingEngine->renderResponse($template, ['form' => $form->createView(), 'publishableKey' => $this->publishableKey, 'last4' => $source->last4, 'bank_name' => $source->bank_name]);
                                } else {
                                    return $this->templatingEngine->renderResponse(
                                        $microDepositsTemplate,
                                        ['form' => $microDepositsForm->createView()]
                                    );
                                }
                            } else {
                                return $this->templatingEngine->renderResponse(
                                    $microDepositsTemplate,
                                    ['form' => $microDepositsForm->createView()]
                                );
                            }
                        } else if ($status == "verified") {
                            return $this->templatingEngine->renderResponse($template, ['form' => $form->createView(), 'publishableKey' => $this->publishableKey, 'last4' => $source->last4, 'bank_name' => $source->bank_name]);
                        } else if ($status == "errored") {
                            return $this->templatingEngine->renderResponse($template, ['form' => $form->createView(), 'publishableKey' => $this->publishableKey, 'error' => 'Status is Errored.']);
                        }
                        break;
                    }
                }
                return $this->templatingEngine->renderResponse($template, ['form' => $form->createView(), 'publishableKey' => $this->publishableKey, 'error' => 'No bank account is associated with this customer but the CustomerID exists in the database.']);
            }
        }
    }

    public function displayAction(Request $request): Response
    {
        $template = '@DestroBundle/Resources/views/_payment_methods_checkout.html.twig';

        if (!$this->container->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            return $this->templatingEngine->renderResponse($template, ['ach_status' => 'unauthorized']);
        }

        $user = $this->container->get('security.token_storage')->getToken()->getUser();

        $stripeCustomerID = $user->getStripeCustomer();

        if (empty($stripeCustomerID)) {
            return $this->templatingEngine->renderResponse($template, ['ach_status' => 'noStripeCustomerID']);
        } else {
            try {
                $cu = Customer::retrieve($stripeCustomerID);
            } catch (Exception | Base $e) {
                return $this->templatingEngine->renderResponse($template, ['ach_status' => 'errorOnRetrieve']);
            }
            $sources = $cu->sources->all();
            foreach ($sources->data as $source) {
                if ($source->className() == "bankaccount") {
                    $status = $source->status;
                    if ($status == "new" || $status == "validated" || $status == "verification_failed" || $status == "errored") {
                        return $this->templatingEngine->renderResponse($template, ['ach_status' => 'notVerified']);
                    } else if ($status == "verified") {
                        /*\Stripe\Charge::create(array(
  "amount" => 1500,
  "currency" => "usd",
  "customer" => $customer_id // Previously stored, then retrieved
));*/
                        return $this->templatingEngine->renderResponse($template, ['ach_status' => 'verified', 'last4' => $source->last4, 'bank_name' => $source->bank_name]);
                    }
                    break;
                }
            }


        }
        return $this->templatingEngine->renderResponse($template, ['ach_status' => 'noSourceInCustomer']);
    }

}