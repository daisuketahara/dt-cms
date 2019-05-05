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
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function checkUserPermission($path, $token) {

        $sql = "SELECT * FROM ( ";
        $sql .= "SELECT route_name, route ";
        $sql .= "FROM user_api_key AS uak ";
        $sql .= "LEFT JOIN users AS u ON uak.user_id = u.id ";
        $sql .= "LEFT JOIN user_permission AS up ON up.user_id = u.id ";
        $sql .= "LEFT JOIN permission AS p ON p.id = up.permission_id ";
        $sql .= "WHERE uak.token = '" . $token . "' ";
        $sql .= "AND p.route LIKE '" . $path . "') p1 ";
        $sql .= "UNION ";
        $sql .= "SELECT route_name, route FROM ( ";
        $sql .= "SELECT p2.route_name, p2.route ";
        $sql .= "FROM user_api_key AS uak ";
        $sql .= "LEFT JOIN users AS u ON uak.user_id = u.id ";
        $sql .= "LEFT JOIN user_role AS ur ON ur.user_id = u.id ";
        $sql .= "LEFT JOIN role_permission AS rp ON rp.role_id = ur.role_id ";
        $sql .= "LEFT JOIN permission AS p2 ON p2.id = rp.permission_id ";
        $sql .= "WHERE uak.token = '" . $token . "' ";
        $sql .= "AND p2.route LIKE '" . $path . "' ";
        $sql .= ") p2";

        $conn = $this->getEntityManager()->getConnection();
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll();

        if (!empty($result[0])) return true;
        return false;
    }

    public function getUserPermissions($email) {

        $sql = "SELECT * FROM ( ";
        $sql .= "SELECT p.* ";
        $sql .= "FROM users AS u ";
        $sql .= "LEFT JOIN user_permission AS up ON up.user_id = u.id ";
        $sql .= "LEFT JOIN permission AS p ON p.id = up.permission_id ";
        $sql .= "WHERE u.email = '" . $email . "' ";
        $sql .= "AND p.route IS NOT NULL) p1 ";
        $sql .= "UNION ";
        $sql .= "SELECT * FROM ( ";
        $sql .= "SELECT p2.* ";
        $sql .= "FROM users AS u ";
        $sql .= "LEFT JOIN user_role AS ur ON ur.user_id = u.id ";
        $sql .= "LEFT JOIN role_permission AS rp ON rp.role_id = ur.role_id ";
        $sql .= "LEFT JOIN permission AS p2 ON p2.id = rp.permission_id ";
        $sql .= "WHERE u.email = '" . $email . "' ";
        $sql .= "AND p2.route IS NOT NULL ";
        $sql .= ") p2";

        $conn = $this->getEntityManager()->getConnection();
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
