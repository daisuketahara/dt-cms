<?php

namespace App\Repository;

use App\Entity\Redirect;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
* @method Redirect|null find($id, $lockMode = null, $lockVersion = null)
* @method Redirect|null findOneBy(array $criteria, array $orderBy = null)
* @method Redirect[]    findAll()
* @method Redirect[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
*/
class RedirectRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Redirect::class);
    }
}
