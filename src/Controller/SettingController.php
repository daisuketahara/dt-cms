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

use App\Entity\Setting;
use App\Form\SettingForm;
use App\Service\LogService;


class SettingController extends Controller
{
    /**
     * @Route("/{_locale}/admin/setting", name="setting")
     */
     final public function list(TranslatorInterface $translator)
     {



         return $this->render('setting/admin/list.html.twig', array(
             'page_title' => $translator->trans('Settings'),
             'can_add' => true,
             'can_edit' => true,
             'can_delete' => true,
         ));
     }

     /**
      * @Route("/{_locale}/admin/setting/ajaxlist", name="setting_ajaxlist")
      */
     final public function ajaxlist(Request $request)
     {
         $sort_column = $request->request->get('sortColumn', 'id');
         $sort_direction = strtoupper($request->request->get('sortDirection', 'desc'));
         $limit = $request->request->get('limit', 15);
         $offset = $request->request->get('offset', 0);
         $filter = $request->request->get('filter', '');

         $where = array();
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

         $qb = $this->getDoctrine()->getRepository(Setting::class)->createQueryBuilder('s');
         $qb->select('count(s.id)');
         $qb->where($whereString);
         $count = $qb->getQuery()->getSingleScalarResult();

         if (empty($limit)) {
             $pages = $this->getDoctrine()
                 ->getRepository(Setting::class)
                 ->findBy($where, array($sort_column => $sort_direction));
         } else {
             $pages = $this->getDoctrine()
                 ->getRepository(Setting::class)
                 ->findBy($where, array($sort_column => $sort_direction), $limit, $offset);
         }

         $encoders = array(new XmlEncoder(), new JsonEncoder());
         $normalizers = array(new ObjectNormalizer());
         $serializer = new Serializer($normalizers, $encoders);

         $json = array(
             'total' => 6,
             'data' => $pages
         );

         $json = $serializer->serialize($json, 'json');

         return $this->json($json);
     }

     /**
      * @Route("/{_locale}/admin/setting/add", name="setting_add")
      * @Route("/{_locale}/admin/setting/edit/{id}", name="setting_edit")
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
            $setting = $this->getDoctrine()
                ->getRepository(Setting::class)
                ->find($id);
            if (!$setting) {
                $setting = new Setting();
                $this->addFlash(
                    'error',
                    $translator->trans('The requested setting does not exist!')
                );
            } else {
                $logMessage .= '<i>Old data:</i><br>';
                $logMessage .= $serializer->serialize($setting, 'json');
                $logMessage .= '<br><br>';
                $logComment = 'Update';

            }
        } else {
            $setting = new Setting();
        }

        $form = $this->createForm(SettingForm::class, $setting);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // perform some action...
            $setting = $form->getData();

            $logMessage .= '<i>New data:</i><br>';
            $logMessage .= $serializer->serialize($setting, 'json');

            $em = $this->getDoctrine()->getManager();
            $em->persist($setting);
            $em->flush();
            $id = $setting->getId();

            $log->add('Setting', $id, $logMessage, $logComment);

            $this->addFlash(
                'success',
                $translator->trans('Your changes were saved!')
            );
            return $this->redirectToRoute('setting_edit', array('id' => $id));
        }

        if (!empty($id)) $title = $translator->trans('Edit setting');
        else $title = $translator->trans('Add setting');

        return $this->render('common/form.html.twig', array(
            'form' => $form->createView(),
            'page_title' => $title,
        ));
     }

     /**
      * @Route("/{_locale}/admin/setting/delete/{id}", name="setting_delete")
      */
     final public function delete($id, LogService $log)
     {
         $em = $this->getDoctrine()->getManager();
         $setting = $em->getRepository(Setting::class)->find($id);

         $encoders = array(new XmlEncoder(), new JsonEncoder());
         $normalizers = array(new ObjectNormalizer());
         $serializer = new Serializer($normalizers, $encoders);
         $logMessage = '<i>Data:</i><br>';
         $logMessage .= $serializer->serialize($setting, 'json');

         $log->add('Setting', $id, $logMessage, 'Delete');

         $em->remove($setting);
         $em->flush();

         return new Response(
             '1'
         );
     }
}
