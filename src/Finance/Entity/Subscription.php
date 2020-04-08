<?php

namespace App\Finance\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Finance\Repository\SubscriptionRepository")
 */
class Subscription
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
    protected $title;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $description;

    /**
     * @ORM\Column(type="integer", length=255)
     */
    protected $price;

    /**
     * @ORM\Column(type="integer", length=10, nullable=true)
     */
    protected $amountTerms;

    /**
     * @ORM\Column(type="string", length=10)
     */
    protected $term;

    /**
     * @ORM\ManyToOne(targetEntity="App\Finance\Entity\Vat")
     */
    private $vat;

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
     * Get the value of Title
     *
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set the value of Title
     *
     * @param mixed $title
     *
     * @return self
     */
    public function setTitle($title)
    {
        $this->title = $title;

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
     * Get the value of Price
     *
     * @return mixed
     */
    public function getPrice()
    {
        return $this->price / 100;
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
        $this->price = $price * 100;

        return $this;
    }

    /**
     * Get the value of Amount Terms
     *
     * @return mixed
     */
    public function getAmountTerms()
    {
        return $this->amountTerms;
    }

    /**
     * Set the value of Amount Terms
     *
     * @param mixed $amountTerms
     *
     * @return self
     */
    public function setAmountTerms($amountTerms)
    {
        $this->amountTerms = $amountTerms;

        return $this;
    }

    /**
     * Get the value of Term
     *
     * @return mixed
     */
    public function getTerm()
    {
        return $this->term;
    }

    /**
     * Set the value of Term
     *
     * @param mixed $term
     *
     * @return self
     */
    public function setTerm($term)
    {
        $this->term = $term;

        return $this;
    }

    /**
     * Get the value of Vat
     *
     * @return mixed
     */
    public function getVat()
    {
        return $this->vat;
    }

    /**
     * Set the value of Vat
     *
     * @param mixed $vat
     *
     * @return self
     */
    public function setVat($vat)
    {
        $this->vat = $vat;

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
