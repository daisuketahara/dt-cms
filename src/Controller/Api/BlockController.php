<?php

namespace App\Controller\Api;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Yaml\Yaml;

use App\Entity\Block;
use App\Entity\BlockContent;
use App\Entity\Locale;
use App\Service\LogService;

class BlockController extends Controller
{
    private $serializer;

    public function __construct() {
        $encoders = array(new XmlEncoder(), new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());
        $this->serializer = new Serializer($normalizers, $encoders);
    }

    /**
    * @Route("/api/v1/block/info/", name="api_block_info"), methods={"GET","HEAD"})
    */
    final public function info(Request $request, TranslatorInterface $translator)
    {
        $properties = Yaml::parseFile('src/Config/block.yaml');

        $api = [];
        $settings = ['title' => $translator->trans('text_blocks')];

        if (!empty($properties['actions'])) {
            foreach($properties['actions'] as $key => $action) {
                if (!empty($action['api'])) $api[$key] = $action['api'];
                elseif (!empty($action['url'])) $settings[$key] = $action['url'];
            }
        }

        $info = array(
            'api' => $api,
            'settings' => $settings,
            'fields' => $properties['fields'],
        );

        return $this->json($info);
    }

    /**
    * @Route("/api/v1/block/list/", name="api_block_list"), methods={"GET","HEAD"})
    */
    final public function list(Request $request)
    {
        $params = json_decode(file_get_contents("php://input"),true);
        if (!empty($params['sort'])) $sort_column = $params['sort']; else $sort_column = 'id';
        if (!empty($params['dir'])) $sort_direction = $params['dir']; else $sort_direction = 'desc';
        if (!empty($params['limit'])) $limit = $params['limit']; else $limit = 15;
        if (!empty($params['offset'])) $offset = $params['offset']; else $offset = 0;
        if (!empty($params['filter'])) $filter = $params['filter'];
        if (!empty($params['locale'])) $localeId = $params['locale'];

        $locale = $this->getDoctrine()
        ->getRepository(Locale::class)
        ->find($localeId);

        $whereString = 'l.locale='. $locale->getId();
        if (!empty($filter)) {
            parse_str($filter, $filter_array);
            foreach($filter_array as $key => $value) {
                if (!empty($value)) {
                    //$where[$key] = $value;
                    $whereString .= " AND l." . $key . " LIKE '%" . $value . "%'";
                }
            }
        }

        $qb = $this->getDoctrine()->getRepository(BlockContent::class)->createQueryBuilder('l');
        $qb->select('count(l.id)');
        $qb->where($whereString);
        $count = $qb->getQuery()->getSingleScalarResult();

        $qb = $this->getDoctrine()->getRepository(BlockContent::class)->createQueryBuilder('l');
        $qb->select();
        $qb->where($whereString);
        $qb->orderBy('l.'.$sort_column, $sort_direction);
        $qb->setMaxResults($limit);
        $qb->setFirstResult($offset);
        $blocks = $qb->getQuery()->getResult();

        $json = array(
            'total' => $count,
            'data' => $blocks,
        );

        $json = $this->serializer->serialize($json, 'json');

        return $this->json($json);
    }

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

    /**
    * @Route("/api/v1/block/insert/", name="api_block_insert", methods={"PUT"})
    * @Route("/api/v1/block/update/{id}/", name="api_block_update", methods={"PUT"})
    */
    final public function edit(int $id=0, Request $request, TranslatorInterface $translator, LogService $log)
    {
        $logMessage = '';
        $logComment = 'Insert';

        $params = json_decode(file_get_contents("php://input"),true);

        if (!empty($params['locale'])) {
            $localeId = $params['locale'];
            $locale = $this->getDoctrine()
            ->getRepository(Locale::class)
            ->find($localeId);
        }

        if (empty($locale)) {
            $locale = $this->getDoctrine()
            ->getRepository(Locale::class)
            ->getDefaultLocale();
        }

        if (!empty($id)) {

            $block = $this->getDoctrine()
            ->getRepository(BlockContent::class)
            ->find($id);

            if (!empty($block) && !empty($localeId) && $localeId != $block->getLocale()->getId()) {
                $otherBlock = $this->getDoctrine()
                ->getRepository(BlockContent::class)
                ->findOneBy(['locale' => $locale, 'block' => $block->getBlock()]);
                if (!$otherBlock) {
                    $otherBlock = new BlockContent();
                    $otherBlock->setBlock($block->getBlock());
                    $otherBlock->setLocale($locale);
                }
                $block = $otherBlock;
            }

            if (!$block) {
                $response = [
                    'success' => false,
                    'message' => 'Cannot find block',
                ];
                $json = json_encode($response);
                return $this->json($json);

            } else {

                $logMessage .= '<i>Old data:</i><br>';
                $logMessage .= $this->serializer->serialize($block, 'json');
                $logMessage .= '<br><br>';
                $logComment = 'Update';
                $message = 'Block has been updated';

            }
        }

        if ($request->isMethod('PUT')) {

            if (empty($block)) {
                $block = new BlockContent();
                $message = 'Block has been inserted';

                $parent = new Block();
                if (!empty($params['tag'])) $parent->setTag($params['tag']);
                else $errors[] = 'Tag cannot be empty';
                $block->setBlock($parent);
            }

            $block->setLocale($locale);
            if (!empty($params['name'])) $block->setName($params['name']);
            else $errors[] = 'Name cannot be empty';

            if (isset($params['description'])) $block->setDescription($params['description']);
            if (isset($params['content'])) $block->setContent($params['content']);

            if (!empty($errors)) {

                $response = [
                    'success' => false,
                    'message' => $errors,
                ];
                $json = json_encode($response);
                return $this->json($json);
            }

            $logMessage .= '<i>New data:</i><br>';
            $logMessage .= $this->serializer->serialize($block, 'json');

            $em = $this->getDoctrine()->getManager();
            $em->persist($block);
            $em->flush();
            $id = $block->getId();

            $log->add('Block', $id, $logMessage, $logComment);

            $response = [
                'success' => true,
                'id' => $id,
                'message' => $message,
            ];
        }

        $json = json_encode($response);
        return $this->json($json);
    }

    /**
    * @Route("/api/v1/block/delete/", name="api_block_delete", methods={"PUT"})
    * @Route("/api/v1/block/delete/{id}/", name="api_block_delete_multiple", methods={"DELETE"})
    */
    final public function delete(int $id=0, LogService $log)
    {
        $params = json_decode(file_get_contents("php://input"),true);
        if (!empty($params['ids'])) $toRemove = $params['ids'];
        elseif (!empty($id)) $toRemove = array($id);

        if (!empty($toRemove)) {
            foreach($toRemove as $blockId) {

                $em = $this->getDoctrine()->getManager();
                $block = $em->getRepository(BlockContent::class)->find($blockId);

                if ($block) {
                    $logMessage = '<i>Data:</i><br>';
                    $logMessage .= $this->serializer->serialize($block, 'json');

                    $log->add('Block', $id, $logMessage, 'Delete');

                    $block = $block->getBlock();

                    $em->remove($block);
                    $em->flush();
                }
            }

            $response = ['success' => true];

        } else {
            $response = [
                'success' => false,
                'message' => 'Id cannot be empty',
            ];
        }

        $json = json_encode($response);
        return $this->json($json);
    }
}
