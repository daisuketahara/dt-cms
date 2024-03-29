<?php

namespace App\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

use App\Entity\Locale;
use App\Entity\AppTranslation;

class AppTranslationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AppTranslation::class);
    }

    public function findTranslationsList(string $where='', array $order=[], int $limit=0, int $offset=0)
    {

        $sql = "SELECT t.id, t.tag, ";
        $sql .= "CONCAT(CAST(ROUND(((SELECT COUNT(*) FROM app_translation AS t2 WHERE t2.translation <> '' AND t2.translation IS NOT NULL AND t2.tag=t.tag)/(SELECT COUNT(*) FROM locale AS l WHERE l.active=1))*100) AS CHAR(3)),'%') AS complete ";
        $sql .= "FROM app_translation AS t ";
        $sql .= "WHERE t.locale_id=(SELECT id FROM locale AS l2 WHERE `default`=1)";

        $conn = $this->getEntityManager()->getConnection();
        $stmt = $conn->prepare($sql);
        //$stmt->execute(['price' => 10]);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function countTranslationsList(string $where='')
    {

        $sql = "SELECT COUNT(*) AS amount FROM app_translation AS t
        WHERE t.locale_id=(SELECT id FROM locale AS l2 WHERE `default`=1)";

        if (!empty($where)) $sql .= " AND " . $where;

        $conn = $this->getEntityManager()->getConnection();
        $stmt = $conn->prepare($sql);
        //$stmt->execute(['price' => 10]);
        $stmt->execute();

        return $stmt->fetch()['amount'];
    }

    public function findTranslationsByLocaleId(int $localeId)
    {
        $qb = $this->createQueryBuilder('t')
        ->andWhere('t.locale = :locale')
        ->setParameter('locale', $localeId)
        ->getQuery();

        return $qb->execute();
    }

    public function findTranslation(string $tag, int $localeId)
    {
        $qb = $this->createQueryBuilder('t')
        ->andWhere('t.tag = :tag')
        ->andWhere('t.locale = :locale')
        ->setParameter('tag', $tag)
        ->setParameter('locale', $localeId)
        ->getQuery();

        return $qb->setMaxResults(1)->getOneOrNullResult();
    }

    public function getExport()
    {
        $locales = $this->getEntityManager()->getRepository(Locale::class)->findAll();

        $sql = "SELECT t1.id, t1.tag";
        $i = 2;
        if (!empty($locales)) {
            foreach($locales as $locale) {
                $sql .= ", t" . $i . ".translation AS " . $locale->getLocale() . " ";
                $i++;
            }
        }
        $sql .= " FROM app_translation AS t1 ";
        $i = 2;
        if (!empty($locales)) {
            foreach($locales as $locale) {
                if ($locale->getDefault()) $sql .= "LEFT JOIN app_translation AS t" . $i . " ON t" . $i . ".tag = t1.tag AND t" . $i . ".locale_id = " . $locale->getId() . " ";
                else $sql .= "LEFT JOIN app_translation AS t" . $i . " ON t" . $i . ".parent_id = t1.id AND t" . $i . ".locale_id = " . $locale->getId() . " ";
                $i++;
            }
        }
        $sql .= "WHERE t1.parent_id IS NULL OR t1.parent_id = 0";

        $conn = $this->getEntityManager()->getConnection();
        $stmt = $conn->prepare($sql);
        //$stmt->execute(['price' => 10]);
        $stmt->execute();

        return $stmt->fetchAll();
    }
}
