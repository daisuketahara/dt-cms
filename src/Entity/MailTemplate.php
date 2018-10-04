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
     * @ORM\Column(type="integer")
     */
    protected $defaultId = 0;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Locale")
     */
    protected $locale;

    /**
     * @ORM\Column(type="string", length=255)
     */
    protected $tag;

    /**
     * @ORM\Column(type="string", length=255)
     */
    protected $name;

    /**
     * @ORM\Column(type="string", length=255)
     */
    protected $subject;

    /**
     * @ORM\Column(type="text")
     */
    protected $body;

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
     * Get the value of Default Id
     *
     * @return mixed
     */
    public function getDefaultId()
    {
        return $this->defaultId;
    }

    /**
     * Set the value of Default Id
     *
     * @param mixed defaultId
     *
     * @return self
     */
    public function setDefaultId($defaultId)
    {
        $this->defaultId = $defaultId;

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
     * Get the value of Subject
     *
     * @return mixed
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * Set the value of Subject
     *
     * @param mixed subject
     *
     * @return self
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * Get the value of Body
     *
     * @return mixed
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * Set the value of Body
     *
     * @param mixed body
     *
     * @return self
     */
    public function setBody($body)
    {
        $this->body = $body;

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
