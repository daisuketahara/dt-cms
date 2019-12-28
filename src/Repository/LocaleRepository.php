<?php

namespace App\Repository;

use App\Entity\Locale;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

class LocaleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Locale::class);
    }

    public function findActiveLocales()
    {
        return $this->createQueryBuilder('l')
            ->where('l.active = 1')
            ->orderBy('l.default', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function getDefaultLocale()
    {
        return $this->createQueryBuilder('l')
            ->where('l.default = 1')
            ->getQuery()
            ->getSingleResult();
    }
}
