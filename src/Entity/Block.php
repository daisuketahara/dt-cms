<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Locale;

/**
* @ORM\Entity(repositoryClass="App\Repository\BlockRepository")
*/
class Block
{
    /**
    * @ORM\Id()
    * @ORM\GeneratedValue()
    * @ORM\Column(type="integer")
    */
    private $id;

    /**
    * @ORM\Column(type="string", length=255)
    */
    private $tag;

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
}
