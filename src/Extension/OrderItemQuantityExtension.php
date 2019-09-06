<?php

namespace PTS\SyliusOrderBatchPlugin\Extension;

use Sylius\Component\Core\Model\Order;
use Sylius\Component\Core\Model\OrderItem;
use Twig\Extension\AbstractExtension;

class OrderItemQuantityExtension extends AbstractExtension
{
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('calculateQuantity', [$this, 'calculateQuantity']),
        ];
    }

    public function calculateQuantity(Order $order): int
    {
        $orderItems = $order->getItems();

        $total = 0;

        foreach ($orderItems as $item) {
            /** @var $item OrderItem */
            $total += $item->getQuantity();
        }
        return $total;
    }
}