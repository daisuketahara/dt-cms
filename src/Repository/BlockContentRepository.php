<?php

namespace App\Repository;

use App\Entity\BlockContent;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
* @method Block|null find($id, $lockMode = null, $lockVersion = null)
* @method Block|null findOneBy(array $criteria, array $orderBy = null)
* @method Block[]    findAll()
* @method Block[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
*/
class BlockContentRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, BlockContent::class);
    }
}
