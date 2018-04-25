<?php

namespace DestroBundle\Menu;

use Sylius\Bundle\AdminBundle\Event\ProductVariantMenuBuilderEvent;

final class AdminProductVariantFormMenuListener
{
    /**
     * @param ProductVariantMenuBuilderEvent $event
     */
    public function addItems(ProductVariantMenuBuilderEvent $event): void
    {
        $menu = $event->getMenu();

        $menu
            ->addChild('product_variant_extra_dimension')
            ->setAttribute('template', 'DestroBundle::Admin/ProductVariant/Tab/_product_variant_extra_dimension.html.twig')
            ->setLabel('Extra Dimensions')
        ;
    }
}