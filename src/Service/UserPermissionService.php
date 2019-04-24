<?php

namespace App\Service;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Doctrine\ORM\EntityManager;
use App\Entity\User;

class UserPermissionService extends Controller
{
    protected $em;
    protected $container;

    public function __construct(EntityManager $em, ContainerInterface $container)
    {
        $this->em = $em;
        $this->container = $container;
    }

    public function getUserPermissions()
    {
        $user = $this->getUser();

        $sql =  "SELECT p.route_name FROM user_permission AS up ";
        $sql .=  "LEFT JOIN permission AS p ON up.permission_id = p.id ";
        $sql .=  "WHERE up.user_id = " . $user->getId() . " ";
        $sql .=  "GROUP BY p.route_name ";
        $sql .=  "UNION ";
        $sql .=  "SELECT p2.route_name FROM user_role AS ur ";
        $sql .=  "LEFT JOIN role_permission AS rp ON ur.role_id = rp.role_id ";
        $sql .=  "LEFT JOIN permission AS p2 ON p2.id = rp.permission_id ";
        $sql .=  "WHERE ur.user_id = " . $user->getId() . " ";
        $sql .=  "GROUP BY p2.route_name";

        $conn = $this->em->getConnection();
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll();

        $permissions = array();

        if ($result)
        foreach($result as $row) $permissions[] = $row['route_name'];

        return $permissions;
    }
}
