<?php

namespace App\Blog\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
* @ORM\Entity(repositoryClass="App\Blog\Repository\BlogRepository")
*/
class Blog
{
    /**
    * @ORM\Id()
    * @ORM\GeneratedValue()
    * @ORM\Column(type="integer")
    */
    private $id;

    /**
    * @ORM\ManyToOne(targetEntity="App\Entity\Block",cascade={"persist"})
    * @ORM\JoinColumn(onDelete="CASCADE")
    */
    private $block;

    /**
    * @ORM\Column(type="string", length=255)
    */
    private $name;

    /**
    * @ORM\Column(type="string", length=255, nullable=true)
    */
    private $description;

    /**
    * @ORM\Column(type="text", nullable=true)
    */
    private $content;

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
    * Get the value of Block
    *
    * @return mixed
    */
    public function getBlock()
    {
        return $this->block;
    }

    /**
    * Set the value of Block
    *
    * @param mixed block
    *
    * @return self
    */
    public function setBlock($block)
    {
        $this->block = $block;

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
}
