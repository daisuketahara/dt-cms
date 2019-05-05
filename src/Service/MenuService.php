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

    public function getMenu($menuId, $useCache=true)
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

        if ($cache->has('menu.' . $locale->getId() . '.' . $menuId) && $useCache) {
            $value = $cache->get('menu.' . $locale->getId() . '.' . $menuId);
            return $value;
        }

        $sql = "SELECT
        	mi.id AS id,
            mi.icon AS icon,
        	CASE
                WHEN mi.label IS NOT NULL AND mi.label <> '' THEN mi.label
                WHEN pm.id IS NOT NULL AND pm.description IS NOT NULL AND pm.description <> '' THEN pm.description
                WHEN pm.id IS NOT NULL THEN pm.route_name
                WHEN p.id IS NOT NULL THEN pc.page_title
            END AS label,
        	CASE
        		WHEN pm.id IS NOT NULL THEN pm.route
        		WHEN p.id IS NOT NULL AND l.default = 0 AND pc.page_route = '' THEN CONCAT(l.locale,'/')
        		WHEN p.id IS NOT NULL AND l.default = 0 THEN CONCAT(l.locale,'/',pc.page_route,'/')
        		WHEN p.id IS NOT NULL AND pc.page_route = '' THEN ''
        		WHEN p.id IS NOT NULL THEN CONCAT(pc.page_route,'/')
        		ELSE mi.route
        	END AS route,
            pm.route_name AS route_name,
            pm.component AS component,
            pm.props AS props
        FROM menu_items AS mi
        LEFT JOIN permission AS pm ON mi.permission_id = pm.id
        LEFT JOIN page AS p ON p.status = 1 AND mi.page_id = p.id
        LEFT JOIN page_content AS pc ON p.id = pc.page_id AND pc.locale_id = " . $locale->getId() . "
        LEFT JOIN locale AS l ON l.id = pc.locale_id
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
            $menuItem['name'] = $mainMenuItem['route'];
            $menuItem['route'] = $mainMenuItem['route'];
            $menuItem['route_name'] = $mainMenuItem['route_name'];
            $menuItem['component'] = $mainMenuItem['component'];
            $menuItem['props'] = $mainMenuItem['props'];

            $mainMenuSub = $this->getSubMenu($mainMenuItem['id']);

            $subMenu = array();

            if ($mainMenuSub) {
                foreach($mainMenuSub as $mainMenuSubItem) {

                    $subMenuItem = array();
                    $subMenuItem['id'] = $mainMenuSubItem['id'];
                    $subMenuItem['icon'] = $mainMenuSubItem['icon'];
                    $subMenuItem['label'] = $mainMenuSubItem['label'];
                    $subMenuItem['route'] = $mainMenuSubItem['route'];
                    $subMenuItem['route_name'] = $mainMenuSubItem['route_name'];
                    $subMenuItem['component'] = $mainMenuSubItem['component'];
                    $subMenuItem['props'] = $mainMenuSubItem['props'];
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
        	CASE
                WHEN mi.label IS NOT NULL AND mi.label <> '' THEN mi.label
                WHEN pm.id IS NOT NULL AND pm.description IS NOT NULL AND pm.description <> '' THEN pm.description
                WHEN pm.id IS NOT NULL THEN pm.route_name
                WHEN p.id IS NOT NULL THEN pc.page_title
            END AS label,
        	CASE
        		WHEN pm.id IS NOT NULL THEN pm.route
        		WHEN p.id IS NOT NULL AND l.default = 0 AND pc.page_route = '' THEN CONCAT(l.locale,'/')
        		WHEN p.id IS NOT NULL AND l.default = 0 THEN CONCAT(l.locale,'/',pc.page_route,'/')
        		WHEN p.id IS NOT NULL AND pc.page_route = '' THEN ''
        		WHEN p.id IS NOT NULL THEN CONCAT(pc.page_route,'/')
        		ELSE mi.route
        	END AS route,
            pm.route_name AS route_name,
            pm.component AS component,
            pm.props AS props
        FROM menu_items AS mi
        LEFT JOIN permission AS pm ON mi.permission_id = pm.id
        LEFT JOIN page AS p ON p.status = 1 AND mi.page_id = p.id
        LEFT JOIN page_content AS pc ON p.id = pc.page_id AND pc.locale_id = " . $locale->getId() . "
        LEFT JOIN locale AS l ON l.id = pc.locale_id
        WHERE mi.active = 1 AND mi.parent_id = " . $parentId . "
        ORDER BY mi.order ASC";

        $conn = $this->em->getConnection();
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $menu = $stmt->fetchAll();

        return $menu;
    }

}
