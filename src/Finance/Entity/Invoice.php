<?php

namespace App\Finance\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Finance\Repository\InvoiceRepository")
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
     * @ORM\Column(type="string", length=255)
     */
    protected $invoiceNumber;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     */
    private $user;

    /**
     * @ORM\OneToOne(targetEntity="App\Finance\Entity\Orders")
     */
    private $order;

    /**
     * @ORM\Column(type="date")
     */
    protected $invoiceDate;

    /**
     * @ORM\Column(type="date")
     */
    protected $reminderDate1;

    /**
     * @ORM\Column(type="date")
     */
    protected $reminderDate2;

    /**
     * @ORM\Column(type="date")
     */
    protected $reminderDate3;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $cancel = false;

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
     * @param mixed $id
     *
     * @return self
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get the value of Invoice Number
     *
     * @return mixed
     */
    public function getInvoiceNumber()
    {
        return $this->invoiceNumber;
    }

    /**
     * Set the value of Invoice Number
     *
     * @param mixed $invoiceNumber
     *
     * @return self
     */
    public function setInvoiceNumber($invoiceNumber)
    {
        $this->invoiceNumber = $invoiceNumber;

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
     * @param mixed $user
     *
     * @return self
     */
    public function setUser($user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get the value of Order
     *
     * @return mixed
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * Set the value of Order
     *
     * @param mixed $order
     *
     * @return self
     */
    public function setOrder($order)
    {
        $this->order = $order;

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
     * @param mixed $invoiceDate
     *
     * @return self
     */
    public function setInvoiceDate($invoiceDate)
    {
        $this->invoiceDate = $invoiceDate;

        return $this;
    }

    /**
     * Get the value of Reminder Date
     *
     * @return mixed
     */
    public function getReminderDate1()
    {
        return $this->reminderDate1;
    }

    /**
     * Set the value of Reminder Date
     *
     * @param mixed $reminderDate1
     *
     * @return self
     */
    public function setReminderDate1($reminderDate1)
    {
        $this->reminderDate1 = $reminderDate1;

        return $this;
    }

    /**
     * Get the value of Reminder Date
     *
     * @return mixed
     */
    public function getReminderDate2()
    {
        return $this->reminderDate2;
    }

    /**
     * Set the value of Reminder Date
     *
     * @param mixed $reminderDate2
     *
     * @return self
     */
    public function setReminderDate2($reminderDate2)
    {
        $this->reminderDate2 = $reminderDate2;

        return $this;
    }

    /**
     * Get the value of Reminder Date
     *
     * @return mixed
     */
    public function getReminderDate3()
    {
        return $this->reminderDate3;
    }

    /**
     * Set the value of Reminder Date
     *
     * @param mixed $reminderDate3
     *
     * @return self
     */
    public function setReminderDate3($reminderDate3)
    {
        $this->reminderDate3 = $reminderDate3;

        return $this;
    }

    /**
     * Get the value of Cancel
     *
     * @return mixed
     */
    public function getCancel()
    {
        return $this->cancel;
    }

    /**
     * Set the value of Cancel
     *
     * @param mixed $cancel
     *
     * @return self
     */
    public function setCancel($cancel)
    {
        $this->cancel = $cancel;

        return $this;
    }
}
