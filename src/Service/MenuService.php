<?php

namespace App\Service;

use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;

use App\Entity\Locale;
use App\Entity\MenuItems;

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
        WHERE mi.menu_id = " . $menuId . " AND mi.active = 1 AND mi.parent_id = 0
        ORDER BY mi.order ASC";

        $conn = $this->em->getConnection();
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $menu = $stmt->fetchAll();

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
