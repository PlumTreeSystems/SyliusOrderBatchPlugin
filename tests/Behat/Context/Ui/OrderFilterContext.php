<?php


namespace Tests\PTS\SyliusOrderBatchPlugin\Behat\Context\Ui;

use PTS\SyliusOrderBatchPlugin\Entity\Filter;
use Behat\Behat\Context\Context;
use Doctrine\Common\Persistence\ObjectManager;

class OrderFilterContext implements Context
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @param ObjectManager $objectManager
     */
    public function __construct(
        ObjectManager $objectManager
    ) {
        $this->objectManager = $objectManager;
    }

    /**
     * @Given /^Store has filter "([^"]+)" for "([^"]+)" with value "([^"]+)"$/
     */
    public function createFilter($filterName, $field, $value)
    {
        $filter = new Filter();
        $filter->setFilterName($filterName);

        switch ($field) {
            case 'Channel':
                $filter->setChannel($value);
                break;
            case 'State':
                $filter->setOrderState($value);
                break;
            case 'Payment state':
                $filter->setPaymentState($value);
                break;
            case 'Shipping state':
                $filter->setShippingState($value);
                break;
            case 'Total less than':
                $filter->setTotalLessThan($value);
                break;
            case 'Greater than':
                $filter->setTotalGreaterThan($value);
                break;
            case 'Currency':
                $filter->setTotalCurrency($value);
                break;
            case 'Date from':
                $filter->setDateFrom($value);
                break;
            case 'Date to':
                $filter->setDateTo($value);
                break;
        }

        $this->objectManager->persist($filter);
        $this->objectManager->flush();
    }

    /**
     * @Given /^Store has filter "([^"]+)" for "([^"]+)" selected "([^"]+)" with value "([^"]+)"$/
     */
    public function createFilterWithType($filterName, $field, $type, $value)
    {
        $filter = new Filter();
        $filter->setFilterName($filterName);

        switch ($field) {
            case 'Number':
                $filter->setNumberType($type);
                $filter->setNumberValue($value);
                break;
            case 'Customer':
                $filter->setCustomerType($type);
                $filter->setCustomerValue($value);
                break;
        }

        $this->objectManager->persist($filter);
        $this->objectManager->flush();
    }
}