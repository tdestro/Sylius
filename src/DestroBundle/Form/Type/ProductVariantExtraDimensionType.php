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

final class ProductVariantExtraDimensionType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('width', NumberType::class, [
                'required' => false,
                'label' => 'sylius.form.variant.width',
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
                'label' => 'sylius.form.variant.height',
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
                'label' => 'sylius.form.variant.depth',
                'invalid_message' => 'sylius.product_variant.depth.invalid',
                'constraints' => [
                    new NotBlank(['groups' => ['sylius']]),
                    new GreaterThan([
                        'value' => 0,
                        'groups' => ['sylius']
                    ]),
                ],
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
            ]);
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