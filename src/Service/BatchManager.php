<?php


namespace PTS\SyliusOrderBatchPlugin\Service;

use PTS\SyliusOrderBatchPlugin\Entity\Batch;
use PTS\SyliusOrderBatchPlugin\Form\Type\BatchType;
use PTS\SyliusOrderBatchPlugin\Interfaces\BatchInterface;
use PTS\SyliusOrderBatchPlugin\Repository\BatchRepository;
use PTS\SyliusOrderBatchPlugin\Repository\OrderRepository;
use Doctrine\ORM\EntityManager;
use Pagerfanta\Pagerfanta;
use SM\Factory\Factory;
use Sylius\Component\Core\Model\Order;
use Sylius\Component\Core\OrderShippingStates;
use Sylius\Component\Core\OrderShippingTransitions;
use Sylius\Component\Shipping\Model\Shipment;
use Sylius\Component\Shipping\ShipmentTransitions;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\EventDispatcher\EventDispatcherInterface as SymfonyEventDispatcherInterface;

class BatchManager
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var FormErrorExtractor
     */
    private $formErrorExtractor;

    /**
     * @var FormFactory
     */
    private $formFactory;

    /**
     * @var Factory
     */
    private $stateMachineFactory;

    /**
     * @var SymfonyEventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * Filters service constructor.
     * @param EntityManager $entityManager
     * @param FormErrorExtractor $formErrorExtractor
     * @param FormFactory $formFactory
     * @param Factory $stateMachineFactory
     * @param SymfonyEventDispatcherInterface $eventDispatcher
     */
    public function __construct(
        EntityManager $entityManager,
        FormErrorExtractor $formErrorExtractor,
        FormFactory $formFactory,
        Factory $stateMachineFactory,
        SymfonyEventDispatcherInterface $eventDispatcher
    ) {
        $this->entityManager = $entityManager;
        $this->formErrorExtractor = $formErrorExtractor;
        $this->formFactory = $formFactory;
        $this->stateMachineFactory = $stateMachineFactory;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function createBatchFromRequest($resources, $request)
    {
        $query = $resources->getData()->getAdapter()->getQuery();
        $ordersDQL = $query->getDQL();
        $parameters = $query->getParameters();

        /** @var OrderRepository $ordersRepository */
        $ordersRepository = $this->entityManager->getRepository(Order::class);
        $orders = $ordersRepository->getOrdersUsingDQL($ordersDQL, $parameters);
        $batchName = $request->query->get('_batchName');
        $ordersForBatch = array_map(function(Order $array) {return $array->getId();}, $orders);

        return $this->createBatch($ordersForBatch, $batchName);
    }

    public function createBatch($orders, $batchName, $type = null)
    {
        $em = $this->entityManager;
        $data = [
            'name' => $batchName,
            'orders' => $orders
        ];

        if (!is_null($type)) {
            $data['type'] = $type;
        }

        $batchRepo = $em->getRepository(Batch::class);
        $batch = $batchRepo->findOneBy(['name' => $batchName]);
        $new = false;

        if (is_null($batch)) {
            $batch = new Batch();

            $new = true;
        }

        $form = $this->formFactory->create(BatchType::class, $batch, [
            'csrf_protection' => false
        ]);

        $form->submit($data, false);
        if ($form->isValid()) {
            if ($new) {
                $this->entityManager->persist($batch);
            }

            $this->entityManager->flush();
            return ['id' => $batch->getId()];
        } else {
            $errors = $this->formErrorExtractor->getErrorMessages($form);
            return ['errors' => $errors];
        }
    }

    public function addToBatch(Order $order, $batchName = '', $type = null) {
        $em = $this->entityManager;

        $batchRepo = $em->getRepository(Batch::class);
        $batch = $batchRepo->findOneBy(['name' => $batchName]);
        $new = false;

        if (is_null($batch)) {
            $batch = new Batch();
            $batch->setName($batchName);

            if (!is_null($type)) {
                $batch->setType($type);
            }

            $new = true;
        }

        $batch->addOrder($order);

        if ($new) {
            $em->persist($batch);
        }

        $em->flush();
    }

    public function markAllAsShipped(Pagerfanta $paginator)
    {
        /**
         * @var Pagerfanta $markPaginator
         */
        $markPaginator = clone $paginator;
        $markPaginator->setMaxPerPage(100);

        $next = true;

        while($next) {
            $results = $markPaginator->getCurrentPageResults();

            /**
             * @var Order $item
             */
            foreach ($results as $item) {
                if ($item->getShippingState() == OrderShippingStates::STATE_READY) {
                    $stateMachine = $this->stateMachineFactory->get($item, OrderShippingTransitions::GRAPH);
                    $stateMachine->apply(OrderShippingTransitions::TRANSITION_SHIP);

                    $shipments = $item->getShipments();

                    /** @var Shipment $shipment */
                    foreach ($shipments as $shipment) {
                        $event = new GenericEvent($shipment);

                        $this->eventDispatcher->dispatch('sylius.shipment.pre_ship', $event);

                        $shipmentStateMachine = $this->stateMachineFactory->get($shipment, ShipmentTransitions::GRAPH);

                        if ($shipment->getState() == OrderShippingStates::STATE_CART) {
                            $shipmentStateMachine->apply(ShipmentTransitions::TRANSITION_CREATE);
                        }

                        $shipmentStateMachine->apply(ShipmentTransitions::TRANSITION_SHIP);

                        $this->eventDispatcher->dispatch('sylius.shipment.post_ship', $event);
                    }

                    $this->entityManager->persist($item);
                    $this->entityManager->flush();
                }
            }

            if ($markPaginator->hasNextPage()) {
                $markPaginator->setCurrentPage($markPaginator->getNextPage());
            } else {
                $next = false;
            }
        }
    }

    public function removeOrderFromNamedBatch(string $orderId, string $batchName)
    {
        $em = $this->entityManager;

        /** @var BatchRepository $batchRepo */
        $batchRepo = $em->getRepository(Batch::class);

        $batch = $batchRepo->findOneBy(['name' => $batchName]);

        if (is_null($batch)) {
            return;
        }

        return $this->removeOrderFromBatch($orderId, $batch);
    }

    public function removeOrderFromBatch(string $orderId, Batch $batch) {
        $em = $this->entityManager;

        $orderRepo = $em->getRepository(Order::class);
        $order = $orderRepo->find($orderId);

        $batch->removeOrder($order);
        $this->entityManager->persist($batch);
        $this->entityManager->flush();

        return $batch;
    }

    public function resolveBatchTypeByGrid(string $gridName) {
        switch ($gridName) {
            case 'app_admin_out_of_stock_batch':
                return BatchInterface::OUT_OF_STOCK_TYPE;
            case 'app_admin_payment_failed_batch':
                return BatchInterface::FAILED_PAYMENT_TYPE;
            case 'app_admin_shipment_batch':
                return BatchInterface::SHIPMENT_TYPE;
            default:
                return 'default';
        }
    }
}