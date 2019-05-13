<?php

namespace App\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

use App\Entity\Locale;
use App\Entity\Translation;

class TranslationRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Translation::class);
    }

    public function findTranslationsList(string $where='', array $order=[], int $limit=0, int $offset=0)
    {
        $sql = "SELECT t.id, t.tag, t.original, ";
        $sql .= "CONCAT(CAST(ROUND(((SELECT COUNT(*) FROM translation_text AS t2 WHERE t2.text <> '' AND t2.text IS NOT NULL AND t2.translation_id=t.id)/(SELECT COUNT(*) FROM locale AS l WHERE l.active=1))*100) AS CHAR(3)),'%') AS complete ";
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
        $sql .= "CASE WHEN tt.text IS NOT NULL THEN tt.text ELSE t.original END AS text ";
        $sql .= "FROM translation AS t ";
        $sql .= "LEFT JOIN translation_text AS tt ON t.id = tt.translation_id AND tt.locale_id=" . $localeId;

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
                $sql .= "LEFT JOIN translation_text AS t" . $i . " ON t" . $i . ".translation_id = t1.id AND t" . $i . ".locale_id = " . $locale->getId() . " ";
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
