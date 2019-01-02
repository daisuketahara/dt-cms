<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Doctrine\ORM\EntityManager;

use App\Entity\Block;
use App\Entity\Locale;

class BlockService
{
    protected $em;
    protected $requestStack;

    public function __construct(EntityManager $em, RequestStack $requestStack)
    {
        $this->em = $em;
        $this->requestStack = $requestStack;
    }

    public function getBlock($key)
    {
        $localeTag = $this->requestStack->getCurrentRequest()->getLocale();
        $locale = $this->em->getRepository(Locale::class)
            ->findOneBy(array('locale' => $localeTag), array());

        $block = $this->em->getRepository(Block::class)
            ->findOneBy(array('tag' => $key, 'locale' => $locale), array());

        if (!empty($block)) return html_entity_decode($block->getContent());
        return false;
    }
}
