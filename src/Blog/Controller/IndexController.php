<?php

namespace App\Blog\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Yaml\Yaml;

use App\Entity\Blog;
use App\Entity\Locale;
use App\Service\LogService;

class IndexController extends AbstractController
{
    /**
    * @Route("/api/v1/block/get/{id}/", name="api_block_get"), methods={"GET","HEAD"})
    */
    final public function getBlock(int $id, Request $request)
    {
        $params = json_decode(file_get_contents("php://input"),true);
        if (!empty($params['locale'])) {
            $localeId = $params['locale'];
            $locale = $this->getDoctrine()
            ->getRepository(Locale::class)
            ->find($localeId);
        }

        if (!empty($id)) {

            $block = $this->getDoctrine()
            ->getRepository(BlockContent::class)
            ->find($id);

            if (!empty($localeId) && $localeId != $block->getLocale()->getId()) {
                $otherBlock = $this->getDoctrine()
                ->getRepository(BlockContent::class)
                ->findOneBy(['locale' => $locale, 'block' => $block->getBlock()]);
                if ($otherBlock) $block = $otherBlock;
                else $block = true;
            }

            if ($block) {
                $response = [
                    'success' => true,
                    'data' => $block,
                ];
            } else {
                $response = [
                    'success' => false,
                    'message' => 'Cannot find block',
                ];
            }
        } else {
            $response = [
                'success' => false,
                'message' => 'Id cannot be empty',
            ];
        }

        $json = $this->serializer->serialize($response, 'json');
        return $this->json($json);
    }
}
