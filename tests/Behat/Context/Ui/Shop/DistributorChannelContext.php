<?php

namespace Tests\PTS\SyliusOrderBatchPlugin\Behat\Context\Ui\Shop;

use Behat\Behat\Context\Context;
use Sylius\Behat\Page\Shop\HomePageInterface;

class DistributorChannelContext implements Context
{
    /**
     * @var HomePageInterface
     */
    private $homePage;

    /**
     * DistributorChannelContext constructor.
     * @param HomePageInterface $homePage
     */
    public function __construct(HomePageInterface $homePage)
    {
        $this->homePage = $homePage;
    }

    /**
     * @Then I find :arg1
     */
    public function iFind($arg1)
    {
        $this->homePage->getContents();
    }
}
