<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
* @ORM\Entity(repositoryClass="App\Repository\MailTemplateRepository")
*/
class MailTemplate
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
    * @ORM\Column(type="text")
    */
    protected $variables;

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
    * Get the value of Variables
    *
    * @return mixed
    */
    public function getVariables()
    {
        return $this->variables;
    }

    /**
    * Set the value of Variables
    *
    * @param mixed variables
    *
    * @return self
    */
    public function setVariables($variables)
    {
        $this->variables = $variables;

        return $this;
    }
}
