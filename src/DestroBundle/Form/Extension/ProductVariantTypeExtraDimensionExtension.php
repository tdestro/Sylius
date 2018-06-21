<?php

declare(strict_types=1);

namespace DestroBundle\Form\Extension;


use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;
use Sylius\Bundle\ProductBundle\Form\Type\ProductVariantType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use DestroBundle\Form\Type\ProductVariantExtraDimensionType;
use Symfony\Component\Validator\Constraints as Assert;

final class ProductVariantTypeExtraDimensionExtension extends AbstractTypeExtension
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('productVariantExtraDimensions', CollectionType::class, [
            'entry_type' =>  ProductVariantExtraDimensionType::class,
            'allow_add' => true,
            'allow_delete' => true,
            'by_reference' => false,
            'label' => 'Extra Dimensions',
            'constraints' => [
                new Assert\Count([
                    'min' => 1,
                    'minMessage' => 'Must have at least one product shipping dimension',
                    'groups' => ['sylius'],
                ]),
            ],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getExtendedType(): string
    {
        return ProductVariantType::class;
    }
}