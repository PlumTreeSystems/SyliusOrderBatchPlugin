<?php

namespace PTS\SyliusOrderBatchPlugin\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Sylius\Component\Core\Model\Order;
use Sylius\Component\Resource\Model\ResourceInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="batch")
 * @UniqueEntity("name", message="Batch name already exists!")
 */
class Batch implements ResourceInterface
{
    private $id;

    /**
     * @Assert\NotBlank(message="Batch name can't be blank!")
     */
    private $name;

    private $orders;

    private $type;

    public function __construct()
    {
        $this->orders = new ArrayCollection();
    }

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
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param $name string
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getOrders()
    {
        return $this->orders;
    }

    /**
     * @param $orders mixed
     */
    public function setOrders($orders)
    {
        $this->orders = $orders;
    }

    /**
     * @param Order $order
     */
    public function removeOrder($order)
    {
        $this->orders->removeElement($order);
    }

    /**
     * @param Order $order
     */
    public function addOrder(Order $order)
    {
        if (!$this->hasOrder($order)) {
            $this->orders->add($order);
        }
    }

    /**
     * @param mixed $order
     * @return boolean
     */
    public function hasOrder($order): bool
    {
        return $this->orders->contains($order);
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->orders;
    }

    /**
     * @param mixed $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }
}
