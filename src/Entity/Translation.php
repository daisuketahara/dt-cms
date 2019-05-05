<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
* @ORM\Entity(repositoryClass="App\Repository\TranslationRepository")
*/
class Translation
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
    private $tag;

    /**
    * @ORM\Column(type="text")
    */
    protected $original;

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
     * Get the value of Original
     *
     * @return mixed
     */
    public function getOriginal()
    {
        return $this->original;
    }

    /**
     * Set the value of Original
     *
     * @param mixed original
     *
     * @return self
     */
    public function setOriginal($original)
    {
        $this->original = $original;

        return $this;
    }

}
