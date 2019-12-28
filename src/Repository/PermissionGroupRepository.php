<?php

namespace App\Repository;

use App\Entity\PermissionGroup;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
* @method PermissionGroup|null find($id, $lockMode = null, $lockVersion = null)
* @method PermissionGroup|null findOneBy(array $criteria, array $orderBy = null)
* @method PermissionGroup[]    findAll()
* @method PermissionGroup[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
*/
class PermissionGroupRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PermissionGroup::class);
    }
}
