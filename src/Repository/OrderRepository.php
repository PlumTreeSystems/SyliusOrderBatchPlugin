<?php

namespace PTS\SyliusOrderBatchPlugin\Repository;

use App\Interfaces\OrderStateInterface;
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
            ->setParameter('orders', $ordersArray);

        return $query;
    }
    public function getOrdersByNumbers($numbers)
    {
        $qb =  $this->createQueryBuilder('o');
        $query = $qb
            ->where($qb->expr()->in('o.number', $numbers))
            ->getQuery();

        $res = $query->execute();

        if (sizeof($res) == 0) {
            return [];
        }

        return $res;
    }
}
