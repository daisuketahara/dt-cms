<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

use App\Entity\Menu;
use App\Entity\MenuItems;
use App\Entity\Locale;
use App\Entity\Permission;
use App\Service\PageContent;

class NavigationController extends Controller
{
    /**
    * @Route("/{_locale}/admin/menu/", name="admin_menu"))
    */
    final public function menu(TranslatorInterface $translator)
    {
        return $this->render('navigation/admin/menu.html.twig', array(
            'apikey' => 'ce07f59f2eca96d9e3e4dbe2becce743',
            'page_title' => $translator->trans('Menu'),
        ));
    }

}
