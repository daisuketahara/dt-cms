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

use App\Entity\Permission;
use App\Form\PermissionForm;
use App\Service\LogService;


class PermissionController extends Controller
{
    /**
     * @Route("/{_locale}/admin/permission", name="permission")
     */
     final public function list(TranslatorInterface $translator)
     {
         return $this->render('permission/admin/list.html.twig', array(
             'page_title' => $translator->trans('Permissions'),
             'can_add' => true,
             'can_edit' => true,
             'can_delete' => true,
         ));
     }

     /**
      * @Route("/{_locale}/admin/permission/ajaxlist", name="permissionajaxlist")
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

         $qb = $this->getDoctrine()->getRepository(Permission::class)->createQueryBuilder('s');
         $qb->select('count(s.id)');
         $qb->where($whereString);
         $count = $qb->getQuery()->getSingleScalarResult();

         if (empty($limit)) {
             $permissions = $this->getDoctrine()
                 ->getRepository(Permission::class)
                 ->findBy($where, array($sort_column => $sort_direction));
         } else {
             $permissions = $this->getDoctrine()
                 ->getRepository(Permission::class)
                 ->findBy($where, array($sort_column => $sort_direction), $limit, $offset);
         }

         $encoders = array(new XmlEncoder(), new JsonEncoder());
         $normalizers = array(new ObjectNormalizer());
         $serializer = new Serializer($normalizers, $encoders);

         $json = array(
             'total' => 6,
             'data' => $permissions
         );

         $json = $serializer->serialize($json, 'json');

         return $this->json($json);
     }

     /**
      * @Route("/{_locale}/admin/permission/add", name="permission_add")
      * @Route("/{_locale}/admin/permission/edit/{id}", name="permission_edit")
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
            $permission = $this->getDoctrine()
                ->getRepository(Permission::class)
                ->find($id);
            if (!$permission) {
                $permission = new Permission();
                $this->addFlash(
                    'error',
                    $translator->trans('The requested permission does not exist!')
                );
            } else {
                $logMessage .= '<i>Old data:</i><br>';
                $logMessage .= $serializer->serialize($permission, 'json');
                $logMessage .= '<br><br>';
                $logComment = 'Update';

            }
        } else {
            $permission = new Permission();
        }

        $form = $this->createForm(PermissionForm::class, $permission);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // perform some action...
            $permission = $form->getData();

            $logMessage .= '<i>New data:</i><br>';
            $logMessage .= $serializer->serialize($permission, 'json');

            $em = $this->getDoctrine()->getManager();
            $em->persist($permission);
            $em->flush();
            $id = $permission->getId();

            $log->add('Permission', $id, $logMessage, $logComment);

            $this->addFlash(
                'success',
                $translator->trans('Your changes were saved!')
            );
            return $this->redirectToRoute('permission_edit', array('id' => $id));
        }

        if (!empty($id)) $title = $translator->trans('Edit permission');
        else $title = $translator->trans('Add permission');

        return $this->render('common/form.html.twig', array(
            'form' => $form->createView(),
            'page_title' => $title,
        ));
     }

     /**
      * @Route("/{_locale}/admin/permission/delete/{id}", name="permission_delete")
      */
     final public function delete($id, LogService $log)
     {
         $em = $this->getDoctrine()->getManager();
         $permission = $em->getRepository(Permission::class)->find($id);

         $encoders = array(new XmlEncoder(), new JsonEncoder());
         $normalizers = array(new ObjectNormalizer());
         $serializer = new Serializer($normalizers, $encoders);
         $logMessage = '<i>Data:</i><br>';
         $logMessage .= $serializer->serialize($permission, 'json');

         $log->add('Permission', $id, $logMessage, 'Delete');

         $em->remove($permission);
         $em->flush();

         return new Response(
             '1'
         );
     }
}
