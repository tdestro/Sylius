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

namespace Sylius\Component\Core\Shipping\Calculator;

use Sylius\Component\Core\Exception\MissingChannelConfigurationException;
use Sylius\Component\Core\Model\ShipmentInterface;
use Sylius\Component\Shipping\Calculator\CalculatorInterface;
use Sylius\Component\Shipping\Model\ShipmentInterface as BaseShipmentInterface;
use Webmozart\Assert\Assert;
use Symfony\Component\VarDumper\VarDumper;

final class UPSCalculator implements CalculatorInterface
{
    /**
     * {@inheritdoc}
     *
     * @throws MissingChannelConfigurationException, Exception
     */
    public function calculate(BaseShipmentInterface $subject, array $configuration): int
    {


        Assert::isInstanceOf($subject, ShipmentInterface::class);

        $channelCode = $subject->getOrder()->getChannel()->getCode();

        if (!isset($configuration[$channelCode])) {
            throw new MissingChannelConfigurationException(sprintf(
                'Channel %s has no amount defined for shipping method %s',
                $subject->getOrder()->getChannel()->getName(),
                $subject->getMethod()->getName()
            ));
        }

        $rate = new \Ups\Rate(
            $_ENV['UPSAccessLicenseNumber'],
            $_ENV['UPSUserId'],
            $_ENV['UPSPassword']
        );


        /*Valid domestic values:
	• 14 = UPS Next Day Air Early
	• 01 = Next Day Air
	• 13 = Next Day Air Saver
	• 59 = 2nd Day Air A.M.
	• 02 = 2nd Day Air
	• 12 = 3 Day Select
	• 03 = Ground
	Valid international values:
	• 11= Standard
	• 07 = Worldwide Express
	• 54 = Worldwide Express Plus
	• 08 = Worldwide Expedited
	• 65 = Saver
	• 96 = UPS Worldwide Express Freight
	Required for Rating and Ignored for Shopping.
	Valid Poland to Poland Same Day values:
	• 82 = UPS Today Standard
	• 83 = UPS Today Dedicated Courier
	• 84 = UPS Today Intercity
	• 85 = UPS Today Express
	• 86 = UPS Today Express Saver,
	• 70 =UPS Access Point Economy
	*/

        $service = $configuration[$channelCode]['service'];


        $shippingAddress = $subject->getOrder()->getShippingAddress();

        if($shippingAddress == Null) {
            return 0;
        }

        //$orderCurrencyCode = $subject->getOrder()->getCurrencyCode();

        //$firstName = $shippingAddress->getFirstName();
        //$lastName = $shippingAddress->getLastName();
        //$phoneNumber = $shippingAddress->getPhoneNumber();
        //$company = $shippingAddress->getCompany();
        $countryCode = $shippingAddress->getCountryCode();
        //$provinceCode = $shippingAddress->getProvinceCode();
        //$provinceName = $shippingAddress->getProvinceName();
        $street = $shippingAddress->getStreet();
        //$city = $shippingAddress->getCity();
        $postcode = $shippingAddress->getPostcode();

        $shipment = new \Ups\Entity\Shipment();
        $shipment->getService()->setCode($service);



        // ItemsTotal is stored as an Int.
        // InvoiceLines, this is for customs international bullshit.
        /*
        $itemsTotalAsFloat = floatval($subject->getOrder()->getItemsTotal());
        $itemsTotalAsInt = intval($itemsTotalAsFloat / 100.00);




        $invoiceLineTotal = new \Ups\Entity\InvoiceLineTotal;
        $invoiceLineTotal->setMonetaryValue($itemsTotalAsInt); // Sum of the monetary value of your packages
        $invoiceLineTotal->setCurrencyCode($subject->getOrder()->getCurrencyCode());
        $shipment->setInvoiceLineTotal($invoiceLineTotal);
        */


        // we are told this is for international shipping.
        // This may have a bug in that some territories may be considered domestic?
        if ($countryCode != 'US'){
            $shipmentServiceOptions = new \Ups\Entity\ShipmentServiceOptions();
            $deliveryConfirmation = new \Ups\Entity\DeliveryConfirmation();
            $deliveryConfirmation->setDcisType(\Ups\Entity\DeliveryConfirmation::DELIVERY_CONFIRMATION_SIGNATURE_REQUIRED);
            $shipmentServiceOptions->setDeliveryConfirmation($deliveryConfirmation);
            $shipment->setShipmentServiceOptions($shipmentServiceOptions);
        }

        $shipperAddress = $shipment->getShipper()->getAddress();
        $shipperAddress->setPostalCode('47909');

        $address = new \Ups\Entity\Address();
        $address->setPostalCode('47909');
        $address->setAddressLine1('1905 Mulligan Way Apt D');

        $shipFrom = new \Ups\Entity\ShipFrom();
        $shipFrom->setAddress($address);
        $shipment->setShipFrom($shipFrom);

        $shipTo = $shipment->getShipTo();
        $shipToAddress = $shipTo->getAddress();
        $shipToAddress->setPostalCode($postcode);
        $shipToAddress->setAddressLine1($street);
        $shipToAddress->setCountryCode($countryCode);

        $items = $subject->getOrder()->getItems();

        foreach ($items as $item){
            $itemUnits = $item->getUnits();
            foreach ($itemUnits as $itemUnit) {
                $depth = $itemUnit->getShippable()->getShippingDepth();
                $height = $itemUnit->getShippable()->getShippingHeight();
                $width = $itemUnit->getShippable()->getShippingWidth();
                $weight = $itemUnit->getShippable()->getShippingWeight();

                $itemUnitTotalAsFloat = floatval($itemUnit->getTotal());
                $itemUnitTotalAsInt = intval($itemUnitTotalAsFloat / 100.00);

                $package = new \Ups\Entity\Package();

                $packageServiceOptions = new \Ups\Entity\PackageServiceOptions();
                $insuredValue = new \Ups\Entity\InsuredValue();
                $insuredValue->setCurrencyCode($subject->getOrder()->getCurrencyCode());
                $insuredValue->setMonetaryValue($itemUnitTotalAsInt);
                $packageServiceOptions->setInsuredValue($insuredValue);

                // This is for domestic shipments only and will probably blow up on international.
                if ($countryCode == 'US') {
                    $deliveryConfirmation = new \Ups\Entity\DeliveryConfirmation();
                    $deliveryConfirmation->setDcisType(\Ups\Entity\DeliveryConfirmation::DELIVERY_CONFIRMATION_SIGNATURE_REQUIRED);
                    $packageServiceOptions->setDeliveryConfirmation($deliveryConfirmation);
                }
                $package->setPackageServiceOptions($packageServiceOptions);

                /*
        packagingtype Valid values:
        00 = UNKNOWN
        01 = UPS Letter
        02 = Package
        03 = Tube
        04 = Pak
        21 = Express Box
        24 = 25KG Box
        25 = 10KG Box
        30 = Pallet
        2a = Small Express Box
        2b = Medium Express Box
        2c = Large Express Box
        */
                $packagingType = new \Ups\Entity\PackagingType();
                $packagingType->setCode('02');
                $package->setPackagingType($packagingType);
                $package->getPackageWeight()->setWeight($weight);

                $dimensions = new \Ups\Entity\Dimensions();
                $dimensions->setHeight($height);
                $dimensions->setWidth($width);
                $dimensions->setLength($depth);
                $unitOfMeasurement = new \Ups\Entity\UnitOfMeasurement;
                $unitOfMeasurement->setCode(\Ups\Entity\UnitOfMeasurement::UOM_IN);
                $dimensions->setUnitOfMeasurement($unitOfMeasurement);
                $package->setDimensions($dimensions);
                $shipment->addPackage($package);

            }
        }

        /*
Rate Type indicates how UPS retrieves your packages. If you take your shipments to your local UPS store,
have a scheduled daily pickup or schedule a one time pick up, it will affect the rate you are quoted.
Valid values:
• 01- Daily Pickup
• 03 - Customer Counter
• 06 - One Time Pickup
• 19 - Letter Center
• 20 - Air Service Center
*/
        $packagingType = new \Ups\Entity\PickupType();
        $packagingType->setCode($configuration[$channelCode]['ratetype']);

        $rateRequest = new \Ups\Entity\RateRequest();
        $rateRequest->setPickupType($packagingType);
        $rateRequest->setShipment($shipment);
        dump($rateRequest);
        $ratedShipment = $rate->getRate($rateRequest)->RatedShipment;
        dump($ratedShipment);
        $firstRatedShipment = reset($ratedShipment);

        if ($firstRatedShipment === false) {
            throw new Exception('Failure (0): Unknown error', 0);
        }

        $monetaryValueAsFloat = floatval($firstRatedShipment->TotalCharges->MonetaryValue);
        $currencyCode = $firstRatedShipment->TotalCharges->CurrencyCode;

        if ($monetaryValueAsFloat < 0.0) {
            throw new Exception('Failure (0): Unknown error', 0);
        }

        $monetaryValueAsInt = intval($monetaryValueAsFloat * 100.00);

        //
        // The ups rates api gives us the shipping cost in the currency of the
        // shipping country and must be converted.
        //

        if ($currencyCode != "USD"){
            throw new Exception('Failure (0): UPS Responded with currency that wasn\'t USD.', 0);
        }
        return (int)$monetaryValueAsInt;
    }

    /**
     * {@inheritdoc}
     */
    public function getType(): string
    {
        return 'ups';
    }
}
