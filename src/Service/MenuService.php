<?php

namespace App\Service;

use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;

use App\Entity\Locale;
use App\Entity\MenuItems;
use App\Service\CacheService;

class MenuService
{
    protected $em;
    protected $requestStack;

    public function __construct(EntityManager $em, RequestStack $requestStack)
    {
        $this->em = $em;
        $this->requestStack = $requestStack;
    }

    public function getMenu($menuId)
    {
        $cache = new CacheService();
        $session = new Session();
        $_locale = $this->requestStack->getCurrentRequest()->getLocale();

        $locale = $this->em->getRepository(Locale::class)
            ->findOneBy(array('locale' => $_locale));

        if (!$locale) {
            $locale = $this->em->getRepository(Locale::class)
                ->findOneBy(array('default' => true));
        }

        if ($cache->has('menu.' . $locale->getId() . '.' . $menuId)) {
            $value = $cache->get('menu.' . $locale->getId() . '.' . $menuId);
            return $value;
        }

        $sql = "SELECT
        	mi.id AS id,
            mi.icon AS icon,
        	CASE WHEN p.id IS NOT NULL THEN p.page_title ELSE mi.label END AS label,
        	CASE
        		WHEN p.id IS NOT NULL AND l.default = 0 AND p.page_route = '' THEN CONCAT(l.locale,'/')
        		WHEN p.id IS NOT NULL AND l.default = 0 THEN CONCAT(l.locale,'/',p.page_route,'/')
        		WHEN p.id IS NOT NULL AND p.page_route = '' THEN ''
        		WHEN p.id IS NOT NULL THEN CONCAT(p.page_route,'/')
        		ELSE mi.route
        	END AS route
        FROM menu_items AS mi
        LEFT JOIN page AS p ON p.status = 1 AND p.locale_id = " . $locale->getId() . " AND (mi.page_id = p.id OR mi.page_id = p.default_id)
        LEFT JOIN locale AS l ON l.id = p.locale_id
        WHERE mi.menu_id = " . $menuId . " AND mi.active = 1 AND mi.parent_id = 0
        ORDER BY mi.order ASC";

        $conn = $this->em->getConnection();
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $mainMenu = $stmt->fetchAll();

        $menu = array();

        foreach($mainMenu as $mainMenuItem) {

            $menuItem = array();
            $menuItem['id'] = $mainMenuItem['id'];
            $menuItem['icon'] = $mainMenuItem['icon'];
            $menuItem['label'] = $mainMenuItem['label'];
            $menuItem['route'] = $mainMenuItem['route'];

            $mainMenuSub = $this->getSubMenu($mainMenuItem['id']);

            $subMenu = array();

            if ($mainMenuSub) {
                foreach($mainMenuSub as $mainMenuSubItem) {

                    $subMenuItem = array();
                    $subMenuItem['id'] = $mainMenuSubItem['id'];
                    $subMenuItem['icon'] = $mainMenuSubItem['icon'];
                    $subMenuItem['label'] = $mainMenuSubItem['label'];
                    $subMenuItem['route'] = $mainMenuSubItem['route'];
                    $subMenu[] = $subMenuItem;
                }
                if (!empty($subMenu)) $menuItem['submenu'] = $subMenu;
            }

            $menu[] = $menuItem;
        }

        $cache->set('menu.' . $locale->getId() . '.' . $menuId, $menu);
        return $menu;
    }

    public function getSubMenu($parentId)
    {
        $session = new Session();
        $_locale = $this->requestStack->getCurrentRequest()->getLocale();

        $locale = $this->em->getRepository(Locale::class)
            ->findOneBy(array('locale' => $_locale));

        if (!$locale) {
            $locale = $this->em->getRepository(Locale::class)
                ->findOneBy(array('default' => true));
        }

        $sql = "SELECT
        	mi.id AS id,
            mi.icon AS icon,
        	CASE WHEN p.id IS NOT NULL THEN p.page_title ELSE mi.label END AS label,
        	CASE
        		WHEN p.id IS NOT NULL AND l.default = 0 AND p.page_route = '' THEN CONCAT(l.locale,'/')
        		WHEN p.id IS NOT NULL AND l.default = 0 THEN CONCAT(l.locale,'/',p.page_route,'/')
        		WHEN p.id IS NOT NULL AND p.page_route = '' THEN ''
        		WHEN p.id IS NOT NULL THEN CONCAT(p.page_route,'/')
        		ELSE mi.route
        	END AS route
        FROM menu_items AS mi
        LEFT JOIN page AS p ON p.status = 1 AND p.locale_id = " . $locale->getId() . " AND (mi.page_id = p.id OR mi.page_id = p.default_id)
        LEFT JOIN locale AS l ON l.id = p.locale_id
        WHERE mi.active = 1 AND mi.parent_id = " . $parentId . "
        ORDER BY mi.order ASC";

        $conn = $this->em->getConnection();
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $menu = $stmt->fetchAll();

        return $menu;
    }

}
