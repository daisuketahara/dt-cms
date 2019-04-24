<?php

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Translation\TranslatorInterface;

use App\Entity\Log;
use App\Service\SettingService;

class LogController extends Controller
{
    /**
    * @Route("/{_locale}/admin/log/", name="log"))
    */
    final public function list(TranslatorInterface $translator)
    {
        return $this->render('log/admin/list.html.twig', array(
            'apikey' => 'ce07f59f2eca96d9e3e4dbe2becce743',
            'page_title' => $translator->trans('Log'),
        ));
    }

    /**
    * @Route("/cron/log/clear/", name="log_clear")
    */
    final public function clear(SettingService $setting)
    {
        $days = $setting->getSetting('log.history.days');

        $em = $this->getDoctrine()->getManager();
        $mail = $em->getRepository(Log::class)->deleteOldRecords($days);

        $logMessage = 'Cleared log';

        //$log->add('Mail Queue', 0, $logMessage, 'Clear mail queue');

        return new Response(
            'Old items in log have been cleared'
        );
    }
}
