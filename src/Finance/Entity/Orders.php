<?php

namespace App\Finance\Entity;

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
}
