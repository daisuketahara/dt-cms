<?php

namespace App\Repository;

use App\Entity\UserNote;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method UserNote|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserNote|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserNote[]    findAll()
 * @method UserNote[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserNoteRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, UserNote::class);
    }

    /*
    public function findBySomething($value)
    {
        return $this->createQueryBuilder('u')
            ->where('u.something = :value')->setParameter('value', $value)
            ->orderBy('u.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */
}
