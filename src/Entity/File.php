<?php


namespace PTS\SyliusOrderBatchPlugin\Entity;

use Sylius\Component\Resource\Model\ResourceInterface;
use Doctrine\ORM\Mapping as ORM;
use PlumTreeSystems\FileBundle\Entity\File as PTSFile;

/**
 * File
 * @ORM\Entity
 * @ORM\Table(name="simply_shop_file")
 */
class File extends PTSFile implements ResourceInterface
{
    private $id;

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param integer $id
     * @return File
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }
}
