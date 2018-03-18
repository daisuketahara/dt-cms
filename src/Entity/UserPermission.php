<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserPermissionRepository")
 */
class UserPermission
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
