<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
* @ORM\Entity(repositoryClass="App\Repository\MenuItemsRepository")
*/
class MenuItems
{
    /**
    * @ORM\Id
    * @ORM\GeneratedValue
    * @ORM\Column(type="integer")
    */
    private $id;

    /**
    * @ORM\ManyToOne(targetEntity="App\Entity\Menu")
    */
    protected $menu;

    /**
    * @ORM\Column(type="integer")
    */
    protected $parentId;

    /**
    * @ORM\Column(type="string", length=255)
    */
    protected $icon;

    /**
    * @ORM\Column(type="string", length=255)
    */
    protected $label;

    /**
    * @ORM\ManyToOne(targetEntity="App\Entity\Page")
    */
    protected $page;

    /**
    * @ORM\ManyToOne(targetEntity="App\Entity\Permission")
    */
    protected $permission;

    /**
    * @ORM\Column(type="string", length=255, nullable=true)
    */
    protected $route;

    /**
    * @ORM\Column(type="string", length=20, nullable=true)
    */
    protected $target;

    /**
    * @ORM\Column(type="boolean")
    */
    protected $protected = 0;

    /**
    * @ORM\Column(type="integer")
    */
    protected $order;

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
     * Get the value of Menu
     *
     * @return mixed
     */
    public function getMenu()
    {
        return $this->menu;
    }

    /**
     * Set the value of Menu
     *
     * @param mixed menu
     *
     * @return self
     */
    public function setMenu($menu)
    {
        $this->menu = $menu;

        return $this;
    }

    /**
     * Get the value of Parent Id
     *
     * @return mixed
     */
    public function getParentId()
    {
        return $this->parentId;
    }

    /**
     * Set the value of Parent Id
     *
     * @param mixed parentId
     *
     * @return self
     */
    public function setParentId($parentId)
    {
        $this->parentId = $parentId;

        return $this;
    }

    /**
     * Get the value of Icon
     *
     * @return mixed
     */
    public function getIcon()
    {
        return $this->icon;
    }

    /**
     * Set the value of Icon
     *
     * @param mixed icon
     *
     * @return self
     */
    public function setIcon($icon)
    {
        $this->icon = $icon;

        return $this;
    }

    /**
     * Get the value of Label
     *
     * @return mixed
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Set the value of Label
     *
     * @param mixed label
     *
     * @return self
     */
    public function setLabel($label)
    {
        $this->label = $label;

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
     * Get the value of Target
     *
     * @return mixed
     */
    public function getTarget()
    {
        return $this->target;
    }

    /**
     * Set the value of Target
     *
     * @param mixed target
     *
     * @return self
     */
    public function setTarget($target)
    {
        $this->target = $target;

        return $this;
    }

    /**
     * Get the value of Protected
     *
     * @return mixed
     */
    public function getProtected()
    {
        return $this->protected;
    }

    /**
     * Set the value of Protected
     *
     * @param mixed protected
     *
     * @return self
     */
    public function setProtected($protected)
    {
        $this->protected = $protected;

        return $this;
    }

    /**
     * Get the value of Order
     *
     * @return mixed
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * Set the value of Order
     *
     * @param mixed order
     *
     * @return self
     */
    public function setOrder($order)
    {
        $this->order = $order;

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
