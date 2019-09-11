<?php


namespace PTS\SyliusOrderBatchPlugin\Batch;

use Doctrine\ORM\EntityManager;

class BatchAction
{
    private $entityManager;

    public function __construct(EntityManager $em)
    {
        $this->entityManager = $em;
    }
}