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
    * @Route("/cron/log/clear/", name="cron_log_clear")
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
