<?php

namespace PTS\SyliusOrderBatchPlugin\Entity;


use Sylius\Component\Resource\Model\ResourceInterface;

/**
 * Document template
 */
class DocumentTemplate implements ResourceInterface
{
    private $id;

    private $code;

    private $content;

    private $style;

    private $title;

    private $lastModified;

    private $locale;

    private $templateData;


    public function __construct()
    {
        $this->lastModified = new \DateTime();
    }

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set code
     *
     * @param string $code
     *
     * @return DocumentTemplate
     */
    public function setCode($code)
    {
        $this->code = $code;
        $this->setLastModified();

        return $this;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set content
     *
     * @param string $content
     *
     * @return DocumentTemplate
     */
    public function setContent($content)
    {
        $this->content = $content;
        $this->setLastModified();

        return $this;
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set content
     *
     * @param string $style
     *
     * @return DocumentTemplate
     */
    public function setStyle($style)
    {
        $this->style = $style;
        $this->setLastModified();

        return $this;
    }

    /**
     * @return string
     */
    public function getStyle()
    {
        return $this->style;
    }

    /**
     * Set title
     *
     * @param string $title
     *
     * @return DocumentTemplate
     */
    public function setTitle($title)
    {
        $this->title = $title;
        $this->setLastModified();

        return $this;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set lastModified
     *
     * @return DocumentTemplate
     */
    public function setLastModified()
    {
        $this->lastModified = new \DateTime();

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getLastModified()
    {
        return $this->lastModified;
    }

    /**
     * Set locale
     *
     * @param string $locale
     *
     * @return DocumentTemplate
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;
        $this->setLastModified();

        return $this;
    }

    /**
     * @return mixed
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * Set templateData
     *
     * @param array $templateData
     *
     * @return DocumentTemplate
     */
    public function setTemplateData(array $templateData)
    {
        $this->templateData = json_encode($templateData);
        $this->setLastModified();

        return $this;
    }

    /**
     * @return array
     */
    public function getTemplateData()
    {
        if ($this->templateData) {
            return json_decode($this->templateData, true);
        }
        return [];
    }
}
