<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
* @ORM\Entity(repositoryClass="App\Repository\TemplateRepository")
*/
class Template
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
    protected $tag;

    /**
    * @ORM\Column(type="string", length=255)
    */
    protected $name;

    /**
    * @ORM\Column(type="text")
    */
    protected $description;

    /**
    * @ORM\ManyToOne(targetEntity="File")
    */
    protected $image;

    /**
    * @ORM\Column(type="string", length=255)
    */
    protected $templateFile;

    /**
    * @ORM\Column(type="string", length=255)
    */
    protected $cssFile;

    /**
    * @ORM\Column(type="string", length=255)
    */
    protected $jsFile;

    /**
    * @ORM\Column(type="text", nullable=true)
    */
    protected $customCss;

    /**
    * @ORM\Column(type="text", nullable=true)
    */
    protected $customJs;

    /**
    * @ORM\Column(type="boolean")
    */
    protected $frontend = 0;

    /**
    * @ORM\Column(type="boolean")
    */
    protected $admin = 0;

    /**
    * @ORM\Column(type="boolean")
    */
    protected $protected = 0;

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
    * Get the value of Tag
    *
    * @return mixed
    */
    public function getTag()
    {
        return $this->tag;
    }

    /**
    * Set the value of Tag
    *
    * @param mixed tag
    *
    * @return self
    */
    public function setTag($tag)
    {
        $this->tag = $tag;

        return $this;
    }

    /**
    * Get the value of Name
    *
    * @return mixed
    */
    public function getName()
    {
        return $this->name;
    }

    /**
    * Set the value of Name
    *
    * @param mixed name
    *
    * @return self
    */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
    * Get the value of Description
    *
    * @return mixed
    */
    public function getDescription()
    {
        return $this->description;
    }

    /**
    * Set the value of Description
    *
    * @param mixed description
    *
    * @return self
    */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
    * Get the value of Image
    *
    * @return mixed
    */
    public function getImage()
    {
        return $this->image;
    }

    /**
    * Set the value of Image
    *
    * @param mixed image
    *
    * @return self
    */
    public function setImage($image)
    {
        $this->image = $image;

        return $this;
    }

    /**
    * Get the value of Template File
    *
    * @return mixed
    */
    public function getTemplateFile()
    {
        return $this->templateFile;
    }

    /**
    * Set the value of Template File
    *
    * @param mixed templateFile
    *
    * @return self
    */
    public function setTemplateFile($templateFile)
    {
        $this->templateFile = $templateFile;

        return $this;
    }

    /**
    * Get the value of Css File
    *
    * @return mixed
    */
    public function getCssFile()
    {
        return $this->cssFile;
    }

    /**
    * Set the value of Css File
    *
    * @param mixed cssFile
    *
    * @return self
    */
    public function setCssFile($cssFile)
    {
        $this->cssFile = $cssFile;

        return $this;
    }

    /**
    * Get the value of Js File
    *
    * @return mixed
    */
    public function getJsFile()
    {
        return $this->jsFile;
    }

    /**
    * Set the value of Js File
    *
    * @param mixed jsFile
    *
    * @return self
    */
    public function setJsFile($jsFile)
    {
        $this->jsFile = $jsFile;

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

    /**
    * Get the value of Frontend
    *
    * @return mixed
    */
    public function getFrontend()
    {
        return $this->frontend;
    }

    /**
    * Set the value of Frontend
    *
    * @param mixed frontend
    *
    * @return self
    */
    public function setFrontend($frontend)
    {
        $this->frontend = $frontend;

        return $this;
    }

    /**
    * Get the value of Admin
    *
    * @return mixed
    */
    public function getAdmin()
    {
        return $this->admin;
    }

    /**
    * Set the value of Admin
    *
    * @param mixed admin
    *
    * @return self
    */
    public function setAdmin($admin)
    {
        $this->admin = $admin;

        return $this;
    }

    /**
    * Get the value of Protected
    *
    * @return mixed
    */
    public function getProtected()
    {
        return $this->protected;
    }

    /**
    * Set the value of Protected
    *
    * @param mixed protected
    *
    * @return self
    */
    public function setProtected($protected)
    {
        $this->protected = $protected;

        return $this;
    }
}
