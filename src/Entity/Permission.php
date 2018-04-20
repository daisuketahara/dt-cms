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
     * @ORM\Column(type="integer")
     */
    private $groupId;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $pageId;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
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
     * Get the value of Group Id
     *
     * @return mixed
     */
    public function getGroupId()
    {
        return $this->groupId;
    }

    /**
     * Set the value of Group Id
     *
     * @param mixed groupId
     *
     * @return self
     */
    public function setGroupId($groupId)
    {
        $this->groupId = $groupId;

        return $this;
    }

    /**
     * Get the value of Page Id
     *
     * @return mixed
     */
    public function getPageId()
    {
        return $this->pageId;
    }

    /**
     * Set the value of Page Id
     *
     * @param mixed pageId
     *
     * @return self
     */
    public function setPageId($pageId)
    {
        $this->pageId = $pageId;

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
}
