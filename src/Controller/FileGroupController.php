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

use App\Entity\FileGroup;
use App\Form\FileGroupForm;
use App\Service\LogService;

class FileGroupController extends Controller
{
    /**
     * @Route("/{_locale}/admin/filegroup/", name="filegroup"))
     */
     final public function list(TranslatorInterface $translator)
     {
         return $this->render('file_group/admin/list.html.twig', array(
             'page_title' => $translator->trans('Filegroups'),
         ));
     }

     /**
      * @Route("/{_locale}/admin/filegroup/get/", name="filegroup_get"))
      */
     final public function getFileGroup(Request $request)
     {
         $sort_column = $request->request->get('sortColumn', 'id');
         $sort_direction = strtoupper($request->request->get('sortDirection', 'asc'));
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

         $qb = $this->getDoctrine()->getRepository(FileGroup::class)->createQueryBuilder('l');
         $qb->select('count(l.id)');
         $qb->where($whereString);
         $count = $qb->getQuery()->getSingleScalarResult();

         if (empty($limit)) {
             $filegroups = $this->getDoctrine()
                 ->getRepository(FileGroup::class)
                 ->findBy($where, array($sort_column => $sort_direction));
         } else {
             $filegroups = $this->getDoctrine()
                 ->getRepository(FileGroup::class)
                 ->findBy($where, array($sort_column => $sort_direction), $limit, $offset);
         }

         $encoders = array(new XmlEncoder(), new JsonEncoder());
         $normalizers = array(new ObjectNormalizer());
         $serializer = new Serializer($normalizers, $encoders);

         $json = array(
             'total' => 6,
             'data' => $filegroups
         );

         $json = $serializer->serialize($json, 'json');

         return $this->json($json);
     }

     /**
      * @Route("/{_locale}/admin/filegroup/add/", name="filegroup_add"))
      * @Route("/{_locale}/admin/filegroup/edit/{id}/", name="filegroup_edit"))
      */
    final public function edit($id=0, Request $request, TranslatorInterface $translator, LogService $log)
    {
        $encoders = array(new XmlEncoder(), new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());
        $serializer = new Serializer($normalizers, $encoders);
        $logMessage = '';
        $logComment = 'Insert';

        if (!empty($id)) {
            $filegroup = $this->getDoctrine()
                ->getRepository(FileGroup::class)
                ->find($id);
            if (!$filegroup) {
                $filegroup = new FileGroup();
                $this->addFlash(
                    'error',
                    $translator->trans('The requested filegroup does not exist!')
                );
            } else {
                $logMessage .= '<i>Old data:</i><br>';
                $logMessage .= $serializer->serialize($filegroup, 'json');
                $logMessage .= '<br><br>';
                $logComment = 'Update';

            }
        } else {
            $filegroup = new FileGroup();
        }

        $form = $this->createForm(FileGroupForm::class, $filegroup);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // perform some action...
            $filegroup = $form->getData();

            $logMessage .= '<i>New data:</i><br>';
            $logMessage .= $serializer->serialize($filegroup, 'json');

            $em = $this->getDoctrine()->getManager();
            $em->persist($filegroup);
            $em->flush();
            $id = $filegroup->getId();

            $log->add('Filegroup', $id, $logMessage, $logComment);

            $this->addFlash(
                'success',
                $translator->trans('Your changes were saved!')
            );
            return $this->redirectToRoute('filegroup_edit', array('id' => $id));
        }

        if (!empty($id)) $title = $translator->trans('Edit filegroup');
        else $title = $translator->trans('Add filegroup');

        return $this->render('common/form.html.twig', array(
            'form' => $form->createView(),
            'page_title' => $title,
        ));
     }

     /**
      * @Route("/{_locale}/admin/filegroup/delete/{id}/", name="filegroup_delete"))
      */
     final public function delete($id, LogService $log)
     {
         $em = $this->getDoctrine()->getManager();
         $filegroup = $em->getRepository(FileGroup::class)->find($id);

         $encoders = array(new XmlEncoder(), new JsonEncoder());
         $normalizers = array(new ObjectNormalizer());
         $serializer = new Serializer($normalizers, $encoders);
         $logMessage = '<i>Data:</i><br>';
         $logMessage .= $serializer->serialize($filegroup, 'json');

         $log->add('Filegroup', $id, $logMessage, 'Delete');

         $em->remove($filegroup);
         $em->flush();

         return new Response(
             '1'
         );
     }
}
