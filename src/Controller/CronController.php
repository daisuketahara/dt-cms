<?php

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Translation\TranslatorInterface;
use Cron\CronExpression;

use App\Entity\Cron;


class CronController extends AbstractController
{
    /**
    * @Route("/cron/", name="cron_run")
    */
    final public function cron(Request $request)
    {
        $start = time();
        $baseUrl = $request->getSchemeAndHttpHost();

        $crons = $this->getDoctrine()
        ->getRepository(Cron::class)
        ->findBy(['active' => 1]);

        if ($crons) {
            foreach($crons as $cron) {

                $script = $baseUrl . $cron->getScript();

                $expression = $cron->getMinute() . ' ';
                $expression .= $cron->getHour() . ' ';
                $expression .= $cron->getDay() . ' ';
                $expression .= $cron->getMonth() . ' ';
                $expression .= $cron->getDayOfWeek();

                $cronExpression = CronExpression::factory($expression);
                $count = $cron->getRunCount();
                $lastRun = $cron->getLastRun()->format('Y-m-d H:i:s');
                $nextRun = $cronExpression->getNextRunDate($lastRun)->format('Y-m-d H:i:s');


                if ($start >= strtotime($nextRun)) {

                    $curl = curl_init();
                    curl_setopt($curl, CURLOPT_URL, $script);
                    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
                    // https://stackoverflow.com/questions/2190854/sending-post-requests-without-waiting-for-response
                    //curl_setopt($curl, CURLOPT_TIMEOUT_MS, 1);
                    //curl_setopt($curl, CURLOPT_NOSIGNAL, 1);
                    curl_exec($curl);
                    curl_close($curl);

                    $cron->setLastRun(new \DateTime(date('Y-m-d H:i:s')));
                    $nextRun = $cronExpression->getNextRunDate(new \DateTime(date('Y-m-d H:i:s')))->format('Y-m-d H:i:s');
                    $cron->setNextRun(new \DateTime($nextRun));
                    $cron->setRunCount($count+1);

                    $em = $this->getDoctrine()->getManager();
                    $em->persist($cron);
                    $em->flush();
                }
            }
        }

        $cronlog = 'Cron init: ' . date('Y-m-d H:i:s') . PHP_EOL;

        return new Response($cronlog);
    }
}
