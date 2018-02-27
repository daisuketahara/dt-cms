<?php

namespace App\Repository;

use App\Entity\Page;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class PageRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Page::class);
    }

    public function findByRoute($route)
    {
        return $this->createQueryBuilder('p')
            ->where('p.pageRoute = :routes')
            ->setParameter('routes', $route)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
