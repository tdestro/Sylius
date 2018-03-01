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

namespace Sylius\Bundle\ShippingBundle\Form\Type\Calculator;

use Sylius\Bundle\MoneyBundle\Form\Type\MoneyType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

final class UPSConfigurationType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('service', ChoiceType::class, array(
            'label' => 'sylius.form.shipping_calculator.ups_configuration.service',
            'choices' => array(
                'UPS Next Day Air Early' => '14',
                'UPS Next Day Air' => '01',
                'UPS Next Day Air Saver' => '13',
                'UPS 2nd Day Air A.M.' => '59',
                'UPS 2nd Day Air' => '02',
                'UPS 3 Day Select' => '12',
                'UPS Ground' => '03',
                // Valid international values:
                'UPS Standard' => '11',
                'UPS Worldwide Express' => '07',
                'UPS Worldwide Express Plus' => '54',
                'UPS Worldwide Expedited' => '08',
                'UPS Saver' => '65',
                'UPS Worldwide Express Freight' => '96',
                // Required for Rating and Ignored for Shopping.
                // Valid Poland to Poland Same Day
                'UPS Today Standard' => '82',
                'UPS Today Dedicated Courier' => '83',
                'UPS Today Intercity' => '84',
                'UPS Today Express' => '85',
                'UPS Today Express Saver' => '86',
                'UPS Access Point Economy' => '70',
            ),
        ));

        $builder->add('ratetype', ChoiceType::class, array(
            'label' => 'sylius.form.shipping_calculator.ups_configuration.ratetype',
            'choices' => array(
                'Daily Pickup' => '01',
                'Customer Counter' => '03',
                'One Time Pickup' => '06',
                'Letter Center' => '19',
                'Air Service Center' => '20',
            ),
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'data_class' => null,
            ])
            ->setRequired('currency')
            ->setAllowedTypes('currency', 'string');
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix(): string
    {
        return 'sylius_shipping_calculator_ups';
    }
}
