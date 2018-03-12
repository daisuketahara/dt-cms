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
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

use App\Entity\User;
use App\Entity\UserInformation;
use App\Entity\UserNote;
use App\Entity\Permission;
use App\Entity\UserPermission;
use App\Service\LogService;


class UserController extends Controller
{
    /**
     * @Route("/{_locale}/admin/user", name="user"))
     */
     final public function list(TranslatorInterface $translator)
     {
         return $this->render('user/admin/list.html.twig', array(
             'page_title' => $translator->trans('Users'),
             'can_add' => true,
             'can_edit' => true,
             'can_delete' => true,
         ));
     }

     /**
      * @Route("/{_locale}/admin/user/ajaxlist", name="user_ajaxlist"))
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

         $qb = $this->getDoctrine()->getRepository(User::class)->createQueryBuilder('u');
         $qb->select('count(u.id)');
         $qb->where($whereString);
         $count = $qb->getQuery()->getSingleScalarResult();

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
      * @Route("/{_locale}/admin/user/add", name="user_add"))
      * @Route("/{_locale}/admin/user/edit/{id}", name="user_edit"))
      */
    final public function edit($id=0, Request $request, TranslatorInterface $translator, LogService $log, UserPasswordEncoderInterface $encoder)
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
            $userinfo = $this->getDoctrine()
                ->getRepository(UserInformation::class)
                ->findOneBy(['userId' => $id]);
            if (!$userinfo) {
                $userinfo = new UserInformation();
            } else {
                $logMessage .= $serializer->serialize($userinfo, 'json');
                $logMessage .= '<br><br>';
            }
            $usernote = $this->getDoctrine()
                ->getRepository(UserNote::class)
                ->findOneBy(['userId' => $id]);
            if (!$usernote) {
                $usernote = new UserNote();
            } else {
                $logMessage .= $serializer->serialize($usernote, 'json');
                $logMessage .= '<br><br>';
            }
        } else {
            $user = new User();
            $userinfo = new UserInformation();
            $usernote = new UserNote();
        }

        $form = $this->createFormBuilder();
        $form = $form->getForm();
        $form->handleRequest($request);

        if ($request->isMethod('POST')) {

            $user->setFirstname($request->request->get('firstname', ''));
            $user->setLastname($request->request->get('lastname', ''));
            $user->setEmail($request->request->get('email', ''));
            $user->setPhone($request->request->get('phone', ''));

            $password = $request->request->get('password', '');
            if ($password != 'passwordnotchanged') {
                $encoded = $encoder->encodePassword($user, $password);
                $user->setPassword($encoded);
            }

            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();
            $id = $user->getId();

            $logMessage .= '<i>New data:</i><br>';
            $logMessage .= $serializer->serialize($user, 'json');

            $userinfo->setUserId($id);
            $userinfo->setCompanyName($request->request->get('form_company_name', ''));
            $userinfo->setWebsite($request->request->get('form_website', ''));
            $userinfo->setVatNumber($request->request->get('form_vat_number', ''));
            $userinfo->setRegistrationNumber($request->request->get('form_registration_number', ''));
            $userinfo->setMailAddress1($request->request->get('form_mail_address_1', ''));
            $userinfo->setMailAddress2($request->request->get('form_mail_address_2', ''));
            $userinfo->setMailZipcode($request->request->get('form_mail_zipcode', ''));
            $userinfo->setMailCity($request->request->get('form_mail_city', ''));
            $userinfo->setMailCountry($request->request->get('form_mail_country', ''));
            $userinfo->setBillingAddress1($request->request->get('form_billing_address_1', ''));
            $userinfo->setBillingAddress2($request->request->get('form_billing_address_2', ''));
            $userinfo->setBillingZipcode($request->request->get('form_billing_zipcode', ''));
            $userinfo->setBillingCity($request->request->get('form_billing_city', ''));
            $userinfo->setBillingCountry($request->request->get('form_billing_country', ''));

            $logMessage .= $serializer->serialize($userinfo, 'json');

            $em = $this->getDoctrine()->getManager();
            $em->persist($userinfo);
            $em->flush();

            $usernote->setUserId($id);
            $usernote->setNote($request->request->get('form_note', ''));

            $logMessage .= $serializer->serialize($usernote, 'json');

            $em = $this->getDoctrine()->getManager();
            $em->persist($usernote);
            $em->flush();

            $setPermissions = $this->getDoctrine()
            ->getRepository(UserPermission::class)
            ->findBy(array('userId' => $id));

            foreach($setPermissions as $setPermission) {
                $em->remove($setPermission);
            }
            $em->flush();

            $formPermissions = $request->request->get('form_permission', '');

            foreach($formPermissions as $formPermissions => $permissionId) {
                $userPermission = new UserPermission();
                $userPermission->setUserId($id);
                $userPermission->setPermissionId($permissionId);
                $em->persist($userPermission);
            }
            $em->flush();

            $log->add('User', $id, $logMessage, $logComment);

            $this->addFlash(
                'success',
                $translator->trans('Your changes were saved!')
            );
            return $this->redirectToRoute('user_edit', array('id' => $id));
        }

        if (!empty($id)) $title = $translator->trans('Edit user');
        else $title = $translator->trans('Add user');

        $permissions = $this->getDoctrine()
            ->getRepository(Permission::class)
            ->getPermissions();

        $setPermissions = $this->getDoctrine()
            ->getRepository(UserPermission::class)
            ->findBy(array('userId' => $id));

        return $this->render('user/admin/edit.html.twig', array(
            'form' => $form->createView(),
            'page_title' => $title,
            'firstname' => $user->getFirstname(),
            'lastname' => $user->getLastname(),
            'email' => $user->getEmail(),
            'phone' => $user->getPhone(),
            'password' => 'passwordnotchanged',
            'email_confirmed' => $user->getEmailConfirmed(),
            'phone_confirmed' => $user->getPhoneConfirmed(),
            'company_name' => $userinfo->getCompanyName(),
            'website' => $userinfo->getWebsite(),
            'vat_number' => $userinfo->getVatNumber(),
            'registration_number' => $userinfo->getRegistrationNumber(),
            'mail_address_1' => $userinfo->getMailAddress1(),
            'mail_address_2' => $userinfo->getMailAddress2(),
            'mail_zipcode' => $userinfo->getMailZipcode(),
            'mail_city' => $userinfo->getMailCity(),
            'mail_country' => $userinfo->getMailCountry(),
            'billing_address_1' => $userinfo->getBillingAddress1(),
            'billing_address_2' => $userinfo->getBillingAddress2(),
            'billing_zipcode' => $userinfo->getBillingZipcode(),
            'billing_city' => $userinfo->getBillingCity(),
            'billing_country' => $userinfo->getBillingCountry(),
            'note' => $usernote->getNote(),
            'permissions' => $permissions,
            'permissions_set' => $setPermissions,
        ));
     }

     /**
      * @Route("/admin/user/delete/{id}", name="user_delete")
      * @Route("/{_locale}/admin/user/delete/{id}", name="user_delete"))
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
