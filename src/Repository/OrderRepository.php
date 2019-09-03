<?php

namespace PTS\SyliusOrderBatchPlugin\Repository;

use PTS\SyliusOrderBatchPlugin\Interfaces\OrderStateInterface;
use DateTime;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\QueryBuilder;
use Sylius\Bundle\CoreBundle\Doctrine\ORM\OrderRepository as BaseOrderRepository;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\CustomerInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PromotionCouponInterface;
use Sylius\Component\Core\OrderCheckoutStates;
use Sylius\Component\Core\OrderPaymentStates;

class OrderRepository extends BaseOrderRepository
{

    public function getAllMadeOrdersSum($customerId)
    {
        $total = $this->createQueryBuilder('o')
            ->leftJoin('o.customer', 'c')
            ->where('o.state = :fulfilled OR o.state = :new')
            ->andWhere('c.id = :customerId')
            ->andWhere('o.paymentState = :paid')
            ->andWhere('o.state != :autoship')
            ->andWhere('o.state != :parent')
            ->setParameter('fulfilled', 'fulfilled')
            ->setParameter('new', 'new')
            ->setParameter('paid', 'paid')
            ->setParameter('autoship', OrderStateInterface::ORDER_STATE_AUTOSHIP)
            ->setParameter('parent', OrderStateInterface::ORDER_STATE_PARENT)
            ->setParameter('customerId', $customerId)
            ->select('SUM(o.volumePoints)')
            ->getQuery()
            ->getSingleScalarResult();

        return $total;
    }

    public function getAllMadeOrdersSumInPastDays($customerId, $intervalDays = 7)
    {
        if (!$intervalDays || is_nan($intervalDays)) {
            $intervalDays = 7;
        }
        $to = new DateTime();
        $from = (new DateTime())->modify('-'.$intervalDays.' days');
        $total = $this->createQueryBuilder('o')
            ->leftJoin('o.customer', 'c')
            ->where('o.state = :fulfilled OR o.state = :new')
            ->andWhere('c.id = :customerId')
            ->andWhere('o.paymentState = :paid')
            ->andWhere('o.checkoutCompletedAt BETWEEN :from AND :to')
            ->andWhere('o.state != :autoship')
            ->andWhere('o.state != :parent')
            ->setParameter('fulfilled', 'fulfilled')
            ->setParameter('new', 'new')
            ->setParameter('paid', 'paid')
            ->setParameter('customerId', $customerId)
            ->setParameter('from', $from)
            ->setParameter('to', $to)
            ->setParameter('autoship', OrderStateInterface::ORDER_STATE_AUTOSHIP)
            ->setParameter('parent', OrderStateInterface::ORDER_STATE_PARENT)
            ->select('SUM(o.total)')
            ->getQuery()
            ->getSingleScalarResult();

        return $total;
    }

    public function getAllOlderCarts($excludeCartId, $customerId)
    {
        $qb = $this->createQueryBuilder('o')
            ->leftJoin('o.customer', 'c')
            ->where('o.state = :cart')
            ->andWhere('c.id = :customerId')
            ->andWhere('o.id != :excludeCartId')
            ->andWhere('o.state != :autoship')
            ->andWhere('o.state != :parent')
            ->setParameter('cart', 'cart')
            ->setParameter('customerId', $customerId)
            ->setParameter('excludeCartId', $excludeCartId)
            ->setParameter('autoship', OrderStateInterface::ORDER_STATE_AUTOSHIP)
            ->setParameter('parent', OrderStateInterface::ORDER_STATE_PARENT)
            ->getQuery();
        return $qb->execute();
    }


    public function getOrdersUsingDQL($query, $parameters)
    {
        $orders = $this->getEntityManager()->createQuery($query)->setParameters($parameters)->execute();

        return $orders;
    }

    public function getOrdersByNumbers($numbers)
    {
        $qb =  $this->createQueryBuilder('o');
        $query = $qb
            ->where($qb->expr()->in('o.number', $numbers))
            ->andWhere('o.state != :autoship')
            ->andWhere('o.state != :parent')
            ->setParameter('autoship', OrderStateInterface::ORDER_STATE_AUTOSHIP)
            ->setParameter('parent', OrderStateInterface::ORDER_STATE_PARENT)
            ->getQuery();

        $res = $query->execute();

        if (sizeof($res) == 0) {
            return [];
        }

        return $res;
    }

    public function getPaginatedBatchOrders($ordersArray)
    {
        $query = $this->createQueryBuilder('o')
            ->orderBy('o.id', 'ASC')
            ->where('o.id IN (:orders)')
            ->andWhere('o.state != :autoship')
            ->andWhere('o.state != :parent')
            ->setParameter('orders', $ordersArray)
            ->setParameter('autoship', OrderStateInterface::ORDER_STATE_AUTOSHIP)
            ->setParameter('parent', OrderStateInterface::ORDER_STATE_PARENT);

        return $query;
    }

    /**
     * @param $channelId
     * @param $customerId
     * @return mixed|null
     * @throws NonUniqueResultException
     */
    public function getCartNotInChannel($channelId, $customerId)
    {
        $query = $this->createQueryBuilder('o')
            ->leftJoin('o.customer', 'c')
            ->leftJoin('o.channel', 'ch')
            ->where('o.state = :cart')
            ->andWhere('c.id = :customerId')
            ->andWhere('ch.id != :excludeChannelId')
            ->andWhere('o.state != :autoship')
            ->andWhere('o.state != :parent')
            ->setParameter('cart', 'cart')
            ->setParameter('customerId', $customerId)
            ->setParameter('excludeChannelId', $channelId)
            ->setParameter('autoship', OrderStateInterface::ORDER_STATE_AUTOSHIP)
            ->setParameter('parent', OrderStateInterface::ORDER_STATE_PARENT)
            ->getQuery();
        try {
            return $query->getSingleResult();
        } catch (NoResultException $e) {
            return null;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function createListQueryBuilder(): QueryBuilder
    {
        return $this->createQueryBuilder('o')
            ->addSelect('channel')
            ->addSelect('customer')
            ->innerJoin('o.channel', 'channel')
            ->leftJoin('o.customer', 'customer')
            ->andWhere('o.state != :state')
            ->andWhere('o.state != :autoship')
            ->andWhere('o.state != :parent')
            ->setParameter('state', OrderInterface::STATE_CART)
            ->setParameter('autoship', OrderStateInterface::ORDER_STATE_AUTOSHIP)
            ->setParameter('parent', OrderStateInterface::ORDER_STATE_PARENT)
            ;
    }

    /**
     * {@inheritdoc}
     */
    public function createByCustomerIdQueryBuilder($customerId): QueryBuilder
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.customer = :customerId')
            ->andWhere('o.state != :state')
            ->andWhere('o.state != :autoship')
            ->andWhere('o.state != :parent')
            ->setParameter('customerId', $customerId)
            ->setParameter('state', OrderInterface::STATE_CART)
            ->setParameter('autoship', OrderStateInterface::ORDER_STATE_AUTOSHIP)
            ->setParameter('parent', OrderStateInterface::ORDER_STATE_PARENT)
            ;
    }

    /**
     * {@inheritdoc}
     */
    public function createByCustomerAndChannelIdQueryBuilder($customerId, $channelId): QueryBuilder
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.customer = :customerId')
            ->andWhere('o.channel = :channelId')
            ->andWhere('o.state != :state')
            ->andWhere('o.state != :autoship')
            ->andWhere('o.state != :parent')
            ->setParameter('customerId', $customerId)
            ->setParameter('channelId', $channelId)
            ->setParameter('state', OrderInterface::STATE_CART)
            ->setParameter('autoship', OrderStateInterface::ORDER_STATE_AUTOSHIP)
            ->setParameter('parent', OrderStateInterface::ORDER_STATE_PARENT)
            ;
    }

    /**
     * {@inheritdoc}
     */
    public function findByCustomer(CustomerInterface $customer): array
    {
        return $this->createByCustomerIdQueryBuilder($customer->getId())
            ->getQuery()
            ->getResult()
            ;
    }

    /**
     * {@inheritdoc}
     */
    public function findForCustomerStatistics(CustomerInterface $customer): array
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.customer = :customerId')
            ->andWhere('o.state = :state')
            ->andWhere('o.state != :autoship')
            ->andWhere('o.state != :parent')
            ->setParameter('customerId', $customer->getId())
            ->setParameter('state', OrderInterface::STATE_FULFILLED)
            ->setParameter('autoship', OrderStateInterface::ORDER_STATE_AUTOSHIP)
            ->setParameter('parent', OrderStateInterface::ORDER_STATE_PARENT)
            ->getQuery()
            ->getResult()
            ;
    }

    /**
     * {@inheritdoc}
     */
    public function findOneForPayment($id): ?OrderInterface
    {
        return $this->createQueryBuilder('o')
            ->addSelect('payments')
            ->addSelect('paymentMethods')
            ->leftJoin('o.payments', 'payments')
            ->leftJoin('payments.method', 'paymentMethods')
            ->andWhere('o.id = :id')
            ->andWhere('o.state != :autoship')
            ->andWhere('o.state != :parent')
            ->setParameter('id', $id)
            ->setParameter('autoship', OrderStateInterface::ORDER_STATE_AUTOSHIP)
            ->setParameter('parent', OrderStateInterface::ORDER_STATE_PARENT)
            ->getQuery()
            ->getOneOrNullResult()
            ;
    }

    /**
     * {@inheritdoc}
     */
    public function countByCustomerAndCoupon(CustomerInterface $customer, PromotionCouponInterface $coupon): int
    {
        return (int) $this->createQueryBuilder('o')
            ->select('COUNT(o.id)')
            ->andWhere('o.customer = :customer')
            ->andWhere('o.promotionCoupon = :coupon')
            ->andWhere('o.state NOT IN (:states)')
            ->andWhere('o.state != :autoship')
            ->andWhere('o.state != :parent')
            ->setParameter('customer', $customer)
            ->setParameter('coupon', $coupon)
            ->setParameter('states', [OrderInterface::STATE_CART, OrderInterface::STATE_CANCELLED])
            ->setParameter('autoship', OrderStateInterface::ORDER_STATE_AUTOSHIP)
            ->setParameter('parent', OrderStateInterface::ORDER_STATE_PARENT)
            ->getQuery()
            ->getSingleScalarResult()
            ;
    }

    /**
     * {@inheritdoc}
     */
    public function countByCustomer(CustomerInterface $customer): int
    {
        return (int) $this->createQueryBuilder('o')
            ->select('COUNT(o.id)')
            ->andWhere('o.customer = :customer')
            ->andWhere('o.state NOT IN (:states)')
            ->andWhere('o.state != :autoship')
            ->andWhere('o.state != :parent')
            ->setParameter('customer', $customer)
            ->setParameter('states', [OrderInterface::STATE_CART, OrderInterface::STATE_CANCELLED])
            ->setParameter('autoship', OrderStateInterface::ORDER_STATE_AUTOSHIP)
            ->setParameter('parent', OrderStateInterface::ORDER_STATE_PARENT)
            ->getQuery()
            ->getSingleScalarResult()
            ;
    }

    /**
     * {@inheritdoc}
     */
    public function findOneByNumberAndCustomer(string $number, CustomerInterface $customer): ?OrderInterface
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.customer = :customer')
            ->andWhere('o.number = :number')
            ->andWhere('o.state != :autoship')
            ->andWhere('o.state != :parent')
            ->setParameter('customer', $customer)
            ->setParameter('number', $number)
            ->setParameter('autoship', OrderStateInterface::ORDER_STATE_AUTOSHIP)
            ->setParameter('parent', OrderStateInterface::ORDER_STATE_PARENT)
            ->getQuery()
            ->getOneOrNullResult()
            ;
    }

    /**
     * {@inheritdoc}
     */
    public function findCartByChannel($id, ChannelInterface $channel): ?OrderInterface
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.id = :id')
            ->andWhere('o.state = :state')
            ->andWhere('o.channel = :channel')
            ->andWhere('o.state != :autoship')
            ->andWhere('o.state != :parent')
            ->setParameter('id', $id)
            ->setParameter('state', OrderInterface::STATE_CART)
            ->setParameter('channel', $channel)
            ->setParameter('autoship', OrderStateInterface::ORDER_STATE_AUTOSHIP)
            ->setParameter('parent', OrderStateInterface::ORDER_STATE_PARENT)
            ->getQuery()
            ->getOneOrNullResult()
            ;
    }

    /**
     * {@inheritdoc}
     */
    public function findLatestCartByChannelAndCustomer(ChannelInterface $channel, CustomerInterface $customer): ?OrderInterface
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.state = :state')
            ->andWhere('o.channel = :channel')
            ->andWhere('o.customer = :customer')
            ->andWhere('o.state != :autoship')
            ->andWhere('o.state != :parent')
            ->setParameter('state', OrderInterface::STATE_CART)
            ->setParameter('channel', $channel)
            ->setParameter('customer', $customer)
            ->setParameter('autoship', OrderStateInterface::ORDER_STATE_AUTOSHIP)
            ->setParameter('parent', OrderStateInterface::ORDER_STATE_PARENT)
            ->addOrderBy('o.createdAt', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
            ;
    }

    /**
     * {@inheritdoc}
     */
    public function getTotalSalesForChannel(ChannelInterface $channel): int
    {
        return (int) $this->createQueryBuilder('o')
            ->select('SUM(o.total)')
            ->andWhere('o.channel = :channel')
            ->andWhere('o.state = :state')
            ->andWhere('o.state != :autoship')
            ->andWhere('o.state != :parent')
            ->setParameter('channel', $channel)
            ->setParameter('state', OrderInterface::STATE_FULFILLED)
            ->setParameter('autoship', OrderStateInterface::ORDER_STATE_AUTOSHIP)
            ->setParameter('parent', OrderStateInterface::ORDER_STATE_PARENT)
            ->getQuery()
            ->getSingleScalarResult()
            ;
    }

    /**
     * {@inheritdoc}
     */
    public function countFulfilledByChannel(ChannelInterface $channel): int
    {
        return (int) $this->createQueryBuilder('o')
            ->select('COUNT(o.id)')
            ->andWhere('o.channel = :channel')
            ->andWhere('o.state = :state')
            ->andWhere('o.state != :autoship')
            ->andWhere('o.state != :parent')
            ->setParameter('channel', $channel)
            ->setParameter('state', OrderInterface::STATE_FULFILLED)
            ->setParameter('autoship', OrderStateInterface::ORDER_STATE_AUTOSHIP)
            ->setParameter('parent', OrderStateInterface::ORDER_STATE_PARENT)
            ->getQuery()
            ->getSingleScalarResult()
            ;
    }

    /**
     * {@inheritdoc}
     */
    public function findLatestInChannel(int $count, ChannelInterface $channel): array
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.channel = :channel')
            ->andWhere('o.state != :state')
            ->andWhere('o.state != :autoship')
            ->andWhere('o.state != :parent')
            ->addOrderBy('o.checkoutCompletedAt', 'DESC')
            ->setParameter('channel', $channel)
            ->setParameter('state', OrderInterface::STATE_CART)
            ->setParameter('autoship', OrderStateInterface::ORDER_STATE_AUTOSHIP)
            ->setParameter('parent', OrderStateInterface::ORDER_STATE_PARENT)
            ->setMaxResults($count)
            ->getQuery()
            ->getResult()
            ;
    }

    /**
     * {@inheritdoc}
     */
    public function findOrdersUnpaidSince(\DateTimeInterface $terminalDate): array
    {
        return $this->createQueryBuilder('o')
            ->where('o.checkoutState = :checkoutState')
            ->andWhere('o.paymentState != :paymentState')
            ->andWhere('o.state = :orderState')
            ->andWhere('o.checkoutCompletedAt < :terminalDate')
            ->andWhere('o.state != :autoship')
            ->andWhere('o.state != :parent')
            ->setParameter('checkoutState', OrderCheckoutStates::STATE_COMPLETED)
            ->setParameter('paymentState', OrderPaymentStates::STATE_PAID)
            ->setParameter('orderState', OrderInterface::STATE_NEW)
            ->setParameter('terminalDate', $terminalDate)
            ->setParameter('autoship', OrderStateInterface::ORDER_STATE_AUTOSHIP)
            ->setParameter('parent', OrderStateInterface::ORDER_STATE_PARENT)
            ->getQuery()
            ->getResult()
            ;
    }

    /**
     * {@inheritdoc}
     */
    public function findCartForSummary($id): ?OrderInterface
    {
        /** @var OrderInterface $order */
        $order = $this->createQueryBuilder('o')
            ->andWhere('o.id = :id')
            ->andWhere('o.state = :state')
            ->andWhere('o.state != :autoship')
            ->andWhere('o.state != :parent')
            ->setParameter('id', $id)
            ->setParameter('state', OrderInterface::STATE_CART)
            ->setParameter('autoship', OrderStateInterface::ORDER_STATE_AUTOSHIP)
            ->setParameter('parent', OrderStateInterface::ORDER_STATE_PARENT)
            ->getQuery()
            ->getOneOrNullResult()
        ;

        $this->associationHydrator->hydrateAssociations($order, [
            'adjustments',
            'items',
            'items.adjustments',
            'items.units',
            'items.units.adjustments',
            'items.variant',
            'items.variant.optionValues',
            'items.variant.optionValues.translations',
            'items.variant.product',
            'items.variant.product.translations',
            'items.variant.product.images',
            'items.variant.product.options',
            'items.variant.product.options.translations',
        ]);

        return $order;
    }

    /**
     * {@inheritdoc}
     */
    public function findCartForAddressing($id): ?OrderInterface
    {
        /** @var OrderInterface $order */
        $order = $this->createQueryBuilder('o')
            ->andWhere('o.id = :id')
            ->andWhere('o.state = :state')
            ->andWhere('o.state != :autoship')
            ->andWhere('o.state != :parent')
            ->setParameter('id', $id)
            ->setParameter('state', OrderInterface::STATE_CART)
            ->setParameter('autoship', OrderStateInterface::ORDER_STATE_AUTOSHIP)
            ->setParameter('parent', OrderStateInterface::ORDER_STATE_PARENT)
            ->getQuery()
            ->getOneOrNullResult()
        ;

        $this->associationHydrator->hydrateAssociations($order, [
            'items',
            'items.variant',
            'items.variant.product',
            'items.variant.product.translations',
        ]);

        return $order;
    }

    /**
     * {@inheritdoc}
     */
    public function findCartForSelectingShipping($id): ?OrderInterface
    {
        /** @var OrderInterface $order */
        $order = $this->createQueryBuilder('o')
            ->andWhere('o.id = :id')
            ->andWhere('o.state = :state')
            ->andWhere('o.state != :autoship')
            ->andWhere('o.state != :parent')
            ->setParameter('id', $id)
            ->setParameter('state', OrderInterface::STATE_CART)
            ->setParameter('autoship', OrderStateInterface::ORDER_STATE_AUTOSHIP)
            ->setParameter('parent', OrderStateInterface::ORDER_STATE_PARENT)
            ->getQuery()
            ->getOneOrNullResult()
        ;

        $this->associationHydrator->hydrateAssociations($order, [
            'items',
            'items.variant',
            'items.variant.product',
            'items.variant.product.translations',
        ]);

        return $order;
    }

    /**
     * {@inheritdoc}
     */
    public function findCartForSelectingPayment($id): ?OrderInterface
    {
        /** @var OrderInterface $order */
        $order = $this->createQueryBuilder('o')
            ->andWhere('o.id = :id')
            ->andWhere('o.state = :state')
            ->andWhere('o.state != :autoship')
            ->andWhere('o.state != :parent')
            ->setParameter('id', $id)
            ->setParameter('state', OrderInterface::STATE_CART)
            ->setParameter('autoship', OrderStateInterface::ORDER_STATE_AUTOSHIP)
            ->setParameter('parent', OrderStateInterface::ORDER_STATE_PARENT)
            ->getQuery()
            ->getOneOrNullResult()
        ;

        $this->associationHydrator->hydrateAssociations($order, [
            'items',
            'items.variant',
            'items.variant.product',
            'items.variant.product.translations',
        ]);

        return $order;
    }

    public function getLastOrder() {
        $qb = $this->createQueryBuilder('o');

        return $qb
            ->where($qb->expr()->isNotNull('o.number'))
            ->andWhere('o.state != :autoship')
            ->andWhere('o.state != :parent')
            ->orderBy('o.checkoutCompletedAt', 'DESC')
            ->setParameter('autoship', OrderStateInterface::ORDER_STATE_AUTOSHIP)
            ->setParameter('parent', OrderStateInterface::ORDER_STATE_PARENT)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
            ;
    }
}
