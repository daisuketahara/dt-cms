<?php

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

use App\Entity\Locale;
use App\Entity\Block;
use App\Service\LogService;
use App\Service\SettingService;

class BlockController extends Controller
{

     /**
      * @Route("/{_locale}/admin/block/", name="admin_block"))
      */
      final public function list(TranslatorInterface $translator)
      {
          return $this->render('block/admin/list.html.twig', array(
              'page_title' => $translator->trans('Text blocks'),
              'can_edit' => true,
              'can_delete' => true,
          ));
      }

      /**
       * @Route("/{_locale}/admin/block/get/", name="admin_block_get"))
       */
      final public function getBlock(Request $request)
      {
          $sort_column = $request->request->get('sortColumn', 'id');
          $sort_direction = strtoupper($request->request->get('sortDirection', 'desc'));
          $limit = $request->request->get('limit', 15);
          $offset = $request->request->get('offset', 0);
          $filter = $request->request->get('filter', '');

          $locale = $this->getDoctrine()
              ->getRepository(Locale::class)
              ->findOneBy(array('locale' => $this->container->getParameter('kernel.default_locale')));

          $where = array('locale' => $locale->getId());
          $whereString = '1=1';
          $filter = explode('&', $filter);
          if (!empty($filter))
          foreach($filter as $filter_item) {
              $filter_item = explode('=', $filter_item);
              if (!empty($filter_item[1])) {
                  $where[$filter_item[0]] = $filter_item[1];
                  $whereString .= " AND `" . $filter_item[0] . "`='" . $filter_item[1] . "'";
              }
          }

          $qb = $this->getDoctrine()->getRepository(Block::class)->createQueryBuilder('m');
          $qb->select('count(m.id)');
          $qb->where($whereString);
          $count = $qb->getQuery()->getSingleScalarResult();

          if (empty($limit)) {
              $block = $this->getDoctrine()
                  ->getRepository(Block::class)
                  ->findBy($where, array($sort_column => $sort_direction));
          } else {
              $block = $this->getDoctrine()
                  ->getRepository(Block::class)
                  ->findBy($where, array($sort_column => $sort_direction), $limit, $offset);
          }

          $encoders = array(new XmlEncoder(), new JsonEncoder());
          $normalizers = array(new ObjectNormalizer());
          $serializer = new Serializer($normalizers, $encoders);

          $json = array(
              'total' => 6,
              'data' => $block
          );

          $json = $serializer->serialize($json, 'json');

          return $this->json($json);
      }

    /**
     * @Route("/{_locale}/admin/block/edit/{id}/", name="admin_block_edit"))
     * @Route("/{_locale}/admin/block/edit/{id}/translate/{localeId}/", name="admin_block_edit_translate"))
     */
    final public function edit($id=0, $localeId=0, Request $request, TranslatorInterface $translator, LogService $log)
    {
        $encoders = array(new XmlEncoder(), new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());
        $serializer = new Serializer($normalizers, $encoders);
        $logMessage = '';
        $logComment = 'Insert';

         if (empty($localeId)) {
             $localeSlug = $this->container->getParameter('kernel.default_locale');
             $locale = $this->getDoctrine()
                 ->getRepository(Locale::class)
                 ->findOneBy(array('locale' => $localeSlug));
             if ($locale) $localeId = $locale->getId();
         }

         if (empty($locale)) {
             $locale = $this->getDoctrine()
                 ->getRepository(Locale::class)
                 ->find($localeId);
         }

         if (!empty($id)) {

             if (!$locale->getDefault()) {

                 $block = $this->getDoctrine()
                     ->getRepository(Block::class)
                     ->findOneBy(array('defaultId' => $id, 'locale' => $localeId));

                 if (!$block) $block = new Block();

                 $block->setDefaultId($id);

             } else {

                 $block = $this->getDoctrine()
                     ->getRepository(Block::class)
                     ->find($id);

                 if (!$block) {
                     $block = new Block();
                     $this->addFlash(
                         'error',
                         $translator->trans('The requested text block does not exist!')
                     );
                 }
             }
             if ($block) {
                 $logMessage .= '<i>Old data:</i><br>';
                 $logMessage .= $serializer->serialize($block, 'json');
                 $logMessage .= '<br><br>';
                 $logComment = 'Update';

             }
         } else {
             $block = new Block();
         }

         $form = $this->createFormBuilder();
         $form = $form->getForm();
         $form->handleRequest($request);

         if ($request->isMethod('POST')) {

            if (!$locale->getDefault() && empty($id)) {

                $localeSlugDefault = $this->container->getParameter('kernel.default_locale');
                $localeDefault = $this->getDoctrine()
                    ->getRepository(Locale::class)
                    ->findOneBy(array('locale' => $localeSlugDefault));
                $blockDefault = new Block();

                $blockDefault->setContent($request->request->get('block-content', ''));

                $em = $this->getDoctrine()->getManager();
                $em->persist($blockDefault);
                $em->flush();
                $id = $pageDefault->getId();
                $block->setDefaultId($id);
            }
            $block->setContent($request->request->get('block-content', ''));

            $logMessage .= '<i>New data:</i><br>';
            $logMessage .= $serializer->serialize($block, 'json');

            $em = $this->getDoctrine()->getManager();
            $em->persist($block);
            $em->flush();

            $log->add('Text block', $id, $logMessage, $logComment);

             $this->addFlash(
                 'success',
                 $translator->trans('Your changes were saved!')
             );

             if (!empty($localeId)) return $this->redirectToRoute('admin_block_edit_translate', array('id' => $id, 'localeId' => $localeId));
             else return $this->redirectToRoute('admin_block_edit', array('id' => $id));
         }

         $title = $translator->trans('Edit text block');

         $locales = $this->getDoctrine()
             ->getRepository(Locale::class)
             ->findAll();

         return $this->render('block/admin/edit.html.twig', array(
             'page_title' => $title,
             'id' => $id,
             'name' => $block->getName(),
             'description' => $block->getDescription(),
             'content' => $block->getContent(),
             //'variables' => explode(',', $block->getVariables()),
             'locale' => $locale,
             'default_locale' => $this->container->getParameter('kernel.default_locale'),
             'locales' => $locales,
         ));
      }
}
