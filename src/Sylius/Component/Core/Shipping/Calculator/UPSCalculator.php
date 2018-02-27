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

final class UPSCalculator implements CalculatorInterface
{
    /**
     * {@inheritdoc}
     *
     * @throws MissingChannelConfigurationException
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

        dump($subject);

        $rate = new \Ups\Rate(
            $_ENV['UPSAccessLicenseNumber'],
            $_ENV['UPSUserId'],
            $_ENV['UPSPassword']
        );

        $service = \Ups\Entity\Service::S_GROUND;

        $shippingAddress = $subject->getOrder()->getShippingAddress();
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
        dump($shipment);
        dump($rate->getRate($shipment));


        return (int) 900;
    }

    /**
     * {@inheritdoc}
     */
    public function getType(): string
    {
        return 'ups';
    }
}
