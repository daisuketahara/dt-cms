<?php

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Translation\TranslatorInterface;

use App\Entity\Log;
use App\Entity\UserApiKey;
use App\Service\SettingService;

class SystemController extends AbstractController
{
    /**
    * @Route("/cron/log/clear/", name="cron_log_clear")
    */
    final public function clearLog(SettingService $setting)
    {
        $days = $setting->get('log.history.days');
        if (!$days) $days = 14;

        $em = $this->getDoctrine()->getManager();
        $mail = $em->getRepository(Log::class)->deleteOldRecords($days);

        $logMessage = 'Cleared log';

        //$log->add('Mail Queue', 0, $logMessage, 'Clear mail queue');

        return new Response(
            'Old items in log have been cleared'
        );
    }

    /**
    * @Route("/cron/user-api-key/clear/", name="cron_user_api_key_clear")
    */
    final public function clearOldUserApiKeys()
    {
        $em = $this->getDoctrine()->getManager();
        $mail = $em->getRepository(UserApiKey::class)->deleteOldKeys();

        $logMessage = 'Cleared old user api keys';

        //$log->add('Mail Queue', 0, $logMessage, 'Clear mail queue');

        return new Response(
            'Cleared old user api keys'
        );
    }
}
