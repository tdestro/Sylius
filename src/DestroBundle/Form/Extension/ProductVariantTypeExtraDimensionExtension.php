<?php

declare(strict_types=1);

namespace DestroBundle\Form\Extension;


use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Sylius\Bundle\ProductBundle\Form\Type\ProductVariantType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use DestroBundle\Form\Type\ProductVariantExtraDimensionType;

final class ProductVariantTypeExtraDimensionExtension extends AbstractTypeExtension
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
                $event->getForm()->add('productVariantExtraDimensions', CollectionType::class, [
                    'entry_type' =>  ProductVariantExtraDimensionType::class,
                    'allow_add' => true,
                    'allow_delete' => true,
                    'by_reference' => false,
                    'label' => 'Extra Dimensions',
                ]);
            });
    }

    /**
     * {@inheritdoc}
     */
    public function getExtendedType(): string
    {
        return ProductVariantType::class;
    }
}