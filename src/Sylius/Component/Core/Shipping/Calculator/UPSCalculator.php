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
use Sylius\Component\Currency\Converter;


// https://github.com/florianv/swap
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
        $orderCurrencyCode = $subject->getOrder()->getCurrencyCode();

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

        $units = $subject->getUnits();

        foreach ($units as $unit) {
            $depth = $unit->getShippable()->getShippingDepth();
            $height = $unit->getShippable()->getShippingHeight();
            $width = $unit->getShippable()->getShippingWidth();
            $weight = $unit->getShippable()->getShippingWeight();

            $package = new \Ups\Entity\Package();

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
        $ratedShipment = $rate->getRate($rateRequest)->RatedShipment;
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


        dump($shipment);
        dump($rate->getRate($shipment)->RatedShipment);
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
