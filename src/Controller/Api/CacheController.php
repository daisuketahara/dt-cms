<?php

namespace App\Controller\Api;

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
    * @Route("/api/v1/cache/info/", name="api_cache_info"), methods={"GET","HEAD"})
    */
    final public function info(Request $request, TranslatorInterface $translator)
    {
        $info = array(
            'elements' => array(
                [
                    'id' => 'clear-cache',
                    'label' => 'clear_cache',
                    'type' => 'button',
                    'api' => '/cache/clear/'
                ]
            ),
        );
        return $this->json(json_encode($info));
    }

    /**
    * @Route("/api/v1/cache/clear/", name="api_clear_cache"), methods={"GET","HEAD"})
    */
    final public function clearCache(Request $request, TranslatorInterface $translator)
    {
        $cache = new CacheService();
        $cache->clear();

        $response = [
            'success' => true,
            'message' => $translator->trans('Cache has been cleared'),
        ];
        $json = json_encode($response);
        return $this->json($json);
    }
}