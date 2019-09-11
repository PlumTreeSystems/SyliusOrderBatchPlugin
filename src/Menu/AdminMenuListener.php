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

        $subMenu
            ->addChild('failedPaymentBatch', ['route' => 'app_batch_payment_failed_show'])
            ->setLabel('app.batchType.failedPayment')
            ->setLabelAttribute('icon', 'minus circle')
        ;

        $subMenu
            ->addChild('outOfStockBatch', ['route' => 'app_batch_out_of_stock_show'])
            ->setLabel('app.batchType.outOfStock')
            ->setLabelAttribute('icon', 'cart arrow down')
        ;

        $subMenu
            ->addChild('shippingBatch', ['route' => 'app_shippingBatch_index'])
            ->setLabel('app.batchType.shipping')
            ->setLabelAttribute('icon', 'dolly flatbed')
        ;


        $subMenu = $menu->getChild('sales');

        $subMenu
            ->addChild('ordersFilters', ['route' => 'app_filter_index'])
            ->setLabel('app.ui.ordersFilters')
            ->setLabelAttribute('icon', 'filter')
        ;
    }
}
