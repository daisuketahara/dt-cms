<?php

namespace App\Repository;

use App\Entity\Permission;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
* @method Permission|null find($id, $lockMode = null, $lockVersion = null)
* @method Permission|null findOneBy(array $criteria, array $orderBy = null)
* @method Permission[]    findAll()
* @method Permission[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
*/
class PermissionRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Permission::class);
    }

    public function getPermissions() {

        $sql = "SELECT p.id, p.route_name, p.description, CASE WHEN pg.name IS NULL THEN 'Other' ELSE pg.name END AS `name`, CASE WHEN pg.name IS NULL THEN 1 ELSE 0 END AS `sort` ";
        $sql .= "FROM permission AS p ";
        $sql .= "LEFT JOIN permission_group AS pg ON pg.id = p.permission_group_id ";
        $sql .= "ORDER BY sort ASC, `name` ASC, p.route_name ASC";

        $conn = $this->getEntityManager()->getConnection();
        $stmt = $conn->prepare($sql);
        //$stmt->execute(['price' => 10]);
        $stmt->execute();

        return $stmt->fetchAll();
    }
}
