<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
* @ORM\Entity(repositoryClass="App\Repository\RedirectRepository")
*/
class Redirect
{
    /**
    * @ORM\Id
    * @ORM\GeneratedValue
    * @ORM\Column(type="integer")
    */
    private $id;

    /**
    * @ORM\Column(type="string", length=255, unique=true)
    */
    protected $oldPageRoute;

    /**
    * @ORM\Column(type="string", length=255)
    */
    protected $newPageRoute;

    /**
    * @ORM\Column(type="integer")
    */
    protected $redirectType;

    /**
    * @ORM\Column(type="boolean")
    */
    protected $active;

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
    * Get the value of Old Page Route
    *
    * @return mixed
    */
    public function getOldPageRoute()
    {
        return $this->oldPageRoute;
    }

    /**
    * Set the value of Old Page Route
    *
    * @param mixed oldPageRoute
    *
    * @return self
    */
    public function setOldPageRoute($oldPageRoute)
    {
        $this->oldPageRoute = $oldPageRoute;

        return $this;
    }

    /**
    * Get the value of New Page Route
    *
    * @return mixed
    */
    public function getNewPageRoute()
    {
        return $this->newPageRoute;
    }

    /**
    * Set the value of New Page Route
    *
    * @param mixed newPageRoute
    *
    * @return self
    */
    public function setNewPageRoute($newPageRoute)
    {
        $this->newPageRoute = $newPageRoute;

        return $this;
    }

    /**
    * Get the value of Redirect Type
    *
    * @return mixed
    */
    public function getRedirectType()
    {
        return $this->redirectType;
    }

    /**
    * Set the value of Redirect Type
    *
    * @param mixed redirectType
    *
    * @return self
    */
    public function setRedirectType($redirectType)
    {
        $this->redirectType = $redirectType;

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
    * @param mixed active
    *
    * @return self
    */
    public function setActive($active)
    {
        $this->active = $active;

        return $this;
    }

}
