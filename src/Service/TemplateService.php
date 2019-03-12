<?php

namespace App\Service;

use Symfony\Component\Cache\Simple\FilesystemCache;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\ORM\EntityManager;
use App\Entity\Template;

class TemplateService
{
    protected $em;
    protected $container;

    public function __construct(EntityManager $em, ContainerInterface $container)
    {
        $this->em = $em;
        $this->container = $container;
    }

    public function getTemplate()
    {
        $cache = new FilesystemCache();

        if ($cache->has('template.front')) {
            $templateFile = $cache->get('template.front');
            return $templateFile;
        }

        $template = $this->em->getRepository(Template::class)
            ->findBy(array('frontend' => 1), array());

        $templateFile = 'layout/' . $template[0]->getTag() . '/index.html.twig';
        $cache->set('template.front', $templateFile);

        return $templateFile;
    }

    public function getAdminTemplate()
    {
        $cache = new FilesystemCache();

        if ($cache->has('template.admin')) {
            $templateFile = $cache->get('template.admin');
            return $templateFile;
        }

        $template = $this->em->getRepository(Template::class)
            ->findBy(array('admin' => 1), array());

        $templateFile = 'layout/' . $template[0]->getTag() . '/index.html.twig';
        $cache->set('template.admin', $templateFile);

        return $templateFile;
    }
}
