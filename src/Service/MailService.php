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
    protected $setting;

    public function __construct(EntityManager $em, \App\Service\SettingService $setting)
    {
        $this->em = $em;
        $this->setting = $setting;
    }

    public function addToQueue($toEmail, $subject, $body, $fromName='', $fromEmail='', $toName='')
    {
        $mail = new MailQueue();
        $request = Request::createFromGlobals();

        if (empty($fromName)) $fromName = $this->setting->getSetting('email.from.name');
        if (empty($fromName)) $fromName = $this->setting->getSetting('site.name');
        if (empty($fromEmail)) $fromEmail = $this->setting->getSetting('email.from');
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
