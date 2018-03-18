<?php

namespace App\Repository;

use App\Entity\UserPermission;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method UserPermission|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserPermission|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserPermission[]    findAll()
 * @method UserPermission[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserPermissionRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, UserPermission::class);
    }

    public function getPermissions()
    {



    }
}
