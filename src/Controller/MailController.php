<?php

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Translation\TranslatorInterface;

use App\Entity\MailQueue;
use App\Service\LogService;
use App\Service\SettingService;


class MailController extends AbstractController
{
    /**
    * @Route("/cron/mail/queue/send/", name="cron_mail_queue_send")
    */
    final public function send(LogService $log, SettingService $setting, \Swift_Mailer $mailer)
    {

        $em = $this->getDoctrine()->getManager();
        $mails = $em->getRepository(MailQueue::class)->findToSend();

        $success = 0;
        $fail = 0;
        foreach($mails as $mail) {

            $from = $mail->getFromEmail();
            if (!$from) $from = $setting->get('mail.from');

            $message = (new \Swift_Message($mail->getSubject()))
            ->setFrom($from)
            ->setTo($mail->getToEmail())
            ->setBody($mail->getBody(), 'text/html');
            if ($mailer->send($message)) {
                $mail->setSendDate(new \DateTime("now"));
                $mail->setStatus(1);
                $success++;
            } else {
                $mail->setStatus(-1);
                $fail++;
            }

            $em = $this->getDoctrine()->getManager();
            $em->persist($mail);
            $em->flush();
        }


        $logMessage = $success . ' send emails cleared from queue. ' . $fail . ' failed attempts.';

        if (!empty($success) || !empty($fail)) $log->add('Mail Queue', 0, $logMessage, 'Send mail queue');

        return new Response(
            $success . ' send emails cleared from queue. ' . $fail . ' failed attempts.'
        );
    }

    /**
    * @Route("/cron/mail/queue/clear/", name="cron_mail_queue_clear")
    */
    final public function clear(SettingService $setting, LogService $log)
    {
        $days = $setting->get('email.history.days');

        $em = $this->getDoctrine()->getManager();
        $mail = $em->getRepository(MailQueue::class)->deleteOldRecords($days);

        $logMessage = 'Emails cleared from queue';

        //$log->add('Mail Queue', 0, $logMessage, 'Clear mail queue');

        return new Response(
            'Emails cleared from queue'
        );
    }
}
