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

namespace Sylius\Bundle\PayumBundle\Action;

use Payum\Core\Action\GatewayAwareAction;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Model\Payment as PayumPayment;
use Payum\Core\Request\Capture;
use Payum\Core\Request\Convert;
use Sylius\Bundle\PayumBundle\Provider\PaymentDescriptionProviderInterface;
use Sylius\Bundle\PayumBundle\Request\GetStatus;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PaymentInterface as SyliusPaymentInterface;

final class CapturePaymentAction extends GatewayAwareAction
{
    /**
     * @var PaymentDescriptionProviderInterface
     */
    private $paymentDescriptionProvider;

    /**
     * @param PaymentDescriptionProviderInterface $paymentDescriptionProvider
     */
    public function __construct(PaymentDescriptionProviderInterface $paymentDescriptionProvider)
    {
        $this->paymentDescriptionProvider = $paymentDescriptionProvider;
    }

    /**
     * {@inheritdoc}
     *
     * @param Capture $request
     */
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        /** @var SyliusPaymentInterface $payment */
        $payment = $request->getModel();

        /** @var OrderInterface $order */
        $order = $payment->getOrder();

        $this->gateway->execute($status = new GetStatus($payment));
        if ($status->isNew()) {
            try {
                $this->gateway->execute($convert = new Convert($payment, 'array', $request->getToken()));
                $payment->setDetails($convert->getResult());
            } catch (RequestNotSupportedException $e) {
                $totalAmount = $order->getTotal();
                $payumPayment = new PayumPayment();
                $payumPayment->setNumber($order->getNumber());
                $payumPayment->setTotalAmount($totalAmount);
                $payumPayment->setCurrencyCode($order->getCurrencyCode());
                $payumPayment->setClientEmail($order->getCustomer()->getEmail());
                $payumPayment->setClientId($order->getCustomer()->getId());
                $payumPayment->setDescription($this->paymentDescriptionProvider->getPaymentDescription($payment));


                // This allows for us to put the stripe elements form in basically anywhere we want and
                // bypass the ugly form in the payum integration.
                $details = $payment->getDetails();

                if (isset($details['setcard'])) {
                    $details['card'] = $details['setcard'];
                    unset($details['setcard']);
                } else if (isset($details['setcustomer'])) {
                    $details['customer'] = $details['setcustomer'];
                    unset($details['setcustomer']);
                }
                $details['receipt_email'] = $order->getCustomer()->getEmail();

                $shippingAddress = $order->getShippingAddress();
                $details['shipping'] = array(
                    "address" => array(
                        "line1" => $shippingAddress->getStreet(),
                        "city" => $shippingAddress->getCity(),
                        "country" => $shippingAddress->getCountryCode(),
                        "line2" => "",
                        "postal_code" => $shippingAddress->getPostcode(),
                        "state" => $shippingAddress->getProvinceName()
                    ),
                    "name" => $shippingAddress->getFullName(),
                    "phone" => $shippingAddress->getPhoneNumber()
                );


                $payumPayment->setDetails($details);

                $this->gateway->execute($convert = new Convert($payumPayment, 'array', $request->getToken()));
                $payment->setDetails($convert->getResult());
            }
        }

        $details = ArrayObject::ensureArrayObject($payment->getDetails());

        try {
            $request->setModel($details);
            $this->gateway->execute($request);
        } finally {
            $payment->setDetails((array)$details);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function supports($request): bool
    {
        return
            $request instanceof Capture &&
            $request->getModel() instanceof SyliusPaymentInterface;
    }
}
