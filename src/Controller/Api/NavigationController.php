<?php

namespace App\Controller\Api;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

use App\Entity\Locale;
use App\Entity\Menu;
use App\Entity\MenuItems;
use App\Entity\Page;
use App\Entity\PageContent;
use App\Entity\Permission;
use App\Service\CacheService;
use App\Service\MenuService;
use App\Service\LogService;


class NavigationController extends AbstractController
{
    private $serializer;

    public function __construct() {
        $encoders = array(new XmlEncoder(), new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());
        $this->serializer = new Serializer($normalizers, $encoders);
    }

    /**
    * @Route("/api/v1/navigation/list/", name="api_navigation_menu_list"), methods={"GET","HEAD"})
    */
    final public function list(Request $request)
    {
        $menus = $this->getDoctrine()->getRepository(Menu::class)->findAll();

        $encoders = array(new XmlEncoder(), new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());
        $this->serializer = new Serializer($normalizers, $encoders);

        $json = array(
            'success' => true,
            'data' => $menus,
        );

        $json = $this->serializer->serialize($json, 'json');

        return $this->json($json);
    }

    /**
    * @Route("/api/v1/navigation/get/{id}/", name="api_navigation_menu_items"), methods={"GET","HEAD"})
    */
    final public function getMenuItems($id, MenuService $menuService)
    {
        $items = $menuService->getMenu($id, true);
        $json = array(
            'success' => true,
            'data' => $items,
        );

        $json = $this->serializer->serialize($json, 'json');

        return $this->json($json);
    }

    /**
    * @Route("/api/v1/navigation/get-to-edit/{id}/", name="api_navigation_menu_items_edit"), methods={"GET","HEAD"})
    */
    final public function getMenuItemsToEdit($id, MenuService $menuService)
    {
        $items = $menuService->getMenu($id, false, true);
        $json = array(
            'success' => true,
            'data' => $items,
        );

        $json = $this->serializer->serialize($json, 'json');

        return $this->json($json);
    }

    /**
    * @Route("/api/v1/navigation/create/", name="api_navigation_create"), methods={"PUT"})
    */
    final public function createMenu()
    {
        $params = json_decode(file_get_contents("php://input"),true);

        $menu = new Menu();

        if (isset($params['name'])) $menu->setName($params['name']);
        else $errors[] = 'Name cannot be empty';

        if (!empty($errors)) {

            $json = json_encode([
                'success' => false,
                'message' => $errors,
            ]);
            return $this->json($json);
        }

        $em = $this->getDoctrine()->getManager();
        $em->persist($menu);
        $em->flush();
        $id = $menu->getId();

        $json = json_encode([
            'success' => true,
            'id' => $id,
        ]);
        return $this->json($json);
    }

    /**
    * @Route("/api/v1/navigation/delete/{id}/", name="api_navigation_delete", methods={"DELETE"})
    */
    final public function delete(int $id=0, LogService $log)
    {
        $params = json_decode(file_get_contents("php://input"),true);
        if (!empty($params['ids'])) $toRemove = $params['ids'];
        elseif (!empty($id)) $toRemove = array($id);

        if (!empty($id)) {
            $em = $this->getDoctrine()->getManager();
            $menu = $em->getRepository(Menu::class)->find($id);
            if ($menu) {
                $log->add('Menu', $id, '', 'Delete');
                $em->remove($menu);
                $em->flush();
            }
            $response = ['success' => true];

        } else {
            $response = [
                'success' => false,
                'message' => 'Id cannot be empty',
            ];
        }

        $json = json_encode($response);
        return $this->json($json);
    }

    /**
    * @Route("/api/v1/navigation/update/{id}/", name="api_navigation_update"), methods={"PUT"})
    */
    final public function updateMenu(int $id)
    {
        $params = json_decode(file_get_contents("php://input"),true);
        $em = $this->getDoctrine()->getManager();

        if (!empty($id)) {
            $menu = $this->getDoctrine()
            ->getRepository(Menu::class)
            ->find($id);
            if ($menu) {

                $items = $this->getDoctrine()
                ->getRepository(MenuItems::class)
                ->findBy(['menu' => $menu]);

                foreach ($items as $item) {
                    $em->remove($item);
                }
                $em->flush();

                $order = 0;

                foreach($params as $item) {

                    $menuItem = new MenuItems();
                    $menuItem->setMenu($menu);
                    $menuItem->setLabel($item['label']);
                    $menuItem->setProtected(false);
                    //$menuItem->setParentId();
                    $menuItem->setSort($order);

                    if (isset($item['icon'])) $menuItem->setIcon($item['icon']);
                    if (isset($item['active'])) $menuItem->setActive($item['active']);
                    else $menuItem->setActive(false);

                    if (isset($item['permission_id'])) {
                        $permission = $this->getDoctrine()
                            ->getRepository(Permission::class)
                            ->find($item['permission_id']);
                        $menuItem->setPermission($permission);
                    } elseif (isset($item['route'])){
                        $menuItem->setRoute($item['route']);
                    }

                    $em->persist($menuItem);
                    $em->flush();
                    $parentId = $menuItem->getId();

                    if (!empty($item['submenu'])) {

                        $subOrder = 0;
                        foreach($item['submenu'] as $subItem) {

                            $menuSubItem = new MenuItems();
                            $menuSubItem->setMenu($menu);
                            $menuSubItem->setLabel($subItem['label']);
                            $menuSubItem->setProtected(false);
                            $menuSubItem->setParentId($parentId);
                            $menuSubItem->setSort($subOrder);

                            if (isset($subItem['icon'])) $menuSubItem->setIcon($subItem['icon']);
                            if (isset($subItem['active'])) $menuSubItem->setActive($subItem['active']);
                            if (isset($subItem['permission_id'])) {
                                $permission = $this->getDoctrine()
                                    ->getRepository(Permission::class)
                                    ->find($subItem['permission_id']);
                                $menuSubItem->setPermission($permission);
                            } elseif (isset($item['route'])){
                                $menuItem->setRoute($item['route']);
                            }

                            $em->persist($menuSubItem);
                            $em->flush();

                            $subOrder++;
                        }
                    }

                    $order++;
                }

                $json = json_encode([
                    'success' => true,
                ]);

                $cache = new CacheService();

                $locales = $this->getDoctrine()
                    ->getRepository(Locale::class)
                    ->findAll();
                foreach($locales as $locale) {
                    $cache->delete('menu.' . $locale->getId() . '.' . $id);
                }

            } else {
                $json = json_encode([
                    'success' => false,
                    'message' => 'Menu does not exist',
                ]);
            }
        } else {
            $json = json_encode([
                'success' => false,
                'message' => 'Id cannot be empty',
            ]);
        }

        return $this->json($json);
    }

    /**
    * @Route("/api/v1/app-routes/", name="api_routes"), methods={"GET","HEAD"})
    */
    final public function getRoutes(MenuService $menuService)
    {
        $menu = $menuService->getMenu(1, false);
        if ($this->getUser()) $email = $this->getUser()->getUsername();
        else $email= '';
        $pages = $this->getDoctrine()->getRepository(Page::class)->getUserPages($email);
        $permissions = $this->getDoctrine()->getRepository(Permission::class)->getUserPermissions($email);

        $json = array(
            'success' => true,
            'menu' => $menu,
            'pages' => $pages,
            'permissions' => $permissions,
        );

        $json = $this->serializer->serialize($json, 'json');

        return $this->json($json);
    }

    /**
    * @Route("/api/v1/admin-routes/", name="api_admin_routes"), methods={"GET","HEAD"})
    */
    final public function getAdminRoutes(MenuService $menuService)
    {
        //$this->denyAccessUnlessGranted('ROLE_API');

        $roles = $this->getUser()->getUserRoles();
        if (!empty($roles)) {
            $menu = $this->getUser()->getUserRoles()[0]->getAdminMenu();
            if ($menu) {
                $menu = $menuService->getMenu($menu->getId(), false);
            }
        }

        if (empty($menu)) $menu = [];
        $email = $this->getUser()->getUsername();
        $permissions = $this->getDoctrine()->getRepository(Permission::class)->getUserPermissions($email);

        $json = array(
            'success' => true,
            'menu' => $menu,
            'permissions' => $permissions,
        );

        $json = $this->serializer->serialize($json, 'json');

        return $this->json($json);
    }

    /**
    * @Route("/api/v1/navigation/routes/", name="api_all_routes"), methods={"GET","HEAD"})
    */
    final public function getAllRoutes(MenuService $menuService)
    {
        $pages = $this->getDoctrine()->getRepository(Permission::class)->getAllNavigationRoutesByType('page');
        $app = $this->getDoctrine()->getRepository(Permission::class)->getAllNavigationRoutesByType('app');
        $admin = $this->getDoctrine()->getRepository(Permission::class)->getAllNavigationRoutesByType('admin');

        $json = array(
            'success' => true,
            'pages' => $pages,
            'app' => $app,
            'admin' => $admin,
        );

        $json = $this->serializer->serialize($json, 'json');
        return $this->json($json);
    }
}
