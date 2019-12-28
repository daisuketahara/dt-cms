<?php

namespace App\Repository;

use App\Entity\UserApiKey;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
* @method UserApiKey|null find($id, $lockMode = null, $lockVersion = null)
* @method UserApiKey|null findOneBy(array $criteria, array $orderBy = null)
* @method UserApiKey[]    findAll()
* @method UserApiKey[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
*/
class UserApiKeyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserApiKey::class);
    }
}
