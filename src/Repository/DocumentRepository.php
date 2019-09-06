<?php

namespace PTS\SyliusOrderBatchPlugin\Repository;

use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;

class DocumentRepository extends EntityRepository
{
    public function getOrderInvoice($orderId, $code)
    {
        $query = $this->createQueryBuilder('d')
            ->leftJoin('d.orders', 'o')
            ->where('o.id = :orderId')
            ->andWhere('d.code = :code')
            ->setParameter('orderId', $orderId)
            ->setParameter('code', $code)
            ->getQuery();

        $res = $query->execute();

        if (sizeof($res) == 0) {
            return null;
        }

        return $res[0];
    }
}