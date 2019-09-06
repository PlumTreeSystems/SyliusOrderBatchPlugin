<?php

namespace PTS\SyliusOrderBatchPlugin\Repository;

use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;

class DocumentTemplateRepository extends EntityRepository
{
    public function getDocumentTemplate($type, $code)
    {
        $query = $this->createQueryBuilder('i')
            ->leftJoin('i.locale', 'l')
            ->where('i.code = :type')
            ->andWhere('l.code = :code')
            ->setParameter('type', $type)
            ->setParameter('code', $code)
            ->getQuery();

        $res = $query->execute();

        if (sizeof($res) == 0) {
            return null;
        }

        return $res[0];
    }
}