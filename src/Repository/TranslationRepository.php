<?php

namespace App\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

use App\Entity\Locale;
use App\Entity\Translation;

class TranslationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Translation::class);
    }

    public function findTranslationsList(string $where='', array $order=[], int $limit=0, int $offset=0)
    {
        $sql = "SELECT t.id, t.tag, t.original, ";
        $sql .= "CONCAT(CAST(ROUND(((SELECT COUNT(*) FROM translation_translation AS t2 WHERE t2.translation <> '' AND t2.translation IS NOT NULL AND t2.translatable_id=t.id)/(SELECT COUNT(*) FROM locale AS l WHERE l.active=1))*100) AS CHAR(3)),'%') AS complete ";
        $sql .= "FROM translation AS t ";
        $sql .= "WHERE 1=1";

        if (!empty($where)) $sql .= " AND " . $where;
        if (!empty($order)) $sql .= " ORDER BY " . $order[0] . " " . $order[1];
        if (!empty($limit)) $sql .= " LIMIT " . $limit;
        if (!empty($offset)) $sql .= " OFFSET " . $offset;

        $conn = $this->getEntityManager()->getConnection();
        $stmt = $conn->prepare($sql);
        //$stmt->execute(['price' => 10]);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function countTranslationsList(string $where='')
    {

        $sql = "SELECT COUNT(*) AS amount FROM translation AS t WHERE 1=1";

        if (!empty($where)) $sql .= " AND " . $where;

        $conn = $this->getEntityManager()->getConnection();
        $stmt = $conn->prepare($sql);
        //$stmt->execute(['price' => 10]);
        $stmt->execute();

        return $stmt->fetch()['amount'];
    }

    public function findTranslationsByLocaleId(int $localeId)
    {
        $sql = "SELECT t.tag, t.original, ";
        $sql .= "CASE WHEN tt.translation IS NOT NULL THEN tt.translation ELSE t.original END AS text ";
        $sql .= "FROM translation AS t ";
        $sql .= "LEFT JOIN translation_translation AS tt ON t.id = tt.translatable_id ";
        $sql .= "LEFT JOIN locale AS l ON l.locale = tt.locale ";
        $sql .= "WHERE l.id=" . $localeId;

        $conn = $this->getEntityManager()->getConnection();
        $stmt = $conn->prepare($sql);
        //$stmt->execute(['price' => 10]);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function findTranslation(string $original, int $localeId)
    {
        $qb = $this->createQueryBuilder('t')
        ->andWhere('t.original = :original')
        ->andWhere('t.locale = :locale')
        ->setParameter('original', $original)
        ->setParameter('locale', $localeId)
        ->getQuery();

        return $qb->setMaxResults(1)->getOneOrNullResult();
    }

    public function getExport()
    {
        $locales = $this->getEntityManager()->getRepository(Locale::class)->findAll();

        $sql = "SELECT t1.id, t1.tag, t1.original";
        $i = 2;
        if (!empty($locales)) {
            foreach($locales as $locale) {
                $sql .= ", t" . $i . ".text AS " . $locale->getLocale() . " ";
                $i++;
            }
        }
        $sql .= " FROM translation AS t1 ";
        $i = 2;
        if (!empty($locales)) {
            foreach($locales as $locale) {
                $sql .= "LEFT JOIN translation_translation AS t" . $i . " ON t" . $i . ".translatable_id = t1.id AND t" . $i . ".locale = " . $locale->getLocale() . " ";
                $i++;
            }
        }

        $conn = $this->getEntityManager()->getConnection();
        $stmt = $conn->prepare($sql);
        //$stmt->execute(['price' => 10]);
        $stmt->execute();

        return $stmt->fetchAll();
    }
}
