<?php

namespace App\Controller\Api;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

use App\Entity\Menu;
use App\Entity\MenuItems;
use App\Entity\Permission;
use App\Service\MenuService;


class NavigationController extends Controller
{
    /**
    * @Route("/api/v1/navigation/list/", name="api_navigation_menu_list"), methods={"GET","HEAD"})
    */
    final public function list(Request $request)
    {
        $menus = $this->getDoctrine()->getRepository(Menu::class)->findAll();

        $encoders = array(new XmlEncoder(), new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());
        $serializer = new Serializer($normalizers, $encoders);

        $json = array(
            'success' => true,
            'data' => $menus,
        );

        $json = $serializer->serialize($json, 'json');

        return $this->json($json);
    }

    /**
    * @Route("/api/v1/navigation/get/{id}/", name="api_navigation_menu_items"), methods={"GET","HEAD"})
    */
    final public function getMenuItems($id, MenuService $menuService)
    {
        $items = $menuService->getMenu($id, false);

        $encoders = array(new XmlEncoder(), new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());
        $serializer = new Serializer($normalizers, $encoders);

        $json = array(
            'success' => true,
            'data' => $items,
        );

        $json = $serializer->serialize($json, 'json');

        return $this->json($json);
    }

    /**
    * @Route("/api/v1/navigation/create/", name="api_navigation_create"), methods={"PUT"})
    */
    final public function createMenu($id, MenuService $menuService)
    {
        $items = $menuService->getMenu($id, false);

        $encoders = array(new XmlEncoder(), new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());
        $serializer = new Serializer($normalizers, $encoders);

        $json = array(
            'success' => true,
            'data' => $items,
        );

        $json = $serializer->serialize($json, 'json');

        return $this->json($json);
    }

    /**
    * @Route("/api/v1/navigation/admin-routes/", name="api_navigation_admin_routes"), methods={"GET","HEAD"})
    */
    final public function getAdminRoutes(MenuService $menuService)
    {
        $this->denyAccessUnlessGranted('ROLE_API');

        $menu = $menuService->getMenu(2, false);
        $email = $this->getUser()->getUsername();
        $permissions = $this->getDoctrine()->getRepository(Permission::class)->getUserPermissions($email);

        $encoders = array(new XmlEncoder(), new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());
        $serializer = new Serializer($normalizers, $encoders);

        $json = array(
            'success' => true,
            'menu' => $menu,
            'permissions' => $permissions,
        );

        $json = $serializer->serialize($json, 'json');

        return $this->json($json);
    }

}
