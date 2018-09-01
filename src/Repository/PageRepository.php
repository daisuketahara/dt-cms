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
            ->andWhere('p.status = :status')
            ->setParameter('routes', $route)
            ->setParameter('status', true)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function getActivePages()
    {
        $qb = $this->createQueryBuilder('p')
            ->andWhere('p.status = :status')
            ->andWhere('p.publishDate <= :publishDate')
            ->setParameter('status', 1)
            ->setParameter('publishDate', date('Y-m-d'))
            ->getQuery();

        return $qb->execute();
    }
}
