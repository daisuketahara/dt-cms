<?php

namespace App\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

use App\Entity\PageContent;

class PageContentRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, PageContent::class);
    }

    public function getActivePages()
    {
        $qb = $this->createQueryBuilder('pc')
        ->select('pc', 'p')
        ->leftJoin('pc.page', 'p')
        ->andWhere('p.status = :status')
        ->andWhere('p.publishDate <= :publishDate')
        ->setParameter('status', 1)
        ->setParameter('publishDate', date('Y-m-d'))
        ->getQuery();

        return $qb->execute();
    }

    public function findByRoute(string $route)
    {
        return $this->createQueryBuilder('p')
        ->where('p.pageRoute = :routes')
        ->andWhere('p.status = :status')
        ->setParameter('routes', $route)
        ->setParameter('status', true)
        ->getQuery()
        ->getOneOrNullResult();
    }
}
