<?php

namespace PTS\SyliusOrderBatchPlugin\Entity;

use Sylius\Component\Resource\Model\ResourceInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="filter")
 * @UniqueEntity("filterName", message="Filter name already exists!")
 */
class Filter implements ResourceInterface
{
    private $id;

    /**
     * @Assert\NotBlank(message="filter name can't be blank")
     */
    private $filterName;

    private $numberType;

    private $numberValue;

    private $customerType;

    private $customerValue;

    private $dateFrom;

    private $dateTo;

    private $channel;

    private $totalGreaterThan;

    private $totalLessThan;

    private $totalCurrency;

    private $orderState;

    private $paymentState;

    private $shippingState;

    private $shippingCountry;

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getFilterName()
    {
        return $this->filterName;
    }

    /**
     * @param $filterName string
     */
    public function setFilterName($filterName)
    {
        $this->filterName = $filterName;
    }

    /**
     * @return string
     */
    public function getNumberType()
    {
        return $this->numberType;
    }

    /**
     * @param $numberType string
     */
    public function setNumberType($numberType)
    {
        $this->numberType = $numberType;
    }

    /**
     * @return string
     */
    public function getNumberValue()
    {
        return $this->numberValue;
    }

    /**
     * @param $numberValue string
     */
    public function setNumberValue($numberValue)
    {
        $this->numberValue = $numberValue;
    }

    /**
     * @return string
     */
    public function getCustomerType()
    {
        return $this->customerType;
    }

    /**
     * @param $customerType string
     */
    public function setCustomerType($customerType)
    {
        $this->customerType = $customerType;
    }

    /**
     * @return string
     */
    public function getCustomerValue()
    {
        return $this->customerValue;
    }

    /**
     * @param $customerValue string
     */
    public function setCustomerValue($customerValue)
    {
        $this->customerValue = $customerValue;
    }

    /**
     * @return \DateTime
     */
    public function getDateFrom()
    {
        return $this->dateFrom;
    }

    /**
     * @param $dateFrom \DateTime
     */
    public function setDateFrom($dateFrom)
    {
        $this->dateFrom = $dateFrom;
    }

    /**
     * @return \DateTime
     */
    public function getDateTo()
    {
        return $this->dateTo;
    }

    /**
     * @param $dateTo \DateTime
     */
    public function setDateTo($dateTo)
    {
        $this->dateTo = $dateTo;
    }

    /**
     * @return string
     */
    public function getChannel()
    {
        return $this->channel;
    }

    /**
     * @param $channel string
     */
    public function setChannel($channel)
    {
        $this->channel = $channel;
    }

    /**
     * @return string
     */
    public function getTotalGreaterThan()
    {
        return $this->totalGreaterThan;
    }

    /**
     * @param $totalGreaterThan string
     */
    public function setTotalGreaterThan($totalGreaterThan)
    {
        $this->totalGreaterThan = $totalGreaterThan;
    }

    /**
     * @return string
     */
    public function getTotalLessThan()
    {
        return $this->totalLessThan;
    }

    /**
     * @param $totalLessThan string
     */
    public function setTotalLessThan($totalLessThan)
    {
        $this->totalLessThan = $totalLessThan;
    }

    /**
     * @return string
     */
    public function getTotalCurrency()
    {
        return $this->totalCurrency;
    }

    /**
     * @param $totalCurrency string
     */
    public function setTotalCurrency($totalCurrency)
    {
        $this->totalCurrency = $totalCurrency;
    }

    /**
     * @return string
     */
    public function getOrderState()
    {
        return $this->orderState;
    }

    /**
     * @param $orderState string
     */
    public function setOrderState($orderState)
    {
        $this->orderState = $orderState;
    }

    /**
     * @return string
     */
    public function getPaymentState()
    {
        return $this->paymentState;
    }

    /**
     * @param $paymentState string
     */
    public function setPaymentState($paymentState)
    {
        $this->paymentState = $paymentState;
    }

    /**
     * @return string
     */
    public function getShippingState()
    {
        return $this->shippingState;
    }

    /**
     * @param $shippingState string
     */
    public function setShippingState($shippingState)
    {
        $this->shippingState = $shippingState;
    }

    /**
     * @return string
     */
    public function getShippingCountry()
    {
        return $this->shippingCountry;
    }

    /**
     * @param $shippingCountry string
     */
    public function setShippingCountry($shippingCountry)
    {
        $this->shippingCountry = $shippingCountry;
    }
}
