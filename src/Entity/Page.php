<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Core\Annotation\ApiResource;

/**
 * @ApiResource
 * @ORM\Entity(repositoryClass="App\Repository\PageRepository")
 */
class Page
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    protected $pageTitle;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Locale")
     */
    protected $locale;

    /**
     * @ORM\Column(type="string", length=255)
     */
    protected $pageRoute;

    /**
     * @ORM\Column(type="text")
     */
    protected $content;

    /**
     * @ORM\Column(type="string", length=255)
     */
    protected $metaTitle;

    /**
     * @ORM\Column(type="string", length=255)
     */
    protected $metaKeywords;

    /**
     * @ORM\Column(type="string", length=255)
     */
    protected $metaDescription;

    /**
     * @ORM\Column(type="text")
     */
    protected $metaCustom;

    /**
     * @ORM\Column(type="date")
     */
    protected $publishDate;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    protected $expireDate;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $status = 0;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $pageWidth;

    /**
     * @ORM\Column(type="integer")
     */
    protected $disableLayout = 0;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $mainImage;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $customCss;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $customJs;

    /**
     * Get the value of Id
     *
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set the value of Id
     *
     * @param mixed id
     *
     * @return self
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get the value of Page Title
     *
     * @return mixed
     */
    public function getPageTitle()
    {
        return $this->pageTitle;
    }

    /**
     * Set the value of Page Title
     *
     * @param mixed pageTitle
     *
     * @return self
     */
    public function setPageTitle($pageTitle)
    {
        $this->pageTitle = $pageTitle;

        return $this;
    }

    /**
     * Get the value of Locale
     *
     * @return mixed
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * Set the value of Locale
     *
     * @param mixed locale
     *
     * @return self
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;

        return $this;
    }

    /**
     * Get the value of Page Route
     *
     * @return mixed
     */
    public function getPageRoute()
    {
        return $this->pageRoute;
    }

    /**
     * Set the value of Page Route
     *
     * @param mixed pageRoute
     *
     * @return self
     */
    public function setPageRoute($pageRoute)
    {
        $this->pageRoute = $pageRoute;

        return $this;
    }

    /**
     * Get the value of Content
     *
     * @return mixed
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set the value of Content
     *
     * @param mixed content
     *
     * @return self
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Get the value of Meta Title
     *
     * @return mixed
     */
    public function getMetaTitle()
    {
        return $this->metaTitle;
    }

    /**
     * Set the value of Meta Title
     *
     * @param mixed metaTitle
     *
     * @return self
     */
    public function setMetaTitle($metaTitle)
    {
        $this->metaTitle = $metaTitle;

        return $this;
    }

    /**
     * Get the value of Meta Keywords
     *
     * @return mixed
     */
    public function getMetaKeywords()
    {
        return $this->metaKeywords;
    }

    /**
     * Set the value of Meta Keywords
     *
     * @param mixed metaKeywords
     *
     * @return self
     */
    public function setMetaKeywords($metaKeywords)
    {
        $this->metaKeywords = $metaKeywords;

        return $this;
    }

    /**
     * Get the value of Meta Description
     *
     * @return mixed
     */
    public function getMetaDescription()
    {
        return $this->metaDescription;
    }

    /**
     * Set the value of Meta Description
     *
     * @param mixed metaDescription
     *
     * @return self
     */
    public function setMetaDescription($metaDescription)
    {
        $this->metaDescription = $metaDescription;

        return $this;
    }

    /**
     * Get the value of Meta Custom
     *
     * @return mixed
     */
    public function getMetaCustom()
    {
        return $this->metaCustom;
    }

    /**
     * Set the value of Meta Custom
     *
     * @param mixed metaCustom
     *
     * @return self
     */
    public function setMetaCustom($metaCustom)
    {
        $this->metaCustom = $metaCustom;

        return $this;
    }

    /**
     * Get the value of Publish Date
     *
     * @return mixed
     */
    public function getPublishDate()
    {
        return $this->publishDate;
    }

    /**
     * Set the value of Publish Date
     *
     * @param mixed publishDate
     *
     * @return self
     */
    public function setPublishDate($publishDate)
    {
        $this->publishDate = $publishDate;

        return $this;
    }

    /**
     * Get the value of Expire Date
     *
     * @return mixed
     */
    public function getExpireDate()
    {
        return $this->expireDate;
    }

    /**
     * Set the value of Expire Date
     *
     * @param mixed expireDate
     *
     * @return self
     */
    public function setExpireDate($expireDate)
    {
        $this->expireDate = $expireDate;

        return $this;
    }

    /**
     * Get the value of Status
     *
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set the value of Status
     *
     * @param mixed status
     *
     * @return self
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get the value of Page Width
     *
     * @return mixed
     */
    public function getPageWidth()
    {
        return $this->pageWidth;
    }

    /**
     * Set the value of Page Width
     *
     * @param mixed pageWidth
     *
     * @return self
     */
    public function setPageWidth($pageWidth)
    {
        $this->pageWidth = $pageWidth;

        return $this;
    }

    /**
     * Get the value of Disable Layout
     *
     * @return mixed
     */
    public function getDisableLayout()
    {
        return $this->disableLayout;
    }

    /**
     * Set the value of Disable Layout
     *
     * @param mixed disableLayout
     *
     * @return self
     */
    public function setDisableLayout($disableLayout)
    {
        $this->disableLayout = $disableLayout;

        return $this;
    }

    /**
     * Get the value of Main Image
     *
     * @return mixed
     */
    public function getMainImage()
    {
        return $this->mainImage;
    }

    /**
     * Set the value of Main Image
     *
     * @param mixed mainImage
     *
     * @return self
     */
    public function setMainImage($mainImage)
    {
        $this->mainImage = $mainImage;

        return $this;
    }

    /**
     * Get the value of Custom Css
     *
     * @return mixed
     */
    public function getCustomCss()
    {
        return $this->customCss;
    }

    /**
     * Set the value of Custom Css
     *
     * @param mixed customCss
     *
     * @return self
     */
    public function setCustomCss($customCss)
    {
        $this->customCss = $customCss;

        return $this;
    }

    /**
     * Get the value of Custom Js
     *
     * @return mixed
     */
    public function getCustomJs()
    {
        return $this->customJs;
    }

    /**
     * Set the value of Custom Js
     *
     * @param mixed customJs
     *
     * @return self
     */
    public function setCustomJs($customJs)
    {
        $this->customJs = $customJs;

        return $this;
    }

}
