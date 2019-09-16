<?php


namespace Tests\PTS\SyliusOrderBatchPlugin\Behat\Context\Setup;

use Sylius\Component\Core\Model\Order;
use Sylius\Component\Core\Model\OrderItem;
use Sylius\Bundle\CoreBundle\Doctrine\ORM\PaymentMethodRepository;
use Sylius\Bundle\CoreBundle\Doctrine\ORM\ShippingMethodRepository;
use Behat\Behat\Context\Context;
use Doctrine\ORM\EntityManager;
use Sylius\Behat\Service\SharedStorageInterface;
use Sylius\Component\Core\Cart\Modifier\LimitingOrderItemQuantityModifier;
use Sylius\Component\Core\Model\Address;
use Sylius\Component\Core\Model\AddressInterface;
use Sylius\Component\Core\Model\AdjustmentInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\Payment;
use Sylius\Component\Core\Model\PaymentMethod;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Core\Model\Shipment;
use Sylius\Component\Core\Model\ShippingMethod;
use Sylius\Component\Core\OrderPaymentStates;
use Sylius\Component\Core\Repository\OrderRepositoryInterface;
use Sylius\Component\Customer\Model\CustomerInterface;
use Sylius\Component\Order\Factory\AdjustmentFactory;
use Sylius\Component\Order\Model\Adjustment;
use Sylius\Component\Order\Modifier\OrderItemQuantityModifierInterface;
use Sylius\Component\Payment\Factory\PaymentFactory;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;

class AppOrderContext implements Context
{
    /**
     * @var SharedStorageInterface
     */
    private $sharedStorage;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var FactoryInterface
     */
    private $orderFactory;

    /**
     * @var FactoryInterface
     */
    private $orderItemFactory;

    /**
     * @var OrderItemQuantityModifierInterface
     */
    private $itemQuantityModifier;

    /**
     * @var PaymentFactory
     */
    private $paymentFactory;

    /**
     * @var FactoryInterface
     */
    private $shipmentFactory;

    /**
     * @var AdjustmentFactory
     */
    private $adjustmentFactory;

    /**
     * @var PaymentMethodRepository
     */
    private $paymentMethodRepository;

    /**
     * @var ShippingMethodRepository
     */
    private $shippingMethodRepository;

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var RepositoryInterface
     */
    private $productVariantRepo;

    /**
     * @var LimitingOrderItemQuantityModifier
     */
    private $orderItemQuantityModifier;

    /**
     * @var CompositeOrderProcessor
     */
    private $compositeOrderProcessor;

    /**
     * @param SharedStorageInterface $sharedStorage
     * @param OrderRepositoryInterface $orderRepository
     * @param FactoryInterface $orderFactory
     * @param FactoryInterface $orderItemFactory
     * @param OrderItemQuantityModifierInterface $itemQuantityModifier
     * @param PaymentFactory $paymentFactory
     * @param FactoryInterface $shipmentFactory
     * @param AdjustmentFactory $adjustmentFactory
     * @param PaymentMethodRepository $paymentMethodRepository
     * @param ShippingMethodRepository $shippingMethodRepository
     * @param EntityManager $em
     * @param RepositoryInterface $productVariantRepo
     * @param LimitingOrderItemQuantityModifier $orderItemQuantityModifier
     */
    public function __construct(
        SharedStorageInterface $sharedStorage,
        OrderRepositoryInterface $orderRepository,
        FactoryInterface $orderFactory,
        FactoryInterface $orderItemFactory,
        OrderItemQuantityModifierInterface $itemQuantityModifier,
        PaymentFactory $paymentFactory,
        FactoryInterface $shipmentFactory,
        AdjustmentFactory $adjustmentFactory,
        PaymentMethodRepository $paymentMethodRepository,
        ShippingMethodRepository $shippingMethodRepository,
        EntityManager $em,
        RepositoryInterface $productVariantRepo,
        LimitingOrderItemQuantityModifier $orderItemQuantityModifier
    ) {
        $this->sharedStorage = $sharedStorage;
        $this->orderRepository = $orderRepository;
        $this->orderFactory = $orderFactory;
        $this->orderItemFactory = $orderItemFactory;
        $this->itemQuantityModifier = $itemQuantityModifier;
        $this->paymentFactory = $paymentFactory;
        $this->shipmentFactory = $shipmentFactory;
        $this->adjustmentFactory = $adjustmentFactory;
        $this->paymentMethodRepository = $paymentMethodRepository;
        $this->shippingMethodRepository = $shippingMethodRepository;
        $this->em = $em;
        $this->productVariantRepo = $productVariantRepo;
        $this->orderItemQuantityModifier = $orderItemQuantityModifier;
    }

    /**
     * @Given /^(this customer) has(?:| also) placed an order "([^"]+)" buying a single ("[^"]+" product) for ("[^"]+") on the ("[^"]+" channel) with paid state$/
     * @Given /^(this customer) has(?:| also) placed an order "([^"]+)" buying a single ("[^"]+" product) for ("[^"]+") on the ("[^"]+" channel) with paid state, which is ("[^"]+")$/
     *
     * @param CustomerInterface $customer
     * @param $orderNumber
     * @param ProductInterface $product
     * @param $price
     * @param ChannelInterface $channel
     * @param null $older
     *
     * @throws
     */
    public function customerHasPlacedAnOrderBuyingASingleProductForOnTheChannel(
        CustomerInterface $customer,
        $orderNumber,
        ProductInterface $product,
        $price,
        ChannelInterface $channel,
        $older = null
    ) {
        if ($older && $older === '"older"') {
            $order = $this->createOrder($customer, $orderNumber, $channel, 'en_US', new \DateTime('-1 month'));
        } else {
            $order = $this->createOrder($customer, $orderNumber, $channel);
        }
        $order->setState(OrderInterface::STATE_NEW);
        $order->setShippingState('ready');

        $this->addVariantWithPriceToOrder($order, $product->getVariants()->first(), $price);
        $this->orderRepository->add($order);
        $this->sharedStorage->set('order', $order);
    }


    /**
     * @param CustomerInterface $customer
     * @param string $number
     * @param ChannelInterface|null $channel
     * @param string|null $localeCode
     *
     * @param null $creationDate
     * @return OrderInterface
     */
    private function createOrder(
        CustomerInterface $customer,
        $number = null,
        ChannelInterface $channel = null,
        $localeCode = null,
        $creationDate = null
    ) {
        $order = $this->createCart($customer, $channel, $localeCode);

        if (null !== $number) {
            $order->setNumber($number);
        }

        if ($creationDate && $creationDate instanceof \DateTime) {
            $order->setCheckoutCompletedAt($creationDate);
        } else {
            $order->completeCheckout();

        }

        $order->setPaymentState(OrderPaymentStates::STATE_PAID);

        return $order;
    }

    /**
     * @param CustomerInterface $customer
     * @param ChannelInterface|null $channel
     * @param string|null $localeCode
     *
     * @return OrderInterface
     */
    private function createCart(
        CustomerInterface $customer,
        ChannelInterface $channel = null,
        $localeCode = null
    ) {
        /** @var OrderInterface $order */
        $order = $this->orderFactory->createNew();

        /** @var AddressInterface */
        $address = $this->createAddress($customer, 'US', 'New Yourk', '22-44 Cuckoo Hall Ln', '45687');

        $order->setBillingAddress($address);
        $order->setShippingAddress($address);
        $order->setCustomer($customer);
        $order->setChannel($channel ?? $this->sharedStorage->get('channel'));
        $order->setLocaleCode($localeCode ?? $this->sharedStorage->get('locale')->getCode());
        $order->setCurrencyCode($order->getChannel()->getBaseCurrency()->getCode());

        return $order;
    }

    /**
     * @param CustomerInterface $customer
     * @param string $countryCode
     * @param string $city
     * @param string $street
     * @param string $postCode
     * @return Address
     */
    private function createAddress(
        CustomerInterface $customer,
        string $countryCode,
        string $city,
        string $street,
        string $postCode
    ) {
        $address = new Address();
        $address->setFirstName($customer->getFirstName());
        $address->setLastName($customer->getLastName());
        $address->setPhoneNumber($customer->getPhoneNumber());

        $address->setCity($city);
        $address->setCountryCode($countryCode);
        $address->setStreet($street);
        $address->setPostcode($postCode);

        return $address;
    }

    /**
     * @param OrderInterface $order
     * @param ProductVariantInterface $variant
     * @param int $price
     */
    private function addVariantWithPriceToOrder(OrderInterface $order, ProductVariantInterface $variant, $price)
    {
        /** @var OrderItem $item */
        $item = $this->orderItemFactory->createNew();
        $item->setVariant($variant);
        $item->setUnitPrice($price);

        $this->itemQuantityModifier->modify($item, 1);

        $order->addItem($item);
    }

    /**
     * @Given order :number has assigned payment method with code :paymentMethodCode
     */
    public function assignPaymentMethodToOrder($number, $paymentMethodCode)
    {
        /** @var Order $order */
        $order = $this->orderRepository->findOneBy(['number' => $number]);

        /** @var PaymentMethod $paymentMethod */
        $paymentMethod = $this->paymentMethodRepository->findOneBy(['code' => $paymentMethodCode]);

        $orderPayments = $order->getPayments();

        if(sizeof($orderPayments) == 0) {
            /** @var Payment $payment */
            $payment = $this->paymentFactory->createNew();

            $payment->setMethod($paymentMethod);
            $payment->setCurrencyCode($order->getCurrencyCode());

            $order->addPayment($payment);
        } else {
            $orderPayments[0]->setMethod($paymentMethod);
        }
        $this->em->persist($order);
        $this->em->flush();
    }

    /**
     * @Given order :number has assigned shipping method with code :shippingMethod
     */
    public function assignShippingMethodToOrder($number, $shippingMethod)
    {
        /** @var Order $order */
        $order = $this->orderRepository->findOneBy(['number' => $number]);

        /** @var ShippingMethod $shippingMethod */
        $shippingMethod = $this->shippingMethodRepository->findOneBy(['code' => $shippingMethod]);

        $orderShippingMethod = $order->getShipments();

        if(sizeof($orderShippingMethod) > 0) {
            $order->removeShipments();
        }

        /** @var Shipment $shipment */
        $shipment = $this->shipmentFactory->createNew();
        $shipment->setMethod($shippingMethod);

        $order->addShipment($shipment);

        $adjustments = $order->getAdjustments()->toArray();

        /** @var Adjustment $item */
        foreach ($adjustments as $item) {
            if ($item->getType() == AdjustmentInterface::SHIPPING_ADJUSTMENT)
            {
                $order->removeAdjustment($item);
            }
        }

        $adjustment = $this->adjustmentFactory->createNew();
        $adjustment->setType(AdjustmentInterface::SHIPPING_ADJUSTMENT);
        $adjustment->setAmount(1000);
        $adjustment->setLabel($shippingMethod->getCode());

        $order->addAdjustment($adjustment);

        $this->sharedStorage->set('order', $order);
        $this->em->persist($order);
        $this->em->flush();
    }
}
