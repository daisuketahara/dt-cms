<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
* @ORM\Entity(repositoryClass="App\Repository\PermissionRepository")
*/
class Permission
{
    /**
    * @ORM\Id
    * @ORM\GeneratedValue
    * @ORM\Column(type="integer")
    */
    private $id;

    /**
    * @ORM\ManyToOne(targetEntity="App\Entity\PermissionGroup")
    */
    private $permissionGroup;

    /**
    * @ORM\OneToOne(targetEntity="App\Entity\Page", cascade={"persist"})
    */
    private $page;

    /**
    * @ORM\Column(type="string", length=255)
    */
    protected $routeName;

    /**
    * @ORM\Column(type="string", length=255)
    */
    protected $description;

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
    * Get the value of Route Name
    *
    * @return mixed
    */
    public function getRouteName()
    {
        return $this->routeName;
    }

    /**
    * Set the value of Route Name
    *
    * @param mixed routeName
    *
    * @return self
    */
    public function setRouteName($routeName)
    {
        $this->routeName = $routeName;

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
    * Get the value of Permission Group
    *
    * @return mixed
    */
    public function getPermissionGroup()
    {
        return $this->permissionGroup;
    }

    /**
    * Set the value of Permission Group
    *
    * @param mixed permissionGroup
    *
    * @return self
    */
    public function setPermissionGroup($permissionGroup)
    {
        $this->permissionGroup = $permissionGroup;

        return $this;
    }

    /**
    * Get the value of Page
    *
    * @return mixed
    */
    public function getPage()
    {
        return $this->page;
    }

    /**
    * Set the value of Page
    *
    * @param mixed page
    *
    * @return self
    */
    public function setPage($page)
    {
        $this->page = $page;

        return $this;
    }

}
