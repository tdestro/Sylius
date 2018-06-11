<?php
namespace DestroBundle\Menu;

use Sylius\Bundle\UiBundle\Menu\Event\MenuBuilderEvent;

final class AccountMenuListener
{
    /**
     * @param MenuBuilderEvent $event
     */
    public function addAccountMenuItems(MenuBuilderEvent $event): void
    {
        $menu = $event->getMenu();

        $menu
            ->addChild('new', ['route' => 'sylius_shop_account_payment_sources'])
            ->setLabel('Payment Sources')
            ->setLabelAttribute('icon', 'money bill alternate')
        ;
    }
}
