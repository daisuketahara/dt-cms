<?php

namespace App\Service;

use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Request;

use App\Entity\MailQueue;
use App\Entity\MailTemplate;
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

    public function addToQueue($toEmail, $tag='', $localeId=0, $variables=array(), $fromName='', $fromEmail='', $toName='')
    {
        $mail = new MailQueue();
        $request = Request::createFromGlobals();

        $template = $this->getDoctrine()
            ->getRepository(MailTemplate::class)
            ->findOneBy(array('tag' => $tag, 'locale' => $localeId));

        $body = $template->getBody();
        $subject = $template->getSubject();
        if ($variables) {
            foreach($variables as $search => $replace) {
                $body = str_replace($search, $replace, $body);
                $subject = str_replace($search, $replace, $subject);
            }
        }

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
