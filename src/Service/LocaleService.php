<?php

namespace App\Service;

use Doctrine\ORM\EntityManager;
use App\Entity\Locale;

class LocaleService
{
    protected $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function getActiveLocales()
    {
        $locales = $this->em->getRepository(Locale::class)
            ->findBy(array('active' => true), array());

        return $locales;
    }
}
