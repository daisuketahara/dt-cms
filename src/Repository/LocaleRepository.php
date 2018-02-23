<?php

namespace App\Repository;

use App\Entity\Locale;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class LocaleRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Locale::class);
    }

    public function findActiveLocales()
    {
        return $this->createQueryBuilder('l')
            ->where('l.active = 1')
            ->orderBy('l.default', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }
}
