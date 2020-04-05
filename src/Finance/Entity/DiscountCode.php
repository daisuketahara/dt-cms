<?php

namespace App\Finance\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Finance\Repository\DiscountCodeRepository")
 */
class DiscountCode
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
    protected $description;

    /**
     * @ORM\Column(type="string", length=255)
     */
    protected $code;

    /**
    * @ORM\Column(type="date", nullable=true)
    */
    protected $startDate;

    /**
    * @ORM\Column(type="date", nullable=true)
    */
    protected $endDate;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $price;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $percentage;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $maxUse;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $used;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $active = false;

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
     * Get the value of Description
     *
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set the value of Description
     *
     * @param mixed $description
     *
     * @return self
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get the value of Code
     *
     * @return mixed
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set the value of Code
     *
     * @param mixed $code
     *
     * @return self
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Get the value of Start Date
     *
     * @return mixed
     */
    public function getStartDate()
    {
        return $this->startDate;
    }

    /**
     * Set the value of Start Date
     *
     * @param mixed $startDate
     *
     * @return self
     */
    public function setStartDate($startDate)
    {
        $this->startDate = $startDate;

        return $this;
    }

    /**
     * Get the value of End Date
     *
     * @return mixed
     */
    public function getEndDate()
    {
        return $this->endDate;
    }

    /**
     * Set the value of End Date
     *
     * @param mixed $endDate
     *
     * @return self
     */
    public function setEndDate($endDate)
    {
        $this->endDate = $endDate;

        return $this;
    }

    /**
     * Get the value of Price
     *
     * @return mixed
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * Set the value of Price
     *
     * @param mixed $price
     *
     * @return self
     */
    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }

    /**
     * Get the value of Percentage
     *
     * @return mixed
     */
    public function getPercentage()
    {
        return $this->percentage;
    }

    /**
     * Set the value of Percentage
     *
     * @param mixed $percentage
     *
     * @return self
     */
    public function setPercentage($percentage)
    {
        $this->percentage = $percentage;

        return $this;
    }

    /**
     * Get the value of Max Use
     *
     * @return mixed
     */
    public function getMaxUse()
    {
        return $this->maxUse;
    }

    /**
     * Set the value of Max Use
     *
     * @param mixed $maxUse
     *
     * @return self
     */
    public function setMaxUse($maxUse)
    {
        $this->maxUse = $maxUse;

        return $this;
    }

    /**
     * Get the value of Used
     *
     * @return mixed
     */
    public function getUsed()
    {
        return $this->used;
    }

    /**
     * Set the value of Used
     *
     * @param mixed $used
     *
     * @return self
     */
    public function setUsed($used)
    {
        $this->used = $used;

        return $this;
    }

    /**
     * Get the value of Active
     *
     * @return mixed
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * Set the value of Active
     *
     * @param mixed $active
     *
     * @return self
     */
    public function setActive($active)
    {
        $this->active = $active;

        return $this;
    }
}
