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

use App\Entity\File;
use App\Form\FileForm;
use App\Service\LogService;


class FileController extends Controller
{
    /**
     * @Route("/{_locale}/admin/file/", name="file"))
     */
     final public function list(TranslatorInterface $translator)
     {
         return $this->render('file/admin/list.html.twig', array(
             'page_title' => $translator->trans('Files'),
             'can_add' => true,
             'can_edit' => true,
             'can_delete' => true,
         ));
     }

     /**
      * @Route("/{_locale}/admin/file/ajaxlist/", name="file_ajaxlist"))
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

         $qb = $this->getDoctrine()->getRepository(File::class)->createQueryBuilder('s');
         $qb->select('count(s.id)');
         $qb->where($whereString);
         $count = $qb->getQuery()->getSingleScalarResult();

         if (empty($limit)) {
             $files = $this->getDoctrine()
                 ->getRepository(File::class)
                 ->findBy($where, array($sort_column => $sort_direction));
         } else {
             $files = $this->getDoctrine()
                 ->getRepository(File::class)
                 ->findBy($where, array($sort_column => $sort_direction), $limit, $offset);
         }

         $encoders = array(new XmlEncoder(), new JsonEncoder());
         $normalizers = array(new ObjectNormalizer());
         $serializer = new Serializer($normalizers, $encoders);

         $json = array(
             'total' => 6,
             'data' => $files
         );

         $json = $serializer->serialize($json, 'json');

         return $this->json($json);
     }

     /**
      * @Route("/{_locale}/admin/file/add/", name="file_add"))
      * @Route("/{_locale}/admin/file/edit/{id}/", name="file_edit"))
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
            $file = $this->getDoctrine()
                ->getRepository(File::class)
                ->find($id);
            if (!$file) {
                $file = new File();
                $this->addFlash(
                    'error',
                    $translator->trans('The requested file does not exist!')
                );
            } else {
                $logMessage .= '<i>Old data:</i><br>';
                $logMessage .= $serializer->serialize($file, 'json');
                $logMessage .= '<br><br>';
                $logComment = 'Update';

            }
        } else {
            $file = new File();
        }

        $form = $this->createForm(FileForm::class, $file);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // perform some action...
            $file = $form->getData();

            $logMessage .= '<i>New data:</i><br>';
            $logMessage .= $serializer->serialize($file, 'json');

            $em = $this->getDoctrine()->getManager();
            $em->persist($file);
            $em->flush();
            $id = $file->getId();

            $log->add('File', $id, $logMessage, $logComment);

            $this->addFlash(
                'success',
                $translator->trans('Your changes were saved!')
            );
            return $this->redirectToRoute('file_edit', array('id' => $id));
        }

        if (!empty($id)) $title = $translator->trans('Edit file');
        else $title = $translator->trans('Add file');

        return $this->render('common/form.html.twig', array(
            'form' => $form->createView(),
            'page_title' => $title,
        ));
     }

     /**
      * @Route("/{_locale}/admin/file/delete/{id}/", name="file_delete"))
      */
     final public function delete($id, LogService $log)
     {
         $em = $this->getDoctrine()->getManager();
         $file = $em->getRepository(File::class)->find($id);

         $encoders = array(new XmlEncoder(), new JsonEncoder());
         $normalizers = array(new ObjectNormalizer());
         $serializer = new Serializer($normalizers, $encoders);
         $logMessage = '<i>Data:</i><br>';
         $logMessage .= $serializer->serialize($file, 'json');

         $log->add('File', $id, $logMessage, 'Delete');

         $em->remove($file);
         $em->flush();

         return new Response(
             '1'
         );
     }
}
