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

    public function get(string $key)
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

    public function set(string $key, $value)
    {
        $setting = $this->em->getRepository(Setting::class)
            ->findOneBy(['settingKey' => $key]);

        if (!$setting) {
            $setting = new Setting();
            $setting->setSettingKey($key);
        }

        $setting->setSettingValue($value);

        $this->em->persist($setting);
        $this->em->flush();

        $cache = new CacheService();

        $value = html_entity_decode($value);
        $cache->set('setting.'.$key, $value);

        return false;
    }
}
