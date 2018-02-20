<?php

namespace App\Service;

use Doctrine\ORM\EntityManager;
use App\Entity\Log;
use Symfony\Component\HttpFoundation\Request;

class LogService
{
    protected $em;
    protected $request;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function add($entity='',$entityId=0,$logtext='',$comment='')
    {
        $log = new Log();
        $request = Request::createFromGlobals();

        $log->setAccountId(1);
        $log->setEntity($entity);
        $log->setEntityId($entityId);
        $log->setLog($logtext);
        $log->setComment($comment);
        $log->setUserIp($request->getClientIp());
        $log->setCreationDate(new \DateTime("now"));

        $this->em->persist($log);
        $this->em->flush();

        return false;
    }
}
