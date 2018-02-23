<?php

namespace App\Service;

use Doctrine\ORM\EntityManager;
use App\Entity\MenuItems;

class MenuService
{
    protected $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function getMenu($menuId)
    {
        $menu = $this->em->getRepository(MenuItems::class)
            ->findBy(array('menuId' => $menuId, 'parentId' => 0, 'active' => 1), array('order' => 'ASC'));
        return $menu;
    }

    public function getSubMenu($parentId)
    {
        $menu = $this->em->getRepository(MenuItems::class)->findBy(array('parentId' => $parentId, 'active' => 1), array('order' => 'ASC'));
        return $menu;
    }

}
