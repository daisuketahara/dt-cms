<?php

namespace App\Repository;

use App\Entity\FileGroup;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
* @method FileGroup|null find($id, $lockMode = null, $lockVersion = null)
* @method FileGroup|null findOneBy(array $criteria, array $orderBy = null)
* @method FileGroup[]    findAll()
* @method FileGroup[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
*/
class FileGroupRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FileGroup::class);
    }
}
