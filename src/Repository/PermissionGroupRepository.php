<?php

namespace App\Repository;

use App\Entity\PermissionGroup;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method PermissionGroup|null find($id, $lockMode = null, $lockVersion = null)
 * @method PermissionGroup|null findOneBy(array $criteria, array $orderBy = null)
 * @method PermissionGroup[]    findAll()
 * @method PermissionGroup[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PermissionGroupRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, PermissionGroup::class);
    }

    /*
    public function findBySomething($value)
    {
        return $this->createQueryBuilder('p')
            ->where('p.something = :value')->setParameter('value', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */
}
