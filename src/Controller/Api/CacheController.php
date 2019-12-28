<?php

namespace App\Controller\Api;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Cache\Simple\FilesystemCache;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\KernelInterface;
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
        return $this->json($info);
    }

    /**
    * @Route("/api/v1/cache/clear/", name="api_clear_cache"), methods={"GET","HEAD"})
    */
    final public function clearCache(Request $request, TranslatorInterface $translator, KernelInterface $kernel)
    {
        $cache = new CacheService();
        $cache->clear();
/*
        $application = new Application($kernel);
        $application->setAutoExit(false);

        $input = new ArrayInput(array(
            'command' => 'cache:clear',
        ));
        $output = new BufferedOutput();
        $application->run($input, $output);
*/
        $response = [
            'success' => true,
            'message' => $translator->trans('Cache has been cleared'),
        ];
        $json = json_encode($response);
        return $this->json($json);
    }
}
