<?php

namespace App\Repository;

use App\Entity\FileGroup;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
* @method FileGroup|null find($id, $lockMode = null, $lockVersion = null)
* @method FileGroup|null findOneBy(array $criteria, array $orderBy = null)
* @method FileGroup[]    findAll()
* @method FileGroup[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
*/
class FileGroupRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, FileGroup::class);
    }
}
