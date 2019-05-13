<?php

namespace App\Repository;

use App\Entity\Page;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class PageRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Page::class);
    }

    public function getActivePages()
    {
        $qb = $this->createQueryBuilder('p')
        ->andWhere('p.status = :status')
        ->andWhere('p.publishDate <= :publishDate')
        ->setParameter('status', 1)
        ->setParameter('publishDate', date('Y-m-d'))
        ->getQuery();

        return $qb->execute();
    }

    public function getUserPages(string $email)
    {
        $sql = "SELECT
            	CONCAT(l.locale, '_', pm.route_name) AS route_name,
            	CASE
                    WHEN l.default=1 AND pc.page_route = '' THEN '/'
                    WHEN l.default=1  THEN CONCAT('/', pc.page_route, '/')
                    WHEN pc.page_route = '' THEN CONCAT('/', l.locale, '/')
                    ELSE CONCAT('/', l.locale, '/', pc.page_route, '/')
                END AS route,
            	l.locale,
            	pm.component,
            	pm.props
            FROM permission AS pm
            LEFT JOIN page AS p ON p.id = pm.page_id
            LEFT JOIN page_content AS pc ON p.id = pc.page_id
            LEFT JOIN locale AS l ON pc.locale_id = l.id

            LEFT JOIN role_permission AS rp ON rp.permission_id = pm.id
            LEFT JOIN user_role AS ur ON ur.role_id = rp.role_id
            LEFT JOIN users AS u ON ur.user_id = u.id

            LEFT JOIN user_permission AS up ON up.permission_id = pm.id
            LEFT JOIN users AS u2 ON up.user_id = u2.id

            WHERE pm.route_name LIKE 'page_%'
            AND p.status = 1
            AND p.publish_date < NOW()
            AND (
            	(rp.role_id IS NULL AND up.permission_id IS NULL)
            	OR u.email = '" . $email . "'
            	OR u2.email = '" . $email . "'
            )";

        $conn = $this->getEntityManager()->getConnection();
        $stmt = $conn->prepare($sql);
        //$stmt->execute(['price' => 10]);
        $stmt->execute();

        return $stmt->fetchAll();
    }
}
