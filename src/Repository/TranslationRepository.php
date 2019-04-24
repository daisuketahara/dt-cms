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

    public function findTranslationsList($where='', $order='', $limit=0, $offset=0)
    {
        $sql = "SELECT t.id, t.original, ";
        $sql .= "CONCAT(CAST(ROUND(((SELECT COUNT(*) FROM translation AS t2 WHERE t2.translation <> '' AND t2.translation IS NOT NULL AND t2.original=t.original)/(SELECT COUNT(*) FROM locale AS l WHERE l.active=1))*100) AS CHAR(3)),'%') AS complete ";
        $sql .= "FROM translation AS t ";
        $sql .= "WHERE t.locale_id=(SELECT id FROM locale AS l2 WHERE `default`=1)";

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

    public function countTranslationsList($where='')
    {

        $sql = "SELECT COUNT(*) AS amount FROM translation AS t WHERE t.locale_id=(SELECT id FROM locale AS l2 WHERE `default`=1)";

        if (!empty($where)) $sql .= " AND " . $where;

        $conn = $this->getEntityManager()->getConnection();
        $stmt = $conn->prepare($sql);
        //$stmt->execute(['price' => 10]);
        $stmt->execute();

        return $stmt->fetch()['amount'];
    }

    public function findTranslationsByLocaleId($localeId)
    {
        $qb = $this->createQueryBuilder('t')
        ->andWhere('t.locale = :locale')
        ->setParameter('locale', $localeId)
        ->getQuery();

        return $qb->execute();
    }

    public function findTranslation($original, $localeId)
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

        $sql = "SELECT t1.id, t1.original";
        $i = 2;
        if (!empty($locales)) {
            foreach($locales as $locale) {
                $sql .= ", t" . $i . ".translation AS " . $locale->getLocale() . " ";
                $i++;
            }
        }
        $sql .= " FROM translation AS t1 ";
        $i = 2;
        if (!empty($locales)) {
            foreach($locales as $locale) {
                if ($locale->getDefault()) $sql .= "LEFT JOIN translation AS t" . $i . " ON t" . $i . ".original = t1.original AND t" . $i . ".locale_id = " . $locale->getId() . " ";
                else $sql .= "LEFT JOIN translation AS t" . $i . " ON t" . $i . ".parent_id = t1.id AND t" . $i . ".locale_id = " . $locale->getId() . " ";
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
