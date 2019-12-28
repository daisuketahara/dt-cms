<?php

namespace App\Repository;

use App\Entity\Role;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
* @method UserRole|null find($id, $lockMode = null, $lockVersion = null)
* @method UserRole|null findOneBy(array $criteria, array $orderBy = null)
* @method UserRole[]    findAll()
* @method UserRole[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
*/
class RoleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Role::class);
    }

    public function findByRolesByPermissionId(int $permission_id)
    {
        $sql = "SELECT r.id FROM role_permission AS rp ";
        $sql .= "LEFT JOIN role AS r ON rp.role_id=r.id ";
        $sql .= "WHERE rp.permission_id=" . $permission_id;

        $conn = $this->getEntityManager()->getConnection();
        $stmt = $conn->prepare($sql);
        //$stmt->execute(['price' => 10]);
        $stmt->execute();

        return $stmt->fetchAll();
    }
}
