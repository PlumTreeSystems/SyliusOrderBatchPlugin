<?php

namespace PTS\SyliusOrderBatchPlugin\Fixtures\Factory;

use Payum\Core\Model\Payment;
use Sylius\Bundle\CoreBundle\Order\NumberGenerator\SequentialOrderNumberGenerator;
use Sylius\Component\Core\Model\ChannelPricing;
use Sylius\Component\Core\Model\Order;
use Sylius\Component\Core\Model\OrderItemInterface;
use Sylius\Component\Core\Model\ProductVariant;
use Sylius\Component\Core\OrderCheckoutStates;
use Sylius\Component\Core\OrderPaymentStates;
use Sylius\Component\Core\OrderShippingStates;
use Sylius\Component\Order\Factory\AdjustmentFactory;
use Sylius\Component\Order\Model\OrderInterface;
use Sylius\Component\Order\Modifier\OrderItemQuantityModifierInterface;
use Sylius\Component\Payment\Factory\PaymentFactory;
use Sylius\Component\Product\Model\Product;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\Options;

class OrderFactory
{
    /**
     * @var OrderItemQuantityModifierInterface
     */
    private $orderItemQuantityModifier;

    /**
     * @var FactoryInterface
     */
    private $orderFactory;

    /**
     * @var FactoryInterface
     */
    private $orderItemFactory;

    /**
     * @var FactoryInterface
     */
    private $addressFactory;

    /**
     * @var FactoryInterface
     */
    private $customerFactory;

    /**
     * @var FactoryInterface
     */
    private $productFactory;

    /**
     * @var AdjustmentFactory
     */
    private $adjustmentFactory;

    /**
     * @var FactoryInterface
     */
    private $shipmentFactory;

    /**
     * @var PaymentFactory
     */
    private $paymentFactory;

    /**
     * @var RepositoryInterface
     */
    private $customerRepository;

    /**
     * @var RepositoryInterface
     */
    private $productRepository;

    /**
     * @var RepositoryInterface
     */
    private $channelRepository;

    /**
     * @var RepositoryInterface
     */
    private $channelPricingRepository;

    /**
     * @var RepositoryInterface
     */
    private $shippingMethodRepository;

    /**
     * @var RepositoryInterface
     */
    private $productVariantRepository;

    /**
     * @var RepositoryInterface
     */
    private $paymentRepository;

    /**
     * @var OptionsResolver
     */
    private $optionsResolver;

    /**
     * @var SequentialOrderNumberGenerator
     */
    private $orderNumberGenerator;

    /**
     * @var AutoshipRepository
     */
    private $autoshipRepository;

    /**
     * @var RepositoryInterface
     */
    private $orderRepository;

    /**
     * @param OrderItemQuantityModifierInterface $orderItemQuantityModifier
     * @param FactoryInterface $orderFactory
     * @param FactoryInterface $orderItemFactory
     * @param FactoryInterface $addressFactory
     * @param FactoryInterface $customerFactory
     * @param FactoryInterface $productFactory
     * @param AdjustmentFactory $adjustmentFactory
     * @param FactoryInterface $shipmentFactory
     * @param PaymentFactory $paymentFactory
     * @param RepositoryInterface $customerRepository
     * @param RepositoryInterface $productRepository
     * @param RepositoryInterface $channelRepository
     * @param RepositoryInterface $channelPricingRepository
     * @param RepositoryInterface $shippingMethodRepository
     * @param RepositoryInterface $productVariantRepository
     * @param RepositoryInterface $paymentRepository
     * @param SequentialOrderNumberGenerator $orderNumberGenerator
     * @param RepositoryInterface $orderRepository
     */

    public function __construct(
        OrderItemQuantityModifierInterface $orderItemQuantityModifier,
        FactoryInterface $orderFactory,
        FactoryInterface $orderItemFactory,
        FactoryInterface $addressFactory,
        FactoryInterface $customerFactory,
        FactoryInterface $productFactory,
        AdjustmentFactory $adjustmentFactory,
        FactoryInterface $shipmentFactory,
        PaymentFactory $paymentFactory,
        RepositoryInterface $customerRepository,
        RepositoryInterface $productRepository,
        RepositoryInterface $channelRepository,
        RepositoryInterface $channelPricingRepository,
        RepositoryInterface $shippingMethodRepository,
        RepositoryInterface $productVariantRepository,
        RepositoryInterface $paymentRepository,
        SequentialOrderNumberGenerator $orderNumberGenerator,
        RepositoryInterface $orderRepository
    )
    {
        $this->orderItemQuantityModifier = $orderItemQuantityModifier;
        $this->orderFactory = $orderFactory;
        $this->orderItemFactory = $orderItemFactory;
        $this->addressFactory = $addressFactory;
        $this->customerFactory = $customerFactory;
        $this->productFactory = $productFactory;
        $this->adjustmentFactory = $adjustmentFactory;
        $this->shipmentFactory = $shipmentFactory;
        $this->paymentFactory = $paymentFactory;
        $this->customerRepository = $customerRepository;
        $this->productRepository = $productRepository;
        $this->channelRepository = $channelRepository;
        $this->channelPricingRepository = $channelPricingRepository;
        $this->shippingMethodRepository = $shippingMethodRepository;
        $this->productVariantRepository = $productVariantRepository;
        $this->paymentRepository = $paymentRepository;
        $this->orderNumberGenerator = $orderNumberGenerator;
        $this->orderRepository = $orderRepository;

        $this->optionsResolver = new OptionsResolver();

        $this->configureOptions($this->optionsResolver);
    }

    public function create(array $options = [])
    {
        $options = $this->optionsResolver->resolve($options);

        /** @var Order $order */
        $order = $this->orderFactory->createNew();
        $order->setLocaleCode($options['localeCode']);
        $order->setCurrencyCode($options['currencyCode']);
        $order->setCheckoutCompletedAt(new \DateTime());

        if (is_null($options['number'])) {
            $number = $this->orderNumberGenerator->generate($order);

            $order->setNumber($number);
            $order->setTokenValue($number);
        } else {
            $order->setNumber($options['number']);
            $order->setTokenValue($options['number']);
        }

        $this->setChannel($order, $options);
        $this->setCustomer($order, $options);

        $order->setNotes($options['notes']);
        $order->setState($options['state']);
        $order->setCheckoutState($options['checkoutState']);
        $order->setPaymentState($options['paymentState']);
        $order->setShippingState($options['shippingState']);

        if (array_key_exists('billingAddress', $options)) {
            $billingAddress = $options['billingAddress'];
        } else {
            $billingAddress = $options['shippingAddress'];
        }

        $this->setBillingAddress($order, $billingAddress);
        $this->setShippingAddress($order, $options['shippingAddress']);
        $this->setItems($order, $options);


        if (is_string($options['checkoutCompletedAt'])) {
            $order->setCheckoutCompletedAt(new \DateTime($options['checkoutCompletedAt']));
        } else {
            $order->setCheckoutCompletedAt($options['checkoutCompletedAt']);
        }

        if (!is_null($options['payments'])) {
            $this->setPayment($order, $options['payments']);
        }


        $this->setAdjustments($order, $options['adjustments']);
        $this->addShipping($order, $options['shipments']);


        return $order;
    }

    private function setCustomer(Order $order, $options)
    {
        if (is_string($options['customer'])) {
            $customer = $this->customerRepository->findOneBy(['email' => $options['customer']]);
        } else {
            $options = $options['customer'];

            $customer = $this->customerRepository->findOneBy(['email' => $options['email']]);

            if (is_null($customer)) {

                /** @var Customer $customer */
                $customer = $this->customerFactory->createNew();

                $customer->setEmail($options['email']);
                $customer->setEmailCanonical($options['email']);
                $customer->setFirstName($options['firstName']);
                $customer->setLastName($options['lastName']);
                $customer->setPhoneNumber($options['phone']);
            }
        }

        $order->setCustomer($customer);

    }

    private function setItems(Order $order, $options)
    {
        foreach ($options['items'] as $item) {
            /**
             * @var OrderItemInterface $orderItem
             */
            $orderItem = $this->orderItemFactory->createNew();

            if (array_key_exists('id', $item['variant'])) {
                $variant = $this->productVariantRepository->find($item['variant']['id']);
            } else {
                /**
                 * @var Product $product
                 */
                $product = $this->productRepository->findOneBy(['code' => $item['variant']['code']]);

                if (is_null($product)) {
                    $product = $this->productFactory->createNew();
                    $product->setCode($item['variant']['code']);
                    $variant = new ProductVariant();
                    $variant->setCode($item['variant']['code']);
                    $product->addVariant($variant);
                }

                $variant = $product->getVariants()->first();
            }

            $orderItem->setVariant($variant);

            $this->orderItemQuantityModifier->modify($orderItem, $item['quantity']);

            /**
             * @var \App\Entity\ChannelPricing $productChannelPrice
             */
            $productChannelPrice = $this->channelPricingRepository->findOneBy([
                'productVariant' => [$variant->getId()],
                'channelCode' => [$order->getChannel()->getCode()]
            ]);

            if (is_null($productChannelPrice)) {
                $channelPrice = new ChannelPricing();
                $channelPrice->setPrice(10);

                $productChannelPrice = $channelPrice;
            }


            $orderItem->setUnitPrice($productChannelPrice->getPrice());


            $orderItem->recalculateAdjustmentsTotal();

            $order->addItem($orderItem);
        }
    }

    private function setChannel(Order $order, $options)
    {
        $channel = $this->channelRepository->findOneBy(['code' => $options['channel']]);
        $order->setChannel($channel);
    }

    private function setShippingAddress(Order $order, $options)
    {
        $address = $this->addressFactory->createNew();

        $address->setFirstName($order->getCustomer()->getFirstName());
        $address->setLastName($order->getCustomer()->getLastName());
        $address->setCountryCode($options['countryCode']);
        $address->setCity($options['city']);
        $address->setStreet($options['street']);
        $address->setPostcode($options['postcode']);
        $address->setFirstName($options['firstName']);
        $address->setLastName($options['lastName']);

        if (array_key_exists('phoneNumber', $options)) {
            $address->setPhoneNumber($options['phoneNumber']);
        }

        $order->setShippingAddress($address);
    }

    private function setBillingAddress(Order $order, $options)
    {
        $address = $this->addressFactory->createNew();

        $address->setFirstName($order->getCustomer()->getFirstName());
        $address->setLastName($order->getCustomer()->getLastName());
        $address->setCountryCode($options['countryCode']);
        $address->setCity($options['city']);
        $address->setStreet($options['street']);
        $address->setPostcode($options['postcode']);
        $address->setFirstName($options['firstName']);
        $address->setLastName($options['lastName']);

        if (array_key_exists('phoneNumber', $options)) {
            $address->setPhoneNumber($options['phoneNumber']);
        }

        $order->setBillingAddress($address);
    }

    private function setAdjustments(Order $order, $options)
    {
        foreach ($options as $item) {
            $amount = $item['amount'];
            $label = $item['label'];
            $type = $item['type'];
            $adjustment = $this->adjustmentFactory->createWithData($type, $label, $amount);
            $order->addAdjustment($adjustment);
        }
    }

    private function addShipping(Order $order, $options)
    {
        foreach ($options as $item) {
            $shipment = $this->shipmentFactory->createNew();

            $code = $item['method']['code'];
            $shipmentMethod = $this->shippingMethodRepository->findOneBy(['code' => $code]);

            $shipment->setMethod($shipmentMethod);

            $order->addShipment($shipment);
        }
    }

    private function setPayment(Order $order, $options)
    {
        if (sizeof($options) == 0) {
            return;
        }
        /**
         * @var Payment $orderPayment
         */
        $orderPayment = $this->paymentRepository->find($options[0]['id']);

        if (is_null($orderPayment)) {
            return;
        }

        $payment = $this->paymentFactory->createNew();
        $payment->setState($orderPayment->getState());
        $payment->setAmount($orderPayment->getAmount());
        $payment->setCurrencyCode($orderPayment->getCurrencyCode());
        $payment->setDetails($orderPayment->getDetails());
        $payment->setMethod($orderPayment->getMethod());

        $order->addPayment($payment);
    }

    /**
     * {@inheritdoc}
     */
    protected function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefault('localeCode', function (Options $options): string {
                return 'en_US';
            })
            ->setDefault('currencyCode', function (Options $options): string {
                return 'USD';
            })
            ->setDefault('state', function (Options $options): string {
                return OrderInterface::STATE_NEW;
            })
            ->setDefault('checkoutState', function (Options $options): string {
                return OrderCheckoutStates::STATE_PAYMENT_SELECTED;
            })
            ->setDefault('paymentState', function (Options $options): string {
                return OrderPaymentStates::STATE_AWAITING_PAYMENT;
            })
            ->setDefault('shippingState', function (Options $options): string {
                return OrderShippingStates::STATE_CART;
            })
            ->setDefault('number', function (Options $options) {
                return null;
            })
            ->setDefault('shippingAddress', function (Options $options): array {
                return [
                    'countryCode' => 'GB',
                    'city' => 'London',
                    'street' => 'Test 81 Brewer St',
                    'postcode' => 'TEST W1F 7ED',
                    'firstName' => 'test',
                    'lastName' => 'testino'
                ];
            })
            ->setDefault('billingAddress', function (Options $options): array {
                return [
                    'countryCode' => 'GB',
                    'city' => 'London',
                    'street' => 'Test 999 Brewer St',
                    'postcode' => 'TEST W1F 123',
                    'firstName' => 'test',
                    'lastName' => 'testino'
                ];
            })
            ->setDefault('channel', function (Options $options): string {
                return 'US_WEB';
            })
            ->setDefault('customer', function (Options $options): array {
                return [
                    'email' => 'test@mailinator.com',
                    'phone' => '+48157954723',
                    'firstName' => 'Testo',
                    'lastName' => 'Testino'
                ];
            })
            ->setDefault('items', function (Options $options): array {
                return [
                    [
                        'variant' => [
                            'code' => '0010'
                        ],
                        'quantity' => 1,
                        'autoship' => true
                    ],
                    [
                        'variant' => [
                            'code' => '0008'
                        ],
                        'quantity' => 1,
                        'autoship' => false
                    ]
                ];
            })
            ->setDefault('adjustments', function (Options $options): array {
                return [
                    [
                        'amount' => 3738,
                        'label' => 'DHL Express',
                        'type' => 'shipping'
                    ]
                ];
            })
            ->setDefault('shipments', function (Options $options): array {
                return [
                    'shipments' => [
                        'method' => [
                            'code' => 'ups'
                        ]
                    ]
                ];
            })
            ->setDefault('checkoutCompletedAt', function (Options $options): string {
                $date = new \DateTime();
                return $date->format(\DateTimeInterface::W3C);
            })
            ->setDefault('payments', function (Options $options) {
                return null;
            })
            ->setDefault('notes', function (Options $options) {
                return null;
            })
            ->setDefault('tags', function (Options $options) {
                return null;
            })
            ->setDefault('children', function (Options $options) {
                return null;
            })
            ->setDefault('parent', function (Options $options) {
                return null;
            });
    }
}
