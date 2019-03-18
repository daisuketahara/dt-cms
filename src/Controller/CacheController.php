<?php

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Cache\Simple\FilesystemCache;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Translation\TranslatorInterface;


class CacheController extends Controller
{


    /**
     * @Route("/{_locale}/admin/cache/", name="admin_cache"))
     */
    final public function index(Request $request, TranslatorInterface $translator)
    {
        if ($request->isMethod('POST')) {
            $cache = new FilesystemCache();
            $cache->clear();

            $this->addFlash(
                'success',
                $translator->trans('The cache has been cleared!')
            );
            return $this->redirectToRoute('admin_cache');
        }

        return $this->render('cache/admin/index.html.twig', array(
            'page_title' => $translator->trans('Cache control'),
        ));
    }
}
