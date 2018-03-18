<?php

namespace App\Repository;

use App\Entity\RolePermission;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method RolePermission|null find($id, $lockMode = null, $lockVersion = null)
 * @method RolePermission|null findOneBy(array $criteria, array $orderBy = null)
 * @method RolePermission[]    findAll()
 * @method RolePermission[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RolePermissionRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, RolePermission::class);
    }

    public function getPermissions()
    {



    }
}
