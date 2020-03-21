<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

use App\Service\RouteService;

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
    * @ORM\Column(type="text", nullable=true)
    */
    protected $description;

    /**
    * @ORM\Column(type="string", length=255, nullable=true)
    */
    protected $companyName;

    /**
    * @ORM\Column(type="string", length=255, nullable=true)
    */
    protected $website;

    /**
    * @ORM\Column(type="string", length=255, nullable=true)
    */
    protected $vatNumber;

    /**
    * @ORM\Column(type="string", length=255, nullable=true)
    */
    protected $registrationNumber;

    /**
    * @ORM\Column(type="string", length=255, nullable=true)
    */
    protected $address1;

    /**
    * @ORM\Column(type="string", length=255, nullable=true)
    */
    protected $address2;

    /**
    * @ORM\Column(type="string", length=255, nullable=true)
    */
    protected $zipcode;

    /**
    * @ORM\Column(type="string", length=255, nullable=true)
    */
    protected $city;

    /**
    * @ORM\Column(type="string", length=255, nullable=true)
    */
    protected $country;

    /**
    * @ORM\Column(type="decimal", precision=10, scale=8, nullable=true)
    */
    protected $latitude;

    /**
    * @ORM\Column(type="decimal", precision=11, scale=8, nullable=true)
    */
    protected $longitude;

    /**
    * @ORM\Column(type="string", length=255, nullable=true)
    */
    protected $billingAddress1;

    /**
    * @ORM\Column(type="string", length=255, nullable=true)
    */
    protected $billingAddress2;

    /**
    * @ORM\Column(type="string", length=255, nullable=true)
    */
    protected $billingZipcode;

    /**
    * @ORM\Column(type="string", length=255, nullable=true)
    */
    protected $billingCity;

    /**
    * @ORM\Column(type="string", length=255, nullable=true)
    */
    protected $billingCountry;

    /**
    * @ORM\Column(type="decimal", precision=10, scale=8, nullable=true)
    */
    protected $billingLatitude;

    /**
    * @ORM\Column(type="decimal", precision=11, scale=8, nullable=true)
    */
    protected $billingLongitude;

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
     * @param mixed description
     *
     * @return self
     */
    public function setDescription($description)
    {
        $this->description = $description;

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
    public function getAddress1()
    {
        return $this->address1;
    }

    /**
     * Set the value of Mail Address
     *
     * @param mixed address1
     *
     * @return self
     */
    public function setAddress1($address1)
    {
        $this->address1 = $address1;

        return $this;
    }

    /**
     * Get the value of Mail Address
     *
     * @return mixed
     */
    public function getAddress2()
    {
        return $this->address2;
    }

    /**
     * Set the value of Mail Address
     *
     * @param mixed address2
     *
     * @return self
     */
    public function setAddress2($address2)
    {
        $this->address2 = $address2;

        return $this;
    }

    /**
     * Get the value of Mail Zipcode
     *
     * @return mixed
     */
    public function getZipcode()
    {
        return $this->zipcode;
    }

    /**
     * Set the value of Mail Zipcode
     *
     * @param mixed zipcode
     *
     * @return self
     */
    public function setZipcode($zipcode)
    {
        $this->zipcode = $zipcode;

        return $this;
    }

    /**
     * Get the value of Mail City
     *
     * @return mixed
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * Set the value of Mail City
     *
     * @param mixed city
     *
     * @return self
     */
    public function setCity($city)
    {
        $this->city = $city;

        return $this;
    }

    /**
     * Get the value of Mail Country
     *
     * @return mixed
     */
    public function getCountry()
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
    public function setCountry($country)
    {
        $this->mailCountry = $country;

        return $this;
    }

    /**
     * Get the value of Mail Latitude
     *
     * @return mixed
     */
    public function getMailLatitude()
    {
        return $this->mailLatitude;
    }

    /**
     * Set the value of Mail Latitude
     *
     * @param mixed $latitude
     *
     * @return self
     */
    public function setMailLatitude($latitude)
    {
        $this->mailLatitude = $latitude;

        return $this;
    }

    /**
     * Get the value of Mail Longitude
     *
     * @return mixed
     */
    public function getMailLongitude()
    {
        return $this->mailLongitude;
    }

    /**
     * Set the value of Mail Longitude
     *
     * @param mixed $longitude
     *
     * @return self
     */
    public function setMailLongitude($longitude)
    {
        $this->mailLongitude = $longitude;

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

    /**
     * Get the value of Billing Latitude
     *
     * @return mixed
     */
    public function getBillingLatitude()
    {
        return $this->billingLatitude;
    }

    /**
     * Set the value of Billing Latitude
     *
     * @param mixed $billingLatitude
     *
     * @return self
     */
    public function setBillingLatitude($billingLatitude)
    {
        $this->billingLatitude = $billingLatitude;

        return $this;
    }

    /**
     * Get the value of Billing Longitude
     *
     * @return mixed
     */
    public function getBillingLongitude()
    {
        return $this->billingLongitude;
    }

    /**
     * Set the value of Billing Longitude
     *
     * @param mixed $billingLongitude
     *
     * @return self
     */
    public function setBillingLongitude($billingLongitude)
    {
        $this->billingLongitude = $billingLongitude;

        return $this;
    }
}
