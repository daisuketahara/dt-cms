<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
* @ORM\Entity(repositoryClass="App\Repository\AppTranslationRepository")
*/
class AppTranslation
{
    /**
    * @ORM\Id
    * @ORM\GeneratedValue
    * @ORM\Column(type="integer")
    */
    private $id;

    /**
    * @ORM\ManyToOne(targetEntity="App\Entity\Locale")
    */
    protected $locale;

    /**
    * @ORM\Column(type="integer", nullable=true)
    */
    protected $parentId;

    /**
    * @ORM\Column(type="string", length=255)
    */
    protected $tag;

    /**
    * @ORM\Column(type="text", nullable=true)
    */
    protected $translation;

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
    * Get the value of Parent Id
    *
    * @return mixed
    */
    public function getParentId()
    {
        return $this->parentId;
    }

    /**
    * Set the value of Parent Id
    *
    * @param mixed parentId
    *
    * @return self
    */
    public function setParentId($parentId)
    {
        $this->parentId = $parentId;

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
    * Get the value of Translation
    *
    * @return mixed
    */
    public function getTranslation()
    {
        return $this->translation;
    }

    /**
    * Set the value of Translation
    *
    * @param mixed translation
    *
    * @return self
    */
    public function setTranslation($translation)
    {
        $this->translation = $translation;

        return $this;
    }
}
