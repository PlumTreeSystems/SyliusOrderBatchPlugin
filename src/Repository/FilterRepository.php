<?php

namespace PTS\SyliusOrderBatchPlugin\Repository;

use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;

class FilterRepository extends EntityRepository
{
    public function getFilters($ids)
    {
        $qb =  $this->createQueryBuilder('f');
        $query = $qb
            ->where($qb->expr()->in('f.id', $ids))
            ->getQuery();

        $res = $query->execute();

        if (sizeof($res) == 0) {
            return [];
        }

        return $res;
    }

    public function getFiltersForSearch()
    {
        $qb =  $this->createQueryBuilder('f');
        $query = $qb
            ->select('f.filterName as value, f.id as id')
            ->getQuery();

        $res = $query->execute();
        return $res;
    }
}