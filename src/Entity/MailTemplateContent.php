<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
* @ORM\Entity(repositoryClass="App\Repository\MailTemplateContentRepository")
*/
class MailTemplateContent
{
    /**
    * @ORM\Id
    * @ORM\GeneratedValue
    * @ORM\Column(type="integer")
    */
    private $id;

    /**
    * @ORM\ManyToOne(targetEntity="App\Entity\MailTemplate",cascade={"persist"})
    * @ORM\JoinColumn(onDelete="CASCADE")
    */
    private $mailTemplate;

    /**
    * @ORM\ManyToOne(targetEntity="App\Entity\Locale")
    */
    protected $locale;

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
    * Get the value of Mail Template
    *
    * @return mixed
    */
    public function getMailTemplate()
    {
        return $this->mailTemplate;
    }

    /**
    * Set the value of Mail Template
    *
    * @param mixed mailTemplate
    *
    * @return self
    */
    public function setMailTemplate($mailTemplate)
    {
        $this->mailTemplate = $mailTemplate;

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
}
