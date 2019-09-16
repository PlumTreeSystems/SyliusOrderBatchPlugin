<?php

/*
 * This file is part of the Sylius package.
 *
 * (c) Paweł Jędrzejewski
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Tests\PTS\SyliusOrderBatchPlugin\Behat\Context\Hook;

use Behat\Behat\Context\Context;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManagerInterface;

final class DoctrineORMContext implements Context
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @BeforeScenario
     */
    public function purgeDatabase()
    {
        $this->entityManager->getConnection()->getConfiguration()->setSQLLogger(null);
        if ($this->entityManager->getConnection()->getDriver()->getName() === 'pdo_mysql') {
            $this->entityManager->getConnection()->prepare("SET FOREIGN_KEY_CHECKS = 0;")->execute();
        } elseif ($this->entityManager->getConnection()->getDriver()->getName() === 'pdo_sqlite') {
            $this->entityManager->getConnection()->prepare("PRAGMA foreign_keys = OFF");
        }
        $purger = new ORMPurger($this->entityManager);
        $purger->purge();
        $this->entityManager->clear();
        if ($this->entityManager->getConnection()->getDriver()->getName() === 'pdo_mysql') {
            $this->entityManager->getConnection()->prepare("SET FOREIGN_KEY_CHECKS = 1;")->execute();
        } elseif ($this->entityManager->getConnection()->getDriver()->getName() === 'pdo_sqlite') {
            $this->entityManager->getConnection()->prepare("PRAGMA foreign_keys = ON");
        }
    }
}
