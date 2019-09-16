<?php


namespace Tests\PTS\SyliusOrderBatchPlugin\Behat\Context\Setup;

use PTS\SyliusOrderBatchPlugin\Entity\Batch;
use PTS\SyliusOrderBatchPlugin\Repository\OrderRepository;
use Behat\Behat\Context\Context;
use Doctrine\Common\Persistence\ObjectManager;
use Sylius\Behat\Service\SharedStorageInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;

class BatchContext implements Context
{
    /**
     * @var SharedStorageInterface
     */
    private $sharedStorage;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var RepositoryInterface
     */
    private $localeRepository;

    /**
     * @param SharedStorageInterface $sharedStorage
     * @param ObjectManager $objectManager
     */
    public function __construct(
        SharedStorageInterface $sharedStorage,
        ObjectManager $objectManager,
        RepositoryInterface $localeRepository
    ) {
        $this->sharedStorage = $sharedStorage;
        $this->objectManager = $objectManager;
        $this->localeRepository = $localeRepository;
    }

    /**
     * @Given /^There is a batch named "([^"]*)" containing orders "([^"]*)"$/
     */
    public function createEmailTemplateWith(string $name, string $orders)
    {
        $ordersNumbers = explode(', ', $orders);
        /** @var OrderRepository $orderRepo */
        $orderRepo = $this->objectManager->getRepository(Order::class);
        $orders = $orderRepo->getOrdersByNumbers($ordersNumbers);

        $batch = new Batch();
        $batch->setName($name);
        $batch->setOrders($orders);

        $this->objectManager->persist($batch);
        $this->objectManager->flush();
    }
}
