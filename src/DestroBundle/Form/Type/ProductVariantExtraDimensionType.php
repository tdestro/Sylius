<?php

declare(strict_types=1);

namespace DestroBundle\Form\Type;

use Symfony\Component\Validator\Constraints\GreaterThan;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Validator\Constraints\NotBlank;
use Sylius\Bundle\MoneyBundle\Form\Type\MoneyType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Sylius\Component\Core\Model\ProductVariantExtraDimension;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

final class ProductVariantExtraDimensionType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('upsEntity', ChoiceType::class,
            array('required' => true,
                'choices' => array(
                    'Customer Supplied Package' => '02',
                    'UPS Letter: 12.5-15in x 9.5in (31.75-38cm x 24cm)' => '01',
                    'UPS Tube: Triangular tube for rolled papers; 38in x 6in x 6in (96.5cm x 15cm x 15cm)' => '03',
                    /*'UPS PAK: Padded, secure, or watertight boxes for sensitive or regulated items; size varies' => '04',
                    'UPS Express Box' => '21',*/
                    'UPS 25 KG Box: 19.75in x 17.75in x 13.25in (50cm x 45cm x 34cm); weight limit 55lbs/25kg' => '24',
                    'UPS 10 KG Box: 16.5in x 13.25in x 10.75in (42cm x 34cm x 27cm); weight limit 22lbs/10kg' => '25',
                    'Pallet: Cargo secured to a shipping pallet' => '30',
                    'UPS Express Box - Small: 13in x 11in x 2in' => '2a',
                    'UPS Express Box - Medium: 16in x 11in x 3in' => '2b',
                    'UPS Express Box - Large: 18in x 13in x 3in; weight limit 30lbs' => '2c',
                    /*'Flats' => '56',
                    'Parcels' => '57',
                    'BPM' => '58',
                    'First Class' => '59',
                    'Priority' => '60',
                    'Machinables' => '61',
                    'Irregulars' => '62',
                    'Parcel Post' => '63',
                    'BPM Parcel' => '64',
                    'Media Mail' => '65',
                    'BPM Flat' => '66',
                    'Standard Flat' => '67'*/
                ),
                'label' => 'UPS Package type',
                'multiple' => false, 'expanded' => false,))
            ->add('applyToQuantity', NumberType::class, [
                'required' => true,
                'label' => 'Quantity amount to apply this dimension to',
                'invalid_message' => 'Invalid quantity',
                'constraints' => [
                    new NotBlank(['groups' => ['sylius']]),
                    new GreaterThan([
                        'value' => 0,
                        'groups' => ['sylius']
                    ]),
                ],
            ])
            ->add('width', NumberType::class, [
                'required' => false,
                'label' => 'Width (Only used with Customer Supplied Packaging)',
            ])
            ->add('height', NumberType::class, [
                'required' => false,
                'label' => 'Height (Only used with Customer Supplied Packaging)',
            ])
            ->add('depth', NumberType::class, [
                'required' => false,
                'label' => 'Depth (Only used with Customer Supplied Packaging)',
                'invalid_message' => 'sylius.product_variant.depth.invalid',
            ])
            ->add('weight', NumberType::class, [
                'required' => false,
                'label' => 'sylius.form.variant.weight',
                'invalid_message' => 'sylius.product_variant.weight.invalid',
                'constraints' => [
                    new NotBlank(['groups' => ['sylius']]),
                    new GreaterThan([
                        'value' => 0,
                        'groups' => ['sylius']
                    ]),
                ],
            ])
            ->add('insured', MoneyType::class, [
                'label' => 'Insured Monetary Value',
                'required' => false,
                'currency' => 'USD',
                'constraints' => [
                    new NotBlank(['groups' => ['sylius']]),
                    new GreaterThan([
                        'value' => 0,
                        'groups' => ['sylius']
                    ]),
                ],
            ])->addEventListener(FormEvents::PRE_SUBMIT,
                function(FormEvent $event) {
                    $data = $event->getData();
                    $form = $event->getForm();
                    if ($data['upsEntity'] && $data['upsEntity'] == '02') {
                        $form
                            ->add('width', NumberType::class, [
                                'required' => false,
                                'label' => 'Width (Customer Supplied Packaging Only)',
                                'invalid_message' => 'sylius.product_variant.width.invalid',
                                'constraints' => [
                                    new NotBlank(['groups' => ['sylius']]),
                                    new GreaterThan([
                                        'value' => 0,
                                        'groups' => ['sylius']
                                    ]),
                                ],
                            ])
                            ->add('height', NumberType::class, [
                                'required' => false,
                                'label' => 'Height (Customer Supplied Packaging Only)',
                                'invalid_message' => 'sylius.product_variant.height.invalid',
                                'constraints' => [
                                    new NotBlank(['groups' => ['sylius']]),
                                    new GreaterThan([
                                        'value' => 0,
                                        'groups' => ['sylius']
                                    ]),
                                ],
                            ])
                            ->add('depth', NumberType::class, [
                                'required' => false,
                                'label' => 'Depth (Customer Supplied Packaging Only)',
                                'invalid_message' => 'sylius.product_variant.depth.invalid',
                                'constraints' => [
                                    new NotBlank(['groups' => ['sylius']]),
                                    new GreaterThan([
                                        'value' => 0,
                                        'groups' => ['sylius']
                                    ]),
                                ],
                            ])
                        ;
                    }
                }
            );
    }


    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults(array(
                'data_class' => ProductVariantExtraDimension::class,
            ));


    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix(): string
    {
        return 'sylius_extra_dimension';
    }


}