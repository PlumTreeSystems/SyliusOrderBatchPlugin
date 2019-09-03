<?php

namespace PTS\SyliusOrderBatchPlugin\Repository;

use PTS\SyliusOrderBatchPlugin\Interfaces\BatchInterface;
use PTS\SyliusOrderBatchPlugin\Interfaces\OrderStateInterface;
use Doctrine\ORM\QueryBuilder;
use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;

class BatchRepository extends EntityRepository
{
    public function getBatchOrders($id, $type = null)
    {
        $qb =  $this->createQueryBuilder('b');
        $qb
            ->leftJoin('b.orders', 'o')
            ->where('b.id = :id')
            ->setParameter('id', $id)
            ->select('o.id');

        if (!is_null($type)) {
            $qb
                ->andWhere('b.type = :type')
                ->setParameter('type', $type);
        }

        $query = $qb
            ->getQuery();

        $res = $query->execute();
        return $res;
    }

    public function getBatchesSearch()
    {
        $qb =  $this->createQueryBuilder('b');
        $query = $qb
            ->where('b.type IS NULL')
            ->select('b.name as value')
            ->getQuery();

        $res = $query->execute();
        return $res;
    }

    public function createListQueryBuilder(): QueryBuilder
    {
        return $this->createQueryBuilder('b')
            ->where('b.type IS NULL')
            ;
    }

    public function createShippingBatchListQueryBuilder(): QueryBuilder
    {
        return $this->createQueryBuilder('b')
            ->where('b.type = :type')
            ->setParameter('type', BatchInterface::SHIPMENT_TYPE)
            ;
    }

    /**
     * @param $type
     * @return mixed
     */
    public function getOrdersFromBatch($type) {
        $qb =  $this->createQueryBuilder('b');
        $query = $qb
            ->leftJoin('b.orders', 'o')
            ->where('b.type = :type')
            ->andWhere('o.state != :parent')
            ->setParameter('type', $type)
            ->setParameter('parent', OrderStateInterface::ORDER_STATE_PARENT)
            ->select('o.id as orderId')
            ->getQuery();

        $res = $query->execute();
        return $res;
    }
}
