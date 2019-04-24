<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Core\Annotation\ApiResource;

/**
* @ApiResource
* @ORM\Entity(repositoryClass="App\Repository\PageRepository")
*/
class Page
{
    /**
    * @ORM\Id
    * @ORM\GeneratedValue
    * @ORM\Column(type="integer")
    */
    private $id;

    /**
    * @ORM\Column(type="date")
    */
    protected $publishDate;

    /**
    * @ORM\Column(type="date", nullable=true)
    */
    protected $expireDate;

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
    * Get the value of Publish Date
    *
    * @return mixed
    */
    public function getPublishDate()
    {
        return $this->publishDate;
    }

    /**
    * Set the value of Publish Date
    *
    * @param mixed publishDate
    *
    * @return self
    */
    public function setPublishDate($publishDate)
    {
        $this->publishDate = $publishDate;

        return $this;
    }

    /**
    * Get the value of Expire Date
    *
    * @return mixed
    */
    public function getExpireDate()
    {
        return $this->expireDate;
    }

    /**
    * Set the value of Expire Date
    *
    * @param mixed expireDate
    *
    * @return self
    */
    public function setExpireDate($expireDate)
    {
        $this->expireDate = $expireDate;

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
