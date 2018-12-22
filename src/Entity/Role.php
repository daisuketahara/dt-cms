<?php

namespace App\Entity;

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
     * @ORM\ManyToMany(targetEntity="App\Entity\Permission")
     */
    protected $permissions;

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
     * Get the value of Permissions
     *
     * @return mixed
     */
    public function getPermissions()
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
}
