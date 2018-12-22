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
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

use App\Entity\Role;
use App\Entity\Permission;
use App\Entity\RolePermission;
use App\Form\RoleForm;
use App\Service\LogService;


class RoleController extends Controller
{
    /**
     * @Route("/{_locale}/admin/role/", name="role")
     */
     final public function list(TranslatorInterface $translator)
     {
         return $this->render('role/admin/list.html.twig', array(
             'page_title' => $translator->trans('User roles'),
             'can_add' => true,
             'can_edit' => true,
             'can_delete' => true,
         ));
     }

     /**
      * @Route("/{_locale}/admin/role/get/", name="role_get")
      */
     final public function getRole(Request $request)
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

         $qb = $this->getDoctrine()->getRepository(Role::class)->createQueryBuilder('u');
         $qb->select('count(u.id)');
         $qb->where($whereString);
         $count = $qb->getQuery()->getSingleScalarResult();

         if (empty($limit)) {
             $roles = $this->getDoctrine()
                 ->getRepository(Role::class)
                 ->findBy($where, array($sort_column => $sort_direction));
         } else {
             $roles = $this->getDoctrine()
                 ->getRepository(Role::class)
                 ->findBy($where, array($sort_column => $sort_direction), $limit, $offset);
         }

         $encoders = array(new XmlEncoder(), new JsonEncoder());
         $normalizers = array(new ObjectNormalizer());
         $serializer = new Serializer($normalizers, $encoders);

         $json = array(
             'total' => 6,
             'data' => $roles
         );

         $json = $serializer->serialize($json, 'json');

         return $this->json($json);
     }

     /**
      * @Route("/{_locale}/admin/role/add/", name="role_add")
      * @Route("/{_locale}/admin/role/edit/{id}/", name="role_edit")
      */
    final public function edit($id=0, Request $request, TranslatorInterface $translator, LogService $log)
    {
        $encoders = array(new XmlEncoder(), new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());
        $serializer = new Serializer($normalizers, $encoders);
        $logMessage = '';
        $logComment = 'Insert';

        if (!empty($id)) {
            $role = $this->getDoctrine()
                ->getRepository(Role::class)
                ->find($id);
            if (!$role) {
                $role = new Role();
                $this->addFlash(
                    'error',
                    $translator->trans('The requested role does not exist!')
                );
            } else {
                $logMessage .= '<i>Old data:</i><br>';
                $logMessage .= $serializer->serialize($role, 'json');
                $logMessage .= '<br><br>';
                $logComment = 'Update';

            }
        } else {
            $role = new Role();
        }

        $roles = $this->getDoctrine()
            ->getRepository(Role::class)
            ->findAll();

        $form = $this->createFormBuilder();
        $form = $form->getForm();
        $form->handleRequest($request);

        if ($request->isMethod('POST')) {

            $role->setName($request->request->get('form_name', ''));
            $role->setDescription($request->request->get('form_description', ''));
            $role->setActive($request->request->get('form_active', false));

            $logMessage .= '<i>New data:</i><br>';
            $logMessage .= $serializer->serialize($role, 'json');

            $em = $this->getDoctrine()->getManager();
            $em->persist($role);
            $em->flush();
            $id = $role->getId();

            $setPermissions = $role->getPermissions();

            if ($setPermissions)
            foreach($setPermissions as $setPermission) {
                $role->removePermission($setPermission);
            }
            $em->persist($role);
            $em->flush();

            $formPermissions = $request->request->get('form_permission', '');

            if ($formPermissions)
            foreach($formPermissions as $permissionId) {
                $permission = $this->getDoctrine()
                ->getRepository(Permission::class)
                ->find($permissionId);
                if ($permission) $role->addPermission($permission);
            }
            $em->persist($role);
            $em->flush();

            $log->add('Role', $id, $logMessage, $logComment);

            $this->addFlash(
                'success',
                $translator->trans('Your changes were saved!')
            );
            return $this->redirectToRoute('role_edit', array('id' => $id));
        }

        if (!empty($id)) $title = $translator->trans('Edit role');
        else $title = $translator->trans('Add role');

        $permissions = $this->getDoctrine()
            ->getRepository(Permission::class)
            ->getPermissions();

        return $this->render('role/admin/edit.html.twig', array(
            'form' => $form->createView(),
            'page_title' => $title,
            'name' => $role->getName(),
            'description' => $role->getDescription(),
            'active' => $role->getActive(),
            'permissions' => $permissions,
            'permissions_set' => $role->getPermissions(),
        ));
     }

     /**
      * @Route("/{_locale}/admin/role/delete/{id}/", name="role_delete")
      */
     final public function delete($id, LogService $log)
     {
         $em = $this->getDoctrine()->getManager();
         $role = $em->getRepository(Role::class)->find($id);

         $encoders = array(new XmlEncoder(), new JsonEncoder());
         $normalizers = array(new ObjectNormalizer());
         $serializer = new Serializer($normalizers, $encoders);
         $logMessage = '<i>Data:</i><br>';
         $logMessage .= $serializer->serialize($role, 'json');

         $log->add('Role', $id, $logMessage, 'Delete');

         $em->remove($role);
         $em->flush();

         return new Response(
             '1'
         );
     }
}
