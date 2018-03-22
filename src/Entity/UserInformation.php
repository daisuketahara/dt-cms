<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserInformationRepository")
 */
class UserInformation
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
    private $userId;

    /**
     * @ORM\Column(type="string", length=255)
     */
    protected $companyName;

    /**
     * @ORM\Column(type="string", length=255)
     */
    protected $website;

    /**
     * @ORM\Column(type="string", length=255)
     */
    protected $vatNumber;

    /**
     * @ORM\Column(type="string", length=255)
     */
    protected $registrationNumber;

    /**
     * @ORM\Column(type="string", length=255)
     */
    protected $mailAddress1;

    /**
     * @ORM\Column(type="string", length=255)
     */
    protected $mailAddress2;

    /**
     * @ORM\Column(type="string", length=255)
     */
    protected $mailZipcode;

    /**
     * @ORM\Column(type="string", length=255)
     */
    protected $mailCity;

    /**
     * @ORM\Column(type="string", length=255)
     */
    protected $mailCountry;

    /**
     * @ORM\Column(type="string", length=255)
     */
    protected $billingAddress1;

    /**
     * @ORM\Column(type="string", length=255)
     */
    protected $billingAddress2;

    /**
     * @ORM\Column(type="string", length=255)
     */
    protected $billingZipcode;

    /**
     * @ORM\Column(type="string", length=255)
     */
    protected $billingCity;

    /**
     * @ORM\Column(type="string", length=255)
     */
    protected $billingCountry;


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
     * Get the value of User Id
     *
     * @return mixed
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * Set the value of User Id
     *
     * @param mixed userId
     *
     * @return self
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * Get the value of Company Name
     *
     * @return mixed
     */
    public function getCompanyName()
    {
        return $this->companyName;
    }

    /**
     * Set the value of Company Name
     *
     * @param mixed companyName
     *
     * @return self
     */
    public function setCompanyName($companyName)
    {
        $this->companyName = $companyName;

        return $this;
    }

    /**
     * Get the value of Website
     *
     * @return mixed
     */
    public function getWebsite()
    {
        return $this->website;
    }

    /**
     * Set the value of Website
     *
     * @param mixed website
     *
     * @return self
     */
    public function setWebsite($website)
    {
        $this->website = $website;

        return $this;
    }

    /**
     * Get the value of Vat Number
     *
     * @return mixed
     */
    public function getVatNumber()
    {
        return $this->vatNumber;
    }

    /**
     * Set the value of Vat Number
     *
     * @param mixed vatNumber
     *
     * @return self
     */
    public function setVatNumber($vatNumber)
    {
        $this->vatNumber = $vatNumber;

        return $this;
    }

    /**
     * Get the value of Registration Number
     *
     * @return mixed
     */
    public function getRegistrationNumber()
    {
        return $this->registrationNumber;
    }

    /**
     * Set the value of Registration Number
     *
     * @param mixed registrationNumber
     *
     * @return self
     */
    public function setRegistrationNumber($registrationNumber)
    {
        $this->registrationNumber = $registrationNumber;

        return $this;
    }

    /**
     * Get the value of Mail Address
     *
     * @return mixed
     */
    public function getMailAddress1()
    {
        return $this->mailAddress1;
    }

    /**
     * Set the value of Mail Address
     *
     * @param mixed mailAddress1
     *
     * @return self
     */
    public function setMailAddress1($mailAddress1)
    {
        $this->mailAddress1 = $mailAddress1;

        return $this;
    }

    /**
     * Get the value of Mail Address
     *
     * @return mixed
     */
    public function getMailAddress2()
    {
        return $this->mailAddress2;
    }

    /**
     * Set the value of Mail Address
     *
     * @param mixed mailAddress2
     *
     * @return self
     */
    public function setMailAddress2($mailAddress2)
    {
        $this->mailAddress2 = $mailAddress2;

        return $this;
    }

    /**
     * Get the value of Mail Zipcode
     *
     * @return mixed
     */
    public function getMailZipcode()
    {
        return $this->mailZipcode;
    }

    /**
     * Set the value of Mail Zipcode
     *
     * @param mixed mailZipcode
     *
     * @return self
     */
    public function setMailZipcode($mailZipcode)
    {
        $this->mailZipcode = $mailZipcode;

        return $this;
    }

    /**
     * Get the value of Mail City
     *
     * @return mixed
     */
    public function getMailCity()
    {
        return $this->mailCity;
    }

    /**
     * Set the value of Mail City
     *
     * @param mixed mailCity
     *
     * @return self
     */
    public function setMailCity($mailCity)
    {
        $this->mailCity = $mailCity;

        return $this;
    }

    /**
     * Get the value of Mail Country
     *
     * @return mixed
     */
    public function getMailCountry()
    {
        return $this->mailCountry;
    }

    /**
     * Set the value of Mail Country
     *
     * @param mixed mailCountry
     *
     * @return self
     */
    public function setMailCountry($mailCountry)
    {
        $this->mailCountry = $mailCountry;

        return $this;
    }

    /**
     * Get the value of Billing Address
     *
     * @return mixed
     */
    public function getBillingAddress1()
    {
        return $this->billingAddress1;
    }

    /**
     * Set the value of Billing Address
     *
     * @param mixed billingAddress1
     *
     * @return self
     */
    public function setBillingAddress1($billingAddress1)
    {
        $this->billingAddress1 = $billingAddress1;

        return $this;
    }

    /**
     * Get the value of Billing Address
     *
     * @return mixed
     */
    public function getBillingAddress2()
    {
        return $this->billingAddress2;
    }

    /**
     * Set the value of Billing Address
     *
     * @param mixed billingAddress2
     *
     * @return self
     */
    public function setBillingAddress2($billingAddress2)
    {
        $this->billingAddress2 = $billingAddress2;

        return $this;
    }

    /**
     * Get the value of Billing Zipcode
     *
     * @return mixed
     */
    public function getBillingZipcode()
    {
        return $this->billingZipcode;
    }

    /**
     * Set the value of Billing Zipcode
     *
     * @param mixed billingZipcode
     *
     * @return self
     */
    public function setBillingZipcode($billingZipcode)
    {
        $this->billingZipcode = $billingZipcode;

        return $this;
    }

    /**
     * Get the value of Billing City
     *
     * @return mixed
     */
    public function getBillingCity()
    {
        return $this->billingCity;
    }

    /**
     * Set the value of Billing City
     *
     * @param mixed billingCity
     *
     * @return self
     */
    public function setBillingCity($billingCity)
    {
        $this->billingCity = $billingCity;

        return $this;
    }

    /**
     * Get the value of Billing Country
     *
     * @return mixed
     */
    public function getBillingCountry()
    {
        return $this->billingCountry;
    }

    /**
     * Set the value of Billing Country
     *
     * @param mixed billingCountry
     *
     * @return self
     */
    public function setBillingCountry($billingCountry)
    {
        $this->billingCountry = $billingCountry;

        return $this;
    }
}