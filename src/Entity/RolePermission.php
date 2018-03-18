<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\RolePermissionRepository")
 */
class RolePermission
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
    private $roleId;

    /**
     * @ORM\Column(type="integer")
     */
    private $permissionId;

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
     * Get the value of Role Id
     *
     * @return mixed
     */
    public function getRoleId()
    {
        return $this->roleId;
    }

    /**
     * Set the value of Role Id
     *
     * @param mixed roleId
     *
     * @return self
     */
    public function setRoleId($roleId)
    {
        $this->roleId = $roleId;

        return $this;
    }

    /**
     * Get the value of Permission Id
     *
     * @return mixed
     */
    public function getPermissionId()
    {
        return $this->permissionId;
    }

    /**
     * Set the value of Permission Id
     *
     * @param mixed permissionId
     *
     * @return self
     */
    public function setPermissionId($permissionId)
    {
        $this->permissionId = $permissionId;

        return $this;
    }

}
