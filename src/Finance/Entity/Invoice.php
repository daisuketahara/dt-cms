<?php

namespace App\Finance\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\InvoiceRepository")
 */
class Invoice
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     */
    private $user;

    /**
     * @ORM\Column(type="date")
     */
    protected $invoiceDate;

    /**
     * @ORM\ManyToMany(targetEntity="App\Finance\Entity\InvoiceLines")
     */
    protected $invoiceLines;

    /**
     * @ORM\Column(type="date")
     */
    protected $reminderDate;

    /**
     * @ORM\Column(type="date")
     */
    protected $secondReminderDate;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $status = 0;

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
     * Get the value of User
     *
     * @return mixed
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set the value of User
     *
     * @param mixed user
     *
     * @return self
     */
    public function setUser($user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get the value of Invoice Date
     *
     * @return mixed
     */
    public function getInvoiceDate()
    {
        return $this->invoiceDate;
    }

    /**
     * Set the value of Invoice Date
     *
     * @param mixed invoiceDate
     *
     * @return self
     */
    public function setInvoiceDate($invoiceDate)
    {
        $this->invoiceDate = $invoiceDate;

        return $this;
    }

    /**
     * Get the value of Order Lines
     *
     * @return mixed
     */
    public function getOrderLines()
    {
        return $this->orderLines;
    }

    /**
     * Set the value of Order Lines
     *
     * @param mixed orderLines
     *
     * @return self
     */
    public function setOrderLines($orderLines)
    {
        $this->orderLines = $orderLines;

        return $this;
    }

    /**
     * Get the value of Reminder Date
     *
     * @return mixed
     */
    public function getReminderDate()
    {
        return $this->reminderDate;
    }

    /**
     * Set the value of Reminder Date
     *
     * @param mixed reminderDate
     *
     * @return self
     */
    public function setReminderDate($reminderDate)
    {
        $this->reminderDate = $reminderDate;

        return $this;
    }

    /**
     * Get the value of Second Reminder Date
     *
     * @return mixed
     */
    public function getSecondReminderDate()
    {
        return $this->secondReminderDate;
    }

    /**
     * Set the value of Second Reminder Date
     *
     * @param mixed secondReminderDate
     *
     * @return self
     */
    public function setSecondReminderDate($secondReminderDate)
    {
        $this->secondReminderDate = $secondReminderDate;

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
