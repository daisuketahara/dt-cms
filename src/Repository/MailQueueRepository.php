<?php

namespace App\Repository;

use App\Entity\MailQueue;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class MailQueueRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, MailQueue::class);
    }

    public function findToSend()
    {
        $qb = $this->createQueryBuilder('m')
            ->andWhere('m.status = 0')
            ->orderBy('m.creationDate', 'ASC')
            //->setParameter('localeId', $localeId)
            ->getQuery();

        return $qb->setMaxResults(30)->execute();
    }

    public function findToDelete()
    {
        $qb = $this->createQueryBuilder('m')
            ->delete()
            ->andWhere('m.status <> 0')
            ->getQuery();

        $result = $qb->execute();
    }
}
