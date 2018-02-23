<?php

namespace App\Service;

use Doctrine\ORM\EntityManager;
use App\Entity\Setting;

class SettingService
{
    protected $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function getSetting($key)
    {
        $setting = $this->em->getRepository(Setting::class)
            ->findBy(array('settingKey' => $key), array());

        if ($setting) return html_entity_decode($setting[0]->getSettingValue());
        return false;
    }
}
