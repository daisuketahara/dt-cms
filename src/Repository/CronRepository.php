<?php

namespace App\Repository;

use App\Entity\Cron;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
* @method Cron|null find($id, $lockMode = null, $lockVersion = null)
* @method Cron|null findOneBy(array $criteria, array $orderBy = null)
* @method Cron[]    findAll()
* @method Cron[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
*/
class CronRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Cron::class);
    }
}
