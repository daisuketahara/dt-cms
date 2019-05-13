<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Doctrine\ORM\EntityManager;

use App\Entity\Block;
use App\Entity\BlockContent;
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

    public function getBlock(string $key)
    {
        $cache = new CacheService();

        $localeTag = $this->requestStack->getCurrentRequest()->getLocale();
        $locale = $this->em->getRepository(Locale::class)
            ->findOneBy(['locale' => $localeTag], array());

        if ($cache->has('block.' . $locale->getId() . '.' . $key)) {
            $block = $cache->get('block.' . $locale->getId() . '.' . $key);
            return $block;
        }

        $block = $this->em->getRepository(Block::class)
            ->findOneBy(['tag' => $key], array());

        if (!empty($block)) {
            $blockContent = $this->em->getRepository(BlockContent::class)
                ->findOneBy(['block' => $block, 'locale' => $locale], array());

            if ($blockContent) {
                $content = html_entity_decode($blockContent->getContent());
                $cache->set('block.' . $locale->getId() . '.' . $key, $content);
                return $content;
            }
        }
        return false;
    }
}
