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

use App\Entity\UserRole;
use App\Form\UserRoleForm;
use App\Service\LogService;


class UserRoleController extends Controller
{
    /**
     * @Route("/{_locale}/admin/userrole", name="userrole")
     */
     final public function list(TranslatorInterface $translator)
     {
         return $this->render('userrole/admin/list.html.twig', array(
             'page_title' => $translator->trans('User roles'),
             'can_add' => true,
             'can_edit' => true,
             'can_delete' => true,
         ));
     }

     /**
      * @Route("/{_locale}/admin/userrole/ajaxlist", name="userrole_ajaxlist")
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

         $qb = $this->getDoctrine()->getRepository(UserRole::class)->createQueryBuilder('u');
         $qb->select('count(u.id)');
         $qb->where($whereString);
         $count = $qb->getQuery()->getSingleScalarResult();

         if (empty($limit)) {
             $userRoles = $this->getDoctrine()
                 ->getRepository(UserRole::class)
                 ->findBy($where, array($sort_column => $sort_direction));
         } else {
             $userRoles = $this->getDoctrine()
                 ->getRepository(UserRole::class)
                 ->findBy($where, array($sort_column => $sort_direction), $limit, $offset);
         }

         $encoders = array(new XmlEncoder(), new JsonEncoder());
         $normalizers = array(new ObjectNormalizer());
         $serializer = new Serializer($normalizers, $encoders);

         $json = array(
             'total' => 6,
             'data' => $userRoles
         );

         $json = $serializer->serialize($json, 'json');

         return $this->json($json);
     }

     /**
      * @Route("/{_locale}/admin/userrole/add", name="userrole_add")
      * @Route("/{_locale}/admin/userrole/edit/{id}", name="userrole_edit")
      */
    final public function edit($id=0, Request $request, TranslatorInterface $translator, LogService $log)
    {
        $encoders = array(new XmlEncoder(), new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());
        $serializer = new Serializer($normalizers, $encoders);
        $logMessage = '';
        $logComment = 'Insert';

        if (!empty($id)) {
            $userRole = $this->getDoctrine()
                ->getRepository(UserRole::class)
                ->find($id);
            if (!$userRole) {
                $userRole = new UserRole();
                $this->addFlash(
                    'error',
                    $translator->trans('The requested userrole does not exist!')
                );
            } else {
                $logMessage .= '<i>Old data:</i><br>';
                $logMessage .= $serializer->serialize($userRole, 'json');
                $logMessage .= '<br><br>';
                $logComment = 'Update';

            }
        } else {
            $userRole = new UserRole();
        }

        $roles = $this->getDoctrine()
            ->getRepository(UserRole::class)
            ->findAll();

        $form = $this->createFormBuilder();
        $form->add('name', TextType::class, array('label' => 'Name'));
        $form->add('description', TextType::class, array('label' => 'Description'));
        $form->add('inherit', ChoiceType::class, array('label' => 'Inherit', 'multiple' => true, 'choices' => $roles));
        $form->add('active', CheckboxType::class, array('label' => 'Active'));
        $form->add('save', SubmitType::class, array('label' => 'Save'));
        $form = $form->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $userRole = $form->getData();

            $logMessage .= '<i>New data:</i><br>';
            $logMessage .= $serializer->serialize($userRole, 'json');

            $em = $this->getDoctrine()->getManager();
            $em->persist($userRole);
            $em->flush();
            $id = $user->getId();

            $log->add('Userrole', $id, $logMessage, $logComment);

            $this->addFlash(
                'success',
                $translator->trans('Your changes were saved!')
            );
            return $this->redirectToRoute('userrole_edit', array('id' => $id));
        }

        if (!empty($id)) $title = $translator->trans('Edit userrole');
        else $title = $translator->trans('Add userrole');

        return $this->render('common/form.html.twig', array(
            'form' => $form->createView(),
            'page_title' => $title,
        ));
     }

     /**
      * @Route("/{_locale}/admin/userrole/delete/{id}", name="userrole_delete")
      */
     final public function delete($id, LogService $log)
     {
         $em = $this->getDoctrine()->getManager();
         $userRole = $em->getRepository(UserRole::class)->find($id);

         $encoders = array(new XmlEncoder(), new JsonEncoder());
         $normalizers = array(new ObjectNormalizer());
         $serializer = new Serializer($normalizers, $encoders);
         $logMessage = '<i>Data:</i><br>';
         $logMessage .= $serializer->serialize($userRole, 'json');

         $log->add('Userrole', $id, $logMessage, 'Delete');

         $em->remove($userRole);
         $em->flush();

         return new Response(
             '1'
         );
     }
}
