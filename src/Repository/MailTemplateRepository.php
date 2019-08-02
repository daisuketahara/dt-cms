<?php

namespace App\Repository;

use App\Entity\MailTemplate;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class MailTemplateRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, MailTemplate::class);
    }

    public function findTemplate(string $tag, int $localeId)
    {
        $sql = "SELECT mtc.*
                FROM mail_template AS mt
                LEFT JOIN mail_template_content AS mtc ON mt.id = mtc.mail_template_id
                WHERE mt.tag = '$tag' AND mtc.locale_id = $localeId
                LIMIT 1";

        $conn = $this->getEntityManager()->getConnection();
        $stmt = $conn->prepare($sql);
        //$stmt->execute(['price' => 10]);
        $stmt->execute();

        return $stmt->fetch();
    }
}
