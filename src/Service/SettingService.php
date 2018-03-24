<?php

namespace App\Service;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\ORM\EntityManager;
use App\Entity\Setting;

class SettingService
{
    protected $em;
    protected $container;

    public function __construct(EntityManager $em, ContainerInterface $container)
    {
        $this->em = $em;
        $this->container = $container;
    }

    public function getSetting($key)
    {
        $setting = $this->em->getRepository(Setting::class)
            ->findBy(array('settingKey' => $key), array());

        if (!empty($setting)) return html_entity_decode($setting[0]->getSettingValue());

        $parameter = $this->container->getParameter($key);
        if (!empty($parameter)) return $parameter;

        return false;
    }
}
