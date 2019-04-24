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
    * @Route("/{_locale}/admin/user/", name="admin_user_list"))
    */
    final public function list(TranslatorInterface $translator)
    {
        return $this->render('user/admin/list.html.twig', array(
            'apikey' => 'ce07f59f2eca96d9e3e4dbe2becce743',
            'page_title' => $translator->trans('Users'),
        ));
    }

    /**
    * @Route("/{_locale}/admin/user/insert/", name="admin_user_insert"))
    * @Route("/{_locale}/admin/user/update/{id}/", name="admin_user_update"))
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
            return $this->redirectToRoute('admin_user_update', array('id' => $id));
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

        if ($user->getLocale()) {
            $userLocaleId = $user->getLocale()->getId();
        } else {
            $defaultLocale = $this->getDoctrine()
            ->getRepository(Locale::class)
            ->getDefaultLocale();
            $userLocaleId = $defaultLocale->getId();
        }


        return $this->render('user/admin/edit.html.twig', array(
            'form' => $form->createView(),
            'page_title' => $title,
            'firstname' => $user->getFirstname(),
            'lastname' => $user->getLastname(),
            'email' => $user->getEmail(),
            'phone' => $user->getPhone(),
            'locale_id' => $userLocaleId,
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
}
