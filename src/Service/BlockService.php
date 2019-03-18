<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Doctrine\ORM\EntityManager;

use App\Entity\Block;
use App\Entity\Locale;
use App\Service\CacheService;

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
        $cache = new CacheService();

        $localeTag = $this->requestStack->getCurrentRequest()->getLocale();
        $locale = $this->em->getRepository(Locale::class)
            ->findOneBy(array('locale' => $localeTag), array());

        if ($cache->has('block.' . $locale->getId() . '.' . $key)) {
            $block = $cache->get('block.' . $locale->getId() . '.' . $key);
            return $block;
        }

        $block = $this->em->getRepository(Block::class)
            ->findOneBy(array('tag' => $key, 'locale' => $locale), array());

        if (!empty($block)) {
            $blockContent = html_entity_decode($block->getContent());
            $cache->set('block.' . $locale->getId() . '.' . $key, $blockContent);
            return $blockContent;
        }
        return false;
    }
}
