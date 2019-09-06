<?php

namespace PTS\SyliusOrderBatchPlugin\Entity;

use Sylius\Component\Core\Model\Order;
use Sylius\Component\Resource\Model\ResourceInterface;
use PlumTreeSystems\FileBundle\Entity\File as PTSFile;

/**
 * Order invoice document
 */
class Document extends PTSFile implements ResourceInterface
{
    private $id;

    private $orders;

    private $code;

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getOrders()
    {
        return $this->orders;
    }

    /**
     * Add order
     *
     * @param Order $order
     *
     * @return Document
     */
    public function addOrder(Order $order)
    {
        $this->orders[] = $order;

        return $this;
    }

    /**
     * Remove order
     *
     * @param Order $order
     */
    public function removeOrder(Order $order)
    {
        $this->orders->removeElement($order);
    }

    /**
     * @param string $code
     *
     * @return Document
     */
    public function setCode($code) {
        $this->code = $code;

        return $this;
    }

    /**
     * @return string
     */
    public function getCode() {
        return $this->code;
    }
}
