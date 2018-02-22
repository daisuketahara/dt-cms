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

use App\Entity\User;
use App\Form\UserForm;
use App\Service\LogService;


class UserController extends Controller
{
    /**
     * @Route("/{_locale}/admin/user", name="user")
     */
     final public function list(TranslatorInterface $translator, LogService $log)
     {
         return $this->render('user/admin/list.html.twig', array(
             'page_title' => $translator->trans('Users'),
             'can_add' => true,
             'can_edit' => true,
             'can_delete' => true,
         ));
     }

     /**
      * @Route("/{_locale}/admin/user/ajaxlist", name="user_ajaxlist")
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
             $users = $this->getDoctrine()
                 ->getRepository(User::class)
                 ->findBy($where, array($sort_column => $sort_direction));
         } else {
             $users = $this->getDoctrine()
                 ->getRepository(User::class)
                 ->findBy($where, array($sort_column => $sort_direction), $limit, $offset);
         }

         $encoders = array(new XmlEncoder(), new JsonEncoder());
         $normalizers = array(new ObjectNormalizer());
         $serializer = new Serializer($normalizers, $encoders);

         $json = array(
             'total' => 6,
             'data' => $users
         );

         $json = $serializer->serialize($json, 'json');

         return $this->json($json);
     }

     /**
      * @Route("/{_locale}/admin/user/add", name="user_add")
      * @Route("/{_locale}/admin/user/edit/{id}", name="user_edit")
      */
    final public function edit($id=0, Request $request, TranslatorInterface $translator, LogService $log)
    {
        $encoders = array(new XmlEncoder(), new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());
        $serializer = new Serializer($normalizers, $encoders);
        $logMessage = '';
        $logComment = 'Insert';

        if (!empty($id)) {
            $user = $this->getDoctrine()
                ->getRepository(User::class)
                ->find($id);
            if (!$user) {
                $user = new User();
                $this->addFlash(
                    'error',
                    $translator->trans('The requested user does not exist!')
                );
            } else {
                $logMessage .= '<i>Old data:</i><br>';
                $logMessage .= $serializer->serialize($user, 'json');
                $logMessage .= '<br><br>';
                $logComment = 'Update';

            }
        } else {
            $user = new User();
        }

        $form = $this->createForm(UserForm::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // perform some action...
            $user = $form->getData();

            $logMessage .= '<i>New data:</i><br>';
            $logMessage .= $serializer->serialize($user, 'json');

            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();
            $id = $user->getId();

            $log->add('User', $id, $logMessage, $logComment);

            $this->addFlash(
                'success',
                $translator->trans('Your changes were saved!')
            );
            return $this->redirectToRoute('user_edit', array('id' => $id));
        }

        if (!empty($id)) $title = $translator->trans('Edit user');
        else $title = $translator->trans('Add user');

        return $this->render('common/form.html.twig', array(
            'form' => $form->createView(),
            'page_title' => $title,
        ));
     }

     /**
      * @Route("/{_locale}/admin/user/delete/{id}", name="user_delete")
      */
     final public function delete($id, LogService $log)
     {
         $em = $this->getDoctrine()->getManager();
         $user = $em->getRepository(User::class)->find($id);

         $encoders = array(new XmlEncoder(), new JsonEncoder());
         $normalizers = array(new ObjectNormalizer());
         $serializer = new Serializer($normalizers, $encoders);
         $logMessage = '<i>Data:</i><br>';
         $logMessage .= $serializer->serialize($user, 'json');

         $log->add('User', $id, $logMessage, 'Delete');

         $em->remove($user);
         $em->flush();

         return new Response(
             '1'
         );
     }
}
