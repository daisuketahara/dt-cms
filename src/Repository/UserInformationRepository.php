<?php

namespace App\Repository;

use App\Entity\UserInformation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
* @method UserInformation|null find($id, $lockMode = null, $lockVersion = null)
* @method UserInformation|null findOneBy(array $criteria, array $orderBy = null)
* @method UserInformation[]    findAll()
* @method UserInformation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
*/
class UserInformationRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, UserInformation::class);
    }
}
