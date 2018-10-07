<?php

namespace App\Service;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\ORM\EntityManager;
use App\Entity\Block;

class BlockService
{
    protected $em;
    protected $container;

    public function __construct(EntityManager $em, ContainerInterface $container)
    {
        $this->em = $em;
        $this->container = $container;
    }

    public function getBlock($key)
    {
        $block = $this->em->getRepository(Block::class)
            ->findBy(array('tag' => $key), array());

        if (!empty($block)) return html_entity_decode($block[0]->getContent());
        return false;
    }
}
