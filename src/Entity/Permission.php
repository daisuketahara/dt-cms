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
    * @ORM\Column(type="string", length=255, nullable=true)
    */
    protected $route;

    /**
    * @ORM\Column(type="string", length=255, nullable=true)
    */
    protected $component;

    /**
    * @ORM\Column(type="text", nullable=true)
    */
    protected $props;

    /**
    * @ORM\Column(type="string", length=255, nullable=true)
    */
    protected $description;

    /**
    * @ORM\Column(type="string", length=255, nullable=true)
    */
    protected $tag;

    /**
    * @ORM\Column(type="boolean", nullable=true)
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
     * Get the value of Route
     *
     * @return mixed
     */
    public function getRoute()
    {
        return $this->route;
    }

    /**
     * Set the value of Route
     *
     * @param mixed route
     *
     * @return self
     */
    public function setRoute($route)
    {
        $this->route = $route;

        return $this;
    }

    /**
     * Get the value of Component
     *
     * @return mixed
     */
    public function getComponent()
    {
        return $this->component;
    }

    /**
     * Set the value of Component
     *
     * @param mixed component
     *
     * @return self
     */
    public function setComponent($component)
    {
        $this->component = $component;

        return $this;
    }

    /**
     * Get the value of Props
     *
     * @return mixed
     */
    public function getProps()
    {
        return $this->props;
    }

    /**
     * Set the value of Props
     *
     * @param mixed props
     *
     * @return self
     */
    public function setProps($props)
    {
        $this->props = $props;

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
     * Get the value of Tag
     *
     * @return mixed
     */
    public function getTag()
    {
        return $this->tag;
    }

    /**
     * Set the value of Tag
     *
     * @param mixed $tag
     *
     * @return self
     */
    public function setTag($tag)
    {
        $this->tag = $tag;

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
     * @param mixed $active
     *
     * @return self
     */
    public function setActive($active)
    {
        $this->active = $active;

        return $this;
    }

}
