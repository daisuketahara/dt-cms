<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
* @ORM\Entity(repositoryClass="App\Repository\RoleRepository")
*/
class Role
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
    protected $name;

    /**
    * @ORM\Column(type="string", length=255)
    */
    protected $description;

    /**
    * @ORM\ManyToOne(targetEntity="App\Entity\Menu")
    */
    protected $adminMenu;

    /**
    * @ORM\Column(type="boolean")
    */
    protected $adminAccess = false;

    /**
    * @ORM\ManyToMany(targetEntity="App\Entity\Permission")
    */
    protected $permissions;

    /**
    * @ORM\Column(type="boolean")
    */
    protected $active;


    public function __construct()
    {
        $this->permissions = new ArrayCollection();
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
    * Get the value of Name
    *
    * @return mixed
    */
    public function getName()
    {
        return $this->name;
    }

    /**
    * Set the value of Name
    *
    * @param mixed name
    *
    * @return self
    */
    public function setName($name)
    {
        $this->name = $name;

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
    * @return Collection|Permission[]
    */
    public function getPermissions(): Collection
    {
        return $this->permissions;
    }

    /**
    * Set the value of Permissions
    *
    * @param mixed permissions
    *
    * @return self
    */
    public function setPermissions($permissions)
    {
        $this->permissions = $permissions;

        return $this;
    }

    /**
    * @param Permission $permission
    */
    public function addPermission(Permission $permission)
    {
        if ($this->permissions->contains($permission)) {
            return;
        }
        $this->permissions->add($permission);
    }

    /**
    * @param Permission $permission
    */
    public function removePermission(Permission $permission)
    {
        if (!$this->permissions->contains($permission)) {
            return;
        }
        $this->permissions->removeElement($permission);
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

    /**
     * Get the value of Admin Menu
     *
     * @return mixed
     */
    public function getAdminMenu()
    {
        return $this->adminMenu;
    }

    /**
     * Set the value of Admin Menu
     *
     * @param mixed $adminMenu
     *
     * @return self
     */
    public function setAdminMenu($adminMenu)
    {
        $this->adminMenu = $adminMenu;

        return $this;
    }

    /**
     * Get the value of Admin Access
     *
     * @return mixed
     */
    public function getAdminAccess()
    {
        return $this->adminAccess;
    }

    /**
     * Set the value of Admin Access
     *
     * @param mixed $adminAccess
     *
     * @return self
     */
    public function setAdminAccess($adminAccess)
    {
        $this->adminAccess = $adminAccess;

        return $this;
    }

}
