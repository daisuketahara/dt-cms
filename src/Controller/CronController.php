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

use App\Entity\Cron;
use App\Form\CronForm;
use App\Service\LogService;


class CronController extends Controller
{
    /**
     * @Route("/{_locale}/admin/cron", name="cron")
     */
     final public function list(TranslatorInterface $translator, LogService $log)
     {
         return $this->render('cron/admin/list.html.twig', array(
             'page_title' => $translator->trans('Crons'),
             'can_add' => true,
             'can_edit' => true,
             'can_delete' => true,
         ));
     }

     /**
      * @Route("/{_locale}/admin/cron/ajaxlist", name="cron_ajaxlist")
      */
     final public function ajaxlist(Request $request)
     {
         $sort_column = $request->request->get('sortColumn', 'id');
         $sort_direction = strtoupper($request->request->get('sortDirection', 'desc'));
         $limit = $request->request->get('limit', 15);
         $offset = $request->request->get('offset', 0);
         $filter = $request->request->get('filter', '');

         $where = array();
         $filter = explode('&', $filter);
         if (!empty($filter))
         foreach($filter as $filter_item) {
             $filter_item = explode('=', $filter_item);
             if (!empty($filter_item[1])) $where[$filter_item[0]] = $filter_item[1];
         }

         if (empty($limit)) {
             $crons = $this->getDoctrine()
                 ->getRepository(Cron::class)
                 ->findBy($where, array($sort_column => $sort_direction));
         } else {
             $crons = $this->getDoctrine()
                 ->getRepository(Cron::class)
                 ->findBy($where, array($sort_column => $sort_direction), $limit, $offset);
         }

         $encoders = array(new XmlEncoder(), new JsonEncoder());
         $normalizers = array(new ObjectNormalizer());
         $serializer = new Serializer($normalizers, $encoders);

         $json = array(
             'total' => 6,
             'data' => $crons
         );

         $json = $serializer->serialize($json, 'json');

         return $this->json($json);
     }

     /**
      * @Route("/{_locale}/admin/cron/add", name="cron_add")
      * @Route("/{_locale}/admin/cron/edit/{id}", name="cron_edit")
      */
    final public function edit($id=0, Request $request, TranslatorInterface $translator, LogService $log)
    {
        //$this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN', null, 'Unable to access this page!');

        $encoders = array(new XmlEncoder(), new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());
        $serializer = new Serializer($normalizers, $encoders);
        $logMessage = '';
        $logComment = 'Insert';

        if (!empty($id)) {
            $cron = $this->getDoctrine()
                ->getRepository(Cron::class)
                ->find($id);
            if (!$cron) {
                $cron = new Cron();
                $this->addFlash(
                    'error',
                    $translator->trans('The requested cron does not exist!')
                );
            } else {
                $logMessage .= '<i>Old data:</i><br>';
                $logMessage .= $serializer->serialize($cron, 'json');
                $logMessage .= '<br><br>';
                $logComment = 'Update';

            }
        } else {
            $cron = new Cron();
        }

        $form = $this->createForm(CronForm::class, $cron);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // perform some action...
            $cron = $form->getData();

            $logMessage .= '<i>New data:</i><br>';
            $logMessage .= $serializer->serialize($cron, 'json');

            $em = $this->getDoctrine()->getManager();
            $em->persist($cron);
            $em->flush();
            $id = $cron->getId();

            $log->add('Cron', $id, $logMessage, $logComment);

            $this->addFlash(
                'success',
                $translator->trans('Your changes were saved!')
            );
            return $this->redirectToRoute('cron_edit', array('id' => $id));
        }

        if (!empty($id)) $title = $translator->trans('Edit cron');
        else $title = $translator->trans('Add cron');

        return $this->render('common/form.html.twig', array(
            'form' => $form->createView(),
            'page_title' => $title,
        ));
     }

     /**
      * @Route("/{_locale}/admin/cron/delete/{id}", name="cron_delete")
      */
     final public function delete($id, LogService $log)
     {
         $em = $this->getDoctrine()->getManager();
         $cron = $em->getRepository(Cron::class)->find($id);

         $encoders = array(new XmlEncoder(), new JsonEncoder());
         $normalizers = array(new ObjectNormalizer());
         $serializer = new Serializer($normalizers, $encoders);
         $logMessage = '<i>Data:</i><br>';
         $logMessage .= $serializer->serialize($cron, 'json');

         $log->add('Cron', $id, $logMessage, 'Delete');

         $em->remove($cron);
         $em->flush();

         return new Response(
             '1'
         );
     }

     /**
      * @Route("/{_locale}/cron/run", name="cron_run")
      */
     final public function run($id, LogService $log)
     {

         

     }
}
