<?php

namespace App\Finance\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Finance\Repository\OrderRepository")
 */
class Orders
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
     * @ORM\ManyToOne(targetEntity="App\Finance\Entity\UserSubscription")
     */
    private $userSubscription;

    /**
    * @ORM\Column(type="date")
    */
    protected $orderDate;

    /**
    * @ORM\ManyToMany(targetEntity="App\Finance\Entity\OrderLine")
    */
    protected $orderLines;

    /**
     * @ORM\Column(type="integer", length=255)
     */
    protected $total_excl;

    /**
     * @ORM\Column(type="integer", length=255)
     */
    protected $total_incl;

    /**
     * @ORM\Column(type="integer", length=255)
     */
    protected $total_vat;

    /**
     * @ORM\ManyToOne(targetEntity="App\Finance\Entity\DiscountCode")
     */
    private $discount;

    /**
     * @ORM\Column(type="string", length=50)
     */
    protected $state = 'PENDING';

    public function __construct()
    {
        $this->orderLines = new ArrayCollection();
    }

    /**
    * @return Collection|OrderLine[]
    */
    public function getOrderLines(): Collection
    {
        return $this->orderLines;
    }

    /**
    * Set the value of Permissions
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
    * @param OrderLine $orderLine
    */
    public function addOrderLine(OrderLine $orderLine)
    {
        if ($this->orderLines->contains($orderLine)) {
            return;
        }
        $this->orderLines->add($orderLine);
    }

    /**
    * @param OrderLine $orderLine
    */
    public function removeOrderLine(OrderLine $orderLine)
    {
        if (!$this->orderLines->contains($orderLine)) {
            return;
        }
        $this->orderLines->removeElement($orderLine);
    }

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
     * Get the value of User Subscription
     *
     * @return mixed
     */
    public function getUserSubscription()
    {
        return $this->userSubscription;
    }

    /**
     * Set the value of User Subscription
     *
     * @param mixed $userSubscription
     *
     * @return self
     */
    public function setUserSubscription($userSubscription)
    {
        $this->userSubscription = $userSubscription;

        return $this;
    }

    /**
     * Get the value of Order Date
     *
     * @return mixed
     */
    public function getOrderDate()
    {
        return $this->orderDate;
    }

    /**
     * Set the value of Order Date
     *
     * @param mixed $orderDate
     *
     * @return self
     */
    public function setOrderDate($orderDate)
    {
        $this->orderDate = $orderDate;

        return $this;
    }

    /**
     * Get the value of Total Excl
     *
     * @return mixed
     */
    public function getTotalExcl()
    {
        return $this->total_excl / 100;
    }

    /**
     * Set the value of Total Excl
     *
     * @param mixed $total_excl
     *
     * @return self
     */
    public function setTotalExcl($total_excl)
    {
        $this->total_excl = $total_excl * 100;

        return $this;
    }

    /**
     * Get the value of Total Incl
     *
     * @return mixed
     */
    public function getTotalIncl()
    {
        return $this->total_incl / 100;
    }

    /**
     * Set the value of Total Incl
     *
     * @param mixed $total_incl
     *
     * @return self
     */
    public function setTotalIncl($total_incl)
    {
        $this->total_incl = $total_incl * 100;

        return $this;
    }

    /**
     * Get the value of Total Vat
     *
     * @return mixed
     */
    public function getTotalVat()
    {
        return $this->total_vat / 100;
    }

    /**
     * Set the value of Total Vat
     *
     * @param mixed $total_vat
     *
     * @return self
     */
    public function setTotalVat($total_vat)
    {
        $this->total_vat = $total_vat * 100;

        return $this;
    }


    /**
     * Get the value of Discount
     *
     * @return mixed
     */
    public function getDiscount()
    {
        return $this->discount;
    }

    /**
     * Set the value of Discount
     *
     * @param mixed $discount
     *
     * @return self
     */
    public function setDiscount($discount)
    {
        $this->discount = $discount;

        return $this;
    }


    /**
     * Get the value of State
     *
     * @return mixed
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * Set the value of State
     *
     * @param mixed $state
     *
     * @return self
     */
    public function setState($state)
    {
        $this->state = $state;

        return $this;
    }

}
