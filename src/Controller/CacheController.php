<?php

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Cache\Simple\FilesystemCache;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Translation\TranslatorInterface;

use App\Service\CacheService;

class CacheController extends Controller
{
    /**
    * @Route("/{_locale}/admin/cache/", name="admin_cache"))
    */
    final public function index(Request $request, TranslatorInterface $translator)
    {
        return $this->render('cache/admin/index.html.twig', array(
            'apikey' => 'ce07f59f2eca96d9e3e4dbe2becce743',
            'page_title' => $translator->trans('Cache control'),
        ));
    }
}
