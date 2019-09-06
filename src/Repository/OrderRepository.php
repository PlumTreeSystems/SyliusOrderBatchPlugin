<?php

namespace PTS\SyliusOrderBatchPlugin\Repository;

use Sylius\Bundle\CoreBundle\Doctrine\ORM\OrderRepository as BaseOrderRepository;

class OrderRepository extends BaseOrderRepository
{
    public function getOrdersUsingDQL($query, $parameters)
    {
        $orders = $this->getEntityManager()->createQuery($query)->setParameters($parameters)->execute();

        return $orders;
    }
    public function getPaginatedBatchOrders($ordersArray)
    {
        $query = $this->createQueryBuilder('o')
            ->orderBy('o.id', 'ASC')
            ->where('o.id IN (:orders)')
            ->andWhere('o.state != :parent')
            ->setParameter('orders', $ordersArray);

        return $query;
    }
}
