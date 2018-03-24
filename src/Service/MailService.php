<?php

namespace App\Service;

use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Request;

use App\Entity\MailQueue;
use App\Service\SettingService;

class MailService
{
    protected $em;
    protected $request;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function addToQueue($toEmail, $subject, $body, $fromName='', $fromEmail='', $toName='', SettingService $setting)
    {
        $mail = new MailQueue();
        $request = Request::createFromGlobals();

        if (empty($fromName)) $fromName = $setting->getSetting('email.from.name');
        if (empty($fromName)) $fromName = $setting->getSetting('site.name');
        if (empty($fromEmail)) $fromEmail = $setting->getSetting('email.from');
        if (empty($toName)) $toName = $toEmail;

        $mail->setFromName($fromName);
        $mail->setFromEmail($fromEmail);
        $mail->setToName($toName);
        $mail->setToEmail($toEmail);
        $mail->setSubject($subject);
        $mail->setBody($body);
        $mail->setCreationDate(new \DateTime("now"));

        $this->em->persist($mail);
        $this->em->flush();

        return false;
    }
}
