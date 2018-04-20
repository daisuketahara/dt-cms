<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRoleRepository")
 */
class UserRole
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     */
    protected $userId;

    /**
     * @ORM\Column(type="integer")
     */
    protected $RoleId;

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
     * @param mixed UserId
     *
     * @return self
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * Get the value of Role Id
     *
     * @return mixed
     */
    public function getRoleId()
    {
        return $this->RoleId;
    }

    /**
     * Set the value of Role Id
     *
     * @param mixed RoleId
     *
     * @return self
     */
    public function setRoleId($RoleId)
    {
        $this->RoleId = $RoleId;

        return $this;
    }

}
