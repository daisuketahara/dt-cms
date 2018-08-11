<?php

namespace App\Service;

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
        $template = $this->em->getRepository(Template::class)
            ->findBy(array('frontend' => 1), array());

        return 'layout/' . $template[0]->getTag() . '/index.html.twig';
    }

    public function getAdminTemplate()
    {
        $template = $this->em->getRepository(Template::class)
            ->findBy(array('admin' => 1), array());

        return 'layout/' . $template[0]->getTag() . '/index.html.twig';
    }

    public function getFooter()
    {
        $template = $this->em->getRepository(Template::class)
            ->findBy(array('frontend' => 1), array());

        return $template[0]->getFooter();
    }
}
