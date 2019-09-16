<?php


namespace Tests\PTS\SyliusOrderBatchPlugin\Behat\Context\Setup;

use Behat\Behat\Context\Context;
use Doctrine\ORM\EntityManager;
use http\Exception\InvalidArgumentException;
use Sylius\Behat\Service\SharedStorageInterface;
use Sylius\Bundle\ChannelBundle\Doctrine\ORM\ChannelRepository;
use Sylius\Component\Core\Formatter\StringInflector;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\Product;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Core\Model\ProductVariant;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\Component\Core\Repository\ProductRepositoryInterface;
use Sylius\Component\Product\Factory\ProductFactoryInterface;
use Sylius\Component\Product\Factory\ProductVariantFactory;
use Sylius\Component\Product\Generator\SlugGeneratorInterface;
use Sylius\Component\Product\Resolver\ProductVariantResolverInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;

class AppProductContext implements Context
{
    /**
     * @var SharedStorageInterface
     */
    private $sharedStorage;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var ProductFactoryInterface
     */
    private $productFactory;

    /**
     * @var ProductVariantResolverInterface
     */
    private $defaultVariantResolver;

    /**
     * @var SlugGeneratorInterface
     */
    private $slugGenerator;

    /**
     * @var ChannelRepository
     */
    private $channelRepository;

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var ProductVariantFactory
     */
    private $productVariantFactory;

    /**
     * @param SharedStorageInterface $sharedStorage
     * @param ProductRepositoryInterface $productRepository
     * @param ProductFactoryInterface $productFactory
     * @param ProductVariantResolverInterface $defaultVariantResolver
     * @param SlugGeneratorInterface $slugGenerator
     * @param ChannelRepository $channelRepository
     * @param EntityManager $entityManager
     * @param ProductVariantFactory $productVariantFactory
     */
    public function __construct(
        SharedStorageInterface $sharedStorage,
        ProductRepositoryInterface $productRepository,
        ProductFactoryInterface $productFactory,
        ProductVariantResolverInterface $defaultVariantResolver,
        SlugGeneratorInterface $slugGenerator,
        ChannelRepository $channelRepository,
        EntityManager $entityManager,
        ProductVariantFactory $productVariantFactory
    )
    {
        $this->sharedStorage = $sharedStorage;
        $this->productRepository = $productRepository;
        $this->productFactory = $productFactory;
        $this->defaultVariantResolver = $defaultVariantResolver;
        $this->slugGenerator = $slugGenerator;
        $this->channelRepository = $channelRepository;
        $this->entityManager = $entityManager;
        $this->productVariantFactory = $productVariantFactory;
    }

    /**
     * @Given /^the store(?:| also) has a product "([^"]+)" priced at ("[^"]+") on the ("[^"]+" channel)$/
     * @Given /^the store(?:| also) has a product "([^"]+)" priced at ("[^"]+")$/
     */
    public function storeHasAProductPricedAt($productName, $price = 100, ChannelInterface $channel = null)
    {
        $product = $this->createProduct($productName, $price, $channel);

        $this->saveProduct($product);
    }


    /**
     * @param string $productName
     * @param int $price
     * @param ChannelInterface|null $channel
     *
     * @return Product
     */
    private function createProduct($productName, $price = 100, ChannelInterface $channel = null)
    {
        /** @var Product $product */
        $product = $this->productFactory->createWithVariant();

        $product->setCode(StringInflector::nameToUppercaseCode($productName));
        $product->setName($productName);
        $product->setSlug($this->slugGenerator->generate($productName));

        /** @var ProductVariantInterface $productVariant */
        $productVariant = $this->defaultVariantResolver->getVariant($product);
        if (null !== $channel) {
            $product->addChannel($channel);
            foreach ($channel->getLocales() as $locale) {
                $product->setFallbackLocale($locale->getCode());
                $product->setCurrentLocale($locale->getCode());

                $product->setName($productName);
                $product->setSlug($this->slugGenerator->generate($productName));
            }
        } else {
            $channels = $this->channelRepository->findAll();
            foreach ($channels as $channel) {
                $product->addChannel($channel);
                foreach ($channel->getLocales() as $locale) {
                    $product->setFallbackLocale($locale->getCode());
                    $product->setCurrentLocale($locale->getCode());

                    $product->setName($productName);
                    $product->setSlug($this->slugGenerator->generate($productName));
                }
            }
        }

        $productVariant->setCode($product->getCode());
        $productVariant->setName($product->getName());

        return $product;
    }

    /**
     * @param Product $product
     */
    private function saveProduct(Product $product)
    {
        $this->productRepository->add($product);
        $this->sharedStorage->set('product', $product);
    }

    /**
     * @Given /^the (product "[^"]+") has(?:| a) "([^"]+)" variant priced at ("[^"]+") with autoship price$/
     * @Given /^(this product) has "([^"]+)" variant priced at ("[^"]+") with autoship price$/
     * @Given /^(this product) has "([^"]+)" variant priced at ("[^"]+") with autoship price in ("([^"]+)" channel)$/
     */
    public function theProductHasVariantPricedAt(
        ProductInterface $product,
        $productVariantName,
        $price,
        ChannelInterface $channel = null
    )
    {
        $this->createProductVariantWithAutoship(
            $product,
            $productVariantName,
            $price,
            StringInflector::nameToUppercaseCode($productVariantName),
            $channel ?? $this->sharedStorage->get('channel')
        );
    }


    /**
     * @Given /^there is ([^"]+) units in stock of (this product)$/
     */

    public function setProductStock($amount, ProductInterface $product)
    {
        /** @var ProductVariant $variant */
        $variant = $product->getVariants()[0];
        $variant->setOnHand($amount);

        $this->entityManager->persist($product);
        $this->entityManager->flush();
    }

    /**
     * @Given /^there is ([^"]+) units in stock of "([^"]+)" product$/
     */
    public function setAmountOfThisProductInStock($amount, $productName)
    {
        $productCode = StringInflector::nameToUppercaseCode($productName);
        $product = $this->productRepository->findOneBy(['code' => $productCode]);

        $productVariant = $product->getVariants()[0];
        $productVariant->setOnHand($amount);

        $this->entityManager->flush();
    }
}
