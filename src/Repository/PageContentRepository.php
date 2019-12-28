<?php

namespace App\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

use App\Entity\PageContent;

class PageContentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
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
