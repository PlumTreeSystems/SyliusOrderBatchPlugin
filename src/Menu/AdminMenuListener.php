<?php

namespace PTS\SyliusOrderBatchPlugin\Menu;

use Sylius\Bundle\UiBundle\Menu\Event\MenuBuilderEvent;

final class AdminMenuListener
{
    /**
     * @param MenuBuilderEvent $event
     */
    public function addAdminMenuItems(MenuBuilderEvent $event): void
    {
        $menu = $event->getMenu();

        $subMenu = $menu
            ->addChild('batch')
            ->setLabel('app.ui.batches')
        ;


        $subMenu
            ->addChild('otherBatch', ['route' => 'app_batch_index'])
            ->setLabel('app.batchType.other')
            ->setLabelAttribute('icon', 'archive')
        ;


        $subMenu = $menu->getChild('sales');

        $subMenu
            ->addChild('ordersFilters', ['route' => 'app_filter_index'])
            ->setLabel('app.ui.ordersFilters')
            ->setLabelAttribute('icon', 'filter')
        ;
    }
}
