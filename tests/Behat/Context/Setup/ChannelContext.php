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

namespace Tests\PTS\SyliusOrderBatchPlugin\Behat\Context\Setup;

use Behat\Behat\Context\Context;
use Doctrine\ORM\EntityManager;
use Sylius\Component\Addressing\Factory\ZoneFactoryInterface;
use Sylius\Component\Addressing\Model\Country;
use Sylius\Component\Addressing\Model\Zone;
use Sylius\Component\Addressing\Model\ZoneInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;

final class ChannelContext implements Context
{
    private $sharedStorage;

    private $defaultChannelFactory;

    /** @var FactoryInterface */
    private $countryFactory;

    /** @var EntityManager */
    private $entityManager;

    /** @var ZoneFactoryInterface */
    private $zoneFactory;

    /**
     * ChannelContext constructor.
     * @param $sharedStorage
     * @param $defaultChannelFactory
     * @param FactoryInterface $countryFactory
     * @param EntityManager $entityManager
     * @param ZoneFactoryInterface $zoneFactory
     */
    public function __construct($sharedStorage, $defaultChannelFactory, FactoryInterface $countryFactory, EntityManager $entityManager, ZoneFactoryInterface $zoneFactory)
    {
        $this->sharedStorage = $sharedStorage;
        $this->defaultChannelFactory = $defaultChannelFactory;
        $this->countryFactory = $countryFactory;
        $this->entityManager = $entityManager;
        $this->zoneFactory = $zoneFactory;
    }


    /**
     * @Given the store operates on a channel identified by non-lowercase :code code
     * @param $channelCode
     */
    public function theStoreOperatesOnAChannelByCode($channelCode, $currencyCode = null)
    {
        if (!$currencyCode) {
            $currencyCode = 'USD';
        }
        $defaultData = $this->defaultChannelFactory->create($channelCode, $channelCode, $currencyCode);
        $this->sharedStorage->setClipboard($defaultData);
        $this->sharedStorage->set('channel', $defaultData['channel']);
    }

    /**
     * @Given the store has a country identified by code :countryCode
     * @param $countryCode
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function theStoreHasACountryIdentifiedByCode($countryCode)
    {
        /** @var Country $country */
        $country = $this->countryFactory->createNew();
        $country->setCode($countryCode);
        /** @var Zone $countryZone */
        $countryZone = $this->zoneFactory->createWithMembers([$countryCode]);
        $countryZone->setCode($countryCode);
        $countryZone->setName('United States');
        $countryZone->setType(ZoneInterface::TYPE_COUNTRY);
        $this->sharedStorage->set('zone', $countryZone);

        $this->entityManager->persist($countryZone);
        $this->entityManager->persist($country);
        $this->entityManager->flush();
    }
}
