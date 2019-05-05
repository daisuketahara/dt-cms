<?php

namespace App\Controller\Api;

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

use App\Entity\User;
use App\Entity\UserInformation;
use App\Entity\UserNote;
use App\Entity\Locale;
use App\Entity\Role;
use App\Entity\Permission;
use App\Entity\UserRole;
use App\Entity\UserPermission;
use App\Entity\UserApiKey;
use App\Service\LogService;


class UserController extends Controller
{
    /**
    * @Route("/api/v1/user/info/", name="api_user_info"), methods={"GET","HEAD"})
    */
    final public function fields(Request $request, TranslatorInterface $translator)
    {
        $info = array(
            'api' => array(
                'list' => '/user/list/',
                'get' => '/user/get/',
                'delete' => '/user/delete/',
            ),
            'fields' => array(
                [
                    'id' => 'id',
                    'label' => 'id',
                    'type' => 'text',
                    'required' => true,
                    'editable' => false,
                    'show_list' => true,
                    'show_form' => false,
                ],[
                    'id' => 'email',
                    'label' => 'email',
                    'type' => 'email',
                    'required' => true,
                    'editable' => true,
                    'show_list' => true,
                    'show_form' => true,
                ],[
                    'id' => 'firstname',
                    'label' => 'firstname',
                    'type' => 'text',
                    'required' => false,
                    'editable' => true,
                    'show_list' => true,
                    'show_form' => true,
                ],[
                    'id' => 'lastname',
                    'label' => 'lastname',
                    'type' => 'text',
                    'required' => false,
                    'editable' => true,
                    'show_list' => true,
                    'show_form' => true,
                ],[
                    'id' => 'active',
                    'label' => 'active',
                    'type' => 'checkbox',
                    'required' => false,
                    'editable' => true,
                    'show_list' => true,
                    'show_form' => true,
                ]
            ),
        );

        return $this->json(json_encode($info));
    }

    /**
    * @Route("/api/v1/user/list/", name="api_user_list"), methods={"GET","HEAD"})
    */
    final public function list(Request $request)
    {
        $params = json_decode(file_get_contents("php://input"),true);
        if (!empty($params['sort'])) $sort_column = $params['sort']; else $sort_column = 'id';
        if (!empty($params['dir'])) $sort_direction = $params['dir']; else $sort_direction = 'desc';
        if (!empty($params['limit'])) $limit = $params['limit']; else $limit = 15;
        if (!empty($params['offset'])) $offset = $params['offset']; else $offset = 0;
        if (!empty($params['filter'])) $filter = $params['filter'];

        $whereString = '1=1';
        if (!empty($filter)) {
            parse_str($filter, $filter_array);
            foreach($filter_array as $key => $value) {
                if (!empty($value)) {
                    //$where[$key] = $value;
                    $whereString .= " AND l." . $key . " LIKE '%" . $value . "%'";
                }
            }
        }

        $qb = $this->getDoctrine()->getRepository(User::class)->createQueryBuilder('l');
        $qb->select('count(l.id)');
        $qb->where($whereString);
        $count = $qb->getQuery()->getSingleScalarResult();

        $qb = $this->getDoctrine()->getRepository(User::class)->createQueryBuilder('l');
        $qb->select();
        $qb->where($whereString);
        $qb->orderBy('l.'.$sort_column, $sort_direction);
        $qb->setMaxResults($limit);
        $qb->setFirstResult($offset);
        $users = $qb->getQuery()->getResult();

        $encoders = array(new XmlEncoder(), new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());
        $serializer = new Serializer($normalizers, $encoders);

        $json = array(
            'total' => $count,
            'data' => $users,
        );

        $json = $serializer->serialize($json, 'json');

        return $this->json($json);
    }

    /**
    * @Route("/api/v1/user/insert/", name="api_user_insert", methods={"PUT"})
    * @Route("/api/v1/user/update/{id}/", name="api_user_update", methods={"PUT"})
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
                //s$logMessage .= $serializer->serialize($user, 'json');
                $logMessage .= '<br><br>';
                $logComment = 'Update';

            }
            $userinfo = $user->getInformation();
            if (!$userinfo) $userinfo = new UserInformation();

            $usernote = $user->getNote();
            if (!$usernote) $usernote = new UserNote();

        } else {
            $user = new User();
            $userinfo = new UserInformation();
            $usernote = new UserNote();
        }

        $form = $this->createFormBuilder();
        $form = $form->getForm();
        $form->handleRequest($request);

        if ($request->isMethod('POST')) {


            $localeId = $request->request->get('user-locale', '');
            $locale = $this->getDoctrine()
            ->getRepository(Locale::class)
            ->find($localeId);

            $user->setFirstname($request->request->get('firstname', ''));
            $user->setLastname($request->request->get('lastname', ''));
            $user->setEmail($request->request->get('email', ''));
            $user->setPhone($request->request->get('phone', ''));
            $user->setLocale($locale);
            $user->setEmailConfirmed($request->request->get('emailConfirmed', false));
            $user->setPhoneConfirmed($request->request->get('phoneConfirmed', false));
            $user->setActive($request->request->get('active', false));

            if (empty($user->getId())) {
                $user->setCreationDate(new \DateTime());
            }

            $password = $request->request->get('password', '');
            if ($password != 'passwordnotchanged') {
                $encoded = $encoder->encodePassword($user, $password);
                $user->setPassword($encoded);
            }

            $logMessage .= '<i>New data:</i><br>';
            //$logMessage .= $serializer->serialize($user, 'json');

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
            $user->setInformation($userinfo);
            $logMessage .= $serializer->serialize($userinfo, 'json');

            $usernote->setNote($request->request->get('form_note', ''));
            $user->setNote($usernote);
            $logMessage .= $serializer->serialize($usernote, 'json');

            $formPermissions = $request->request->get('form_permission', '');
            if (!empty($formPermissions))
            foreach($formPermissions as $formPermission => $permissionId) {
                $permission = $this->getDoctrine()
                ->getRepository(Permission::class)
                ->find($permissionId);
                $user->addPermission($permission);
            }
            //$user->setPermissions($userPermissions);

            $formRoles = $request->request->get('form_role', '');
            if (!empty($formRoles))
            foreach($formRoles as $formRole => $roleId) {
                $role = $this->getDoctrine()
                ->getRepository(Role::class)
                ->find($roleId);
                $user->addRole($role);
            }

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

        $roles = $this->getDoctrine()
        ->getRepository(Role::class)
        ->findAll();

        $locales = $this->getDoctrine()
        ->getRepository(Locale::class)
        ->findAll();

        $permissions = $this->getDoctrine()
        ->getRepository(Permission::class)
        ->getPermissions();

        $apiKeys = $this->getDoctrine()
        ->getRepository(UserApiKey::class)
        ->findBy(array('user' => $user));

        return $this->render('user/admin/edit.html.twig', array(
            'form' => $form->createView(),
            'page_title' => $title,
            'firstname' => $user->getFirstname(),
            'lastname' => $user->getLastname(),
            'email' => $user->getEmail(),
            'phone' => $user->getPhone(),
            'locale_id' => $user->getLocale()->getId(),
            'locales' => $locales,
            'password' => 'passwordnotchanged',
            'email_confirmed' => $user->getEmailConfirmed(),
            'phone_confirmed' => $user->getPhoneConfirmed(),
            'active' => $user->getActive(),
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
            'roles' => $roles,
            'roles_set' => $user->getUserRoles(),
            'permissions' => $permissions,
            'permissions_set' => $user->getPermissions(),
            'api_keys' => $apiKeys,
        ));
    }

    /**
    * @Route("/api/v1/user/delete/", name="api_user_delete", methods={"PUT"})
    * @Route("/api/v1/user/delete/{id}/", name="api_user_delete_multiple", methods={"DELETE"})
    */
    final public function delete($id=0, LogService $log)
    {
        $encoders = array(new XmlEncoder(), new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());
        $serializer = new Serializer($normalizers, $encoders);

        $params = json_decode(file_get_contents("php://input"),true);
        if (!empty($params['ids'])) $toRemove = $params['ids'];
        elseif (!empty($id)) $toRemove = array($id);

        if (!empty($toRemove)) {
            foreach($toRemove as $userId) {

                $em = $this->getDoctrine()->getManager();
                $user = $em->getRepository(User::class)->find($userId);

                if ($user) {
                    $logMessage = '<i>Data:</i><br>';
                    $logMessage .= $serializer->serialize($user, 'json');

                    $log->add('User', $id, $logMessage, 'Delete');

                    $em->remove($user);
                    $em->flush();
                }
            }

            $response = ['success' => true];

        } else {
            $response = [
                'success' => false,
                'message' => 'Id cannot be empty',
            ];
        }

        $json = json_encode($response);
        return $this->json($json);
    }
}
