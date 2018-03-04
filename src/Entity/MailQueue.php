<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\MailQueueRepository")
 */
class MailQueue
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $fromName;

    /**
     * @ORM\Column(type="string", length=255)
     */
    protected $fromEmail;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $replyEmail;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $toName;

    /**
     * @ORM\Column(type="string", length=255)
     */
    protected $toEmail;

    /**
     * @ORM\Column(type="string", length=255)
     */
    protected $subject;

    /**
     * @ORM\Column(type="text")
     */
    protected $body;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $creationDate;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $sendDate;

    /**
     * @ORM\Column(type="integer")
     */
    private $status = 0;

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
     * Get the value of From Name
     *
     * @return mixed
     */
    public function getFromName()
    {
        return $this->fromName;
    }

    /**
     * Set the value of From Name
     *
     * @param mixed fromName
     *
     * @return self
     */
    public function setFromName($fromName)
    {
        $this->fromName = $fromName;

        return $this;
    }

    /**
     * Get the value of From Email
     *
     * @return mixed
     */
    public function getFromEmail()
    {
        return $this->fromEmail;
    }

    /**
     * Set the value of From Email
     *
     * @param mixed fromEmail
     *
     * @return self
     */
    public function setFromEmail($fromEmail)
    {
        $this->fromEmail = $fromEmail;

        return $this;
    }

    /**
     * Get the value of Reply Email
     *
     * @return mixed
     */
    public function getReplyEmail()
    {
        return $this->replyEmail;
    }

    /**
     * Set the value of Reply Email
     *
     * @param mixed replyEmail
     *
     * @return self
     */
    public function setReplyEmail($replyEmail)
    {
        $this->replyEmail = $replyEmail;

        return $this;
    }

    /**
     * Get the value of To Name
     *
     * @return mixed
     */
    public function getToName()
    {
        return $this->toName;
    }

    /**
     * Set the value of To Name
     *
     * @param mixed toName
     *
     * @return self
     */
    public function setToName($toName)
    {
        $this->toName = $toName;

        return $this;
    }

    /**
     * Get the value of To Email
     *
     * @return mixed
     */
    public function getToEmail()
    {
        return $this->toEmail;
    }

    /**
     * Set the value of To Email
     *
     * @param mixed toEmail
     *
     * @return self
     */
    public function setToEmail($toEmail)
    {
        $this->toEmail = $toEmail;

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
     * Get the value of Creation Date
     *
     * @return mixed
     */
    public function getCreationDate()
    {
        return $this->creationDate;
    }

    /**
     * Set the value of Creation Date
     *
     * @param mixed creationDate
     *
     * @return self
     */
    public function setCreationDate($creationDate)
    {
        $this->creationDate = $creationDate;

        return $this;
    }

    /**
     * Get the value of Send Date
     *
     * @return mixed
     */
    public function getSendDate()
    {
        if (!empty($this->sendDate)) return $this->sendDate;
        return '';
    }

    /**
     * Set the value of Send Date
     *
     * @param mixed sendDate
     *
     * @return self
     */
    public function setSendDate($sendDate)
    {
        $this->sendDate = $sendDate;

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

}
