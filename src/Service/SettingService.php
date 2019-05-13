<?php

namespace App\Service;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\ORM\EntityManager;

use App\Entity\Setting;
use App\Service\CacheService;

class SettingService
{
    protected $em;
    protected $container;

    public function __construct(EntityManager $em, ContainerInterface $container)
    {
        $this->em = $em;
        $this->container = $container;
    }

    public function getSetting(string $key)
    {
        $cache = new CacheService();

        if ($cache->has('setting.'.$key)) {
            $value = $cache->get('setting.'.$key);
            return $value;
        }

        $setting = $this->em->getRepository(Setting::class)
        ->findBy(array('settingKey' => $key), array());

        if (!empty($setting)) {
            $value = html_entity_decode($setting[0]->getSettingValue());
            $cache->set('setting.'.$key, $value);
            return $value;
        }
        return false;
    }
}
