<?php

namespace App\Service;

use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Request;

use App\Entity\MailQueue;

class MailService
{
    protected $em;
    protected $request;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function addToQueue($fromName, $fromEmail, $toName, $toEmail, $subject, $body)
    {
        $mail = new MailQueue();
        $request = Request::createFromGlobals();

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
