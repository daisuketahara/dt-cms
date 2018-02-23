<?php

namespace App\Repository;

use App\Entity\Translation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class TranslationRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Translation::class);
    }

    public function findTranslationsList($where='', $order='', $limit=0, $offset=0) {

        $sql = "SELECT
            	t.id,
            	t.original,
            	CONCAT(CAST(ROUND(((SELECT COUNT(*) FROM translation AS t2 WHERE t2.translation <> '' AND t2.translation IS NOT NULL AND t2.original=t.original)/(SELECT COUNT(*) FROM locale AS l WHERE l.active=1))*100) AS CHAR(3)),'%') AS complete
            FROM translation AS t
            WHERE t.locale_id=(SELECT id FROM locale AS l2 WHERE `default`=1)";

        $conn = $this->getEntityManager()->getConnection();
        $stmt = $conn->prepare($sql);
        //$stmt->execute(['price' => 10]);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function findTranslationsByLocaleId($localeId)
    {
        $qb = $this->createQueryBuilder('t')
            ->andWhere('t.localeId = :localeId')
            ->setParameter('localeId', $localeId)
            ->getQuery();

        return $qb->execute();
    }

    public function findTranslation($original, $localeId)
    {
        $qb = $this->createQueryBuilder('t')
            ->andWhere('t.original = :original')
            ->andWhere('t.localeId = :localeId')
            ->setParameter('original', $original)
            ->setParameter('localeId', $localeId)
            ->getQuery();

        return $qb->setMaxResults(1)->getOneOrNullResult();
    }
}
