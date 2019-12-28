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
use Symfony\Component\Yaml\Yaml;

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
    private $serializer;

    public function __construct() {
        $encoders = array(new XmlEncoder(), new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());
        $this->serializer = new Serializer($normalizers, $encoders);
    }

    /**
    * @Route("/api/v1/user/info/", name="api_user_info"), methods={"GET","HEAD"})
    */
    final public function fields(Request $request, TranslatorInterface $translator)
    {
        $properties = Yaml::parseFile('src/Config/User.yaml');

        $info = array(
            'api' => array(
                'list' => '/user/list/',
                'get' => '/user/get/',
                'delete' => '/user/delete/',
            ),
            'settings' => array(
                'insert' => '/user/insert/',
                'update' => '/user/update/',
            ),
            'fields' => $properties['fields'],
        );

        return $this->json($info);
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

        $json = array(
            'total' => $count,
            'data' => $users,
        );

        $json = $this->serializer->serialize($json, 'json');

        return $this->json($json);
    }

    /**
    * @Route("/api/v1/user/get/{id}/", name="api_user_get"), methods={"GET","HEAD"})
    */
    final public function getUserData(int $id, Request $request)
    {
        if (!empty($id)) {
            $user = $this->getDoctrine()
            ->getRepository(User::class)
            ->find($id);
            if ($user) {
                $user->setPassword('passwordnotchanged');
                $response = [
                    'success' => true,
                    'data' => $user,
                ];
            } else {
                $response = [
                    'success' => false,
                    'message' => 'Cannot find filegroup',
                ];
            }
        } else {
            $response = [
                'success' => false,
                'message' => 'Id cannot be empty',
            ];
        }

        $json = $this->serializer->serialize($response, 'json');
        return $this->json($json);
    }

    /**
    * @Route("/api/v1/user/insert/", name="api_user_insert", methods={"PUT"})
    * @Route("/api/v1/user/update/{id}/", name="api_user_update", methods={"PUT"})
    */
    final public function edit(int $id=0, Request $request, TranslatorInterface $translator, LogService $log, UserPasswordEncoderInterface $encoder)
    {
        $logMessage = '';
        $logComment = 'Insert';
        $errors = array();

        if (!empty($id)) {
            $user = $this->getDoctrine()
            ->getRepository(User::class)
            ->find($id);
            if (!$user) {
                $error[] = 'User does not exist';
            } else {
                $logMessage .= '<i>Old data:</i><br>';
                $logMessage .= $this->serializer->serialize($user, 'json');
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

        $params = json_decode(file_get_contents("php://input"),true);

        if (!empty($params['locale']['id'])) {
            $localeId = $params['locale']['id'];
            $locale = $this->getDoctrine()
                ->getRepository(Locale::class)
                ->find($localeId);
        }

        if (!$locale) {
            $locale = $this->getDoctrine()
                ->getRepository(Locale::class)
                ->getDefaultLocale();
        }

        $user->setLocale($locale);

        if (!empty($params['email'])) $user->setEmail($params['email']);
        else $errors[] = 'Email cannot be empty';

        if (isset($params['firstname'])) $user->setFirstname($params['firstname']);
        if (isset($params['lastname'])) $user->setLastname($params['lastname']);
        if (isset($params['phone'])) $user->setPhone($params['phone']);
        if (isset($params['emailConfirmed'])) $user->setEmailConfirmed($params['emailConfirmed']);
        if (isset($params['phoneConfirmed'])) $user->setPhoneConfirmed($params['phoneConfirmed']);
        if (isset($params['active'])) $user->setActive($params['active']);

        if (empty($user->getId())) $user->setCreationDate(new \DateTime());

        if (!empty($params['password'])) {
            $password = $params['password'];
            if ($password != 'passwordnotchanged') {
                $encoded = $encoder->encodePassword($user, $password);
                $user->setPassword($encoded);
            }
        }

        if (empty($user->getPassword())) {
            $errors[] = 'Password cannot be empty';
        }

        $logMessage .= '<i>New data:</i><br>';
        $logMessage .= $this->serializer->serialize($user, 'json');

        if (isset($params['information']['companyName'])) $userinfo->setCompanyName($params['information']['companyName']);
        if (isset($params['information']['website'])) $userinfo->setWebsite($params['information']['website']);
        if (isset($params['information']['vatNumber'])) $userinfo->setVatNumber($params['information']['vatNumber']);
        if (isset($params['information']['registrationNumber'])) $userinfo->setRegistrationNumber($params['information']['registrationNumber']);
        if (isset($params['information']['mailAddress1'])) $userinfo->setMailAddress1($params['information']['mailAddress1']);
        if (isset($params['information']['mailAddress2'])) $userinfo->setMailAddress2($params['information']['mailAddress2']);
        if (isset($params['information']['mailZipcode'])) $userinfo->setMailZipcode($params['information']['mailZipcode']);
        if (isset($params['information']['mailCity'])) $userinfo->setMailCity($params['information']['mailCity']);
        if (isset($params['information']['mailCountry'])) $userinfo->setMailCountry($params['information']['mailCountry']);
        if (isset($params['information']['billingAddress1'])) $userinfo->setBillingAddress1($params['information']['billingAddress1']);
        if (isset($params['information']['billingAddress2'])) $userinfo->setBillingAddress2($params['information']['billingAddress2']);
        if (isset($params['information']['billingZipcode'])) $userinfo->setBillingZipcode($params['information']['billingZipcode']);
        if (isset($params['information']['billingCity'])) $userinfo->setBillingCity($params['information']['billingCity']);
        if (isset($params['information']['billingCountry'])) $userinfo->setBillingCountry($params['information']['billingCountry']);

        $user->setInformation($userinfo);
        $logMessage .= $this->serializer->serialize($userinfo, 'json');

        if (isset($params['note']['note'])) $usernote->setNote($params['note']['note']);
        $user->setNote($usernote);
        $logMessage .= $this->serializer->serialize($usernote, 'json');

        $userRoles = $user->getUserRoles();
        if (!empty($userRoles)) {
            foreach($userRoles as $userRole) {
                $user->removeRole($userRole);
            }
        }

        if (isset($params['roles'])) $formRoles = $params['roles'];
        if (!empty($formRoles))
        foreach($formRoles as $formRoleId => $formRoleStatus) {
            if (!empty($formRoleId) && $formRoleStatus != false) {
                $role = $this->getDoctrine()
                ->getRepository(Role::class)
                ->find($formRoleId);
                if ($role) $user->addRole($role);
            }
        }

        $userPermissions = $user->getPermissions();
        if (!empty($userPermissions)) {
            foreach($userPermissions as $userPermission) {
                $user->removePermission($userPermission);
            }
        }

        if (isset($params['permissions'])) $formPermissions = $params['permissions'];
        if (!empty($formPermissions))
        foreach($formPermissions as $formPermissionId => $formPermissionStatus) {
            if (!empty($formPermissionId) && $formPermissionStatus != false) {
                $permission = $this->getDoctrine()
                ->getRepository(Permission::class)
                ->find($formPermissionId);
                if ($permission) $user->addPermission($permission);
            }
        }

        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush();
        $id = $user->getId();

        $log->add('User', $id, $logMessage, $logComment);

        $response = array(
            'success' => true,
            'id' => $id
        );
        $json = json_encode($response);
        return $this->json($json);
    }

    /**
    * @Route("/api/v1/user/delete/", name="api_user_delete", methods={"PUT"})
    * @Route("/api/v1/user/delete/{id}/", name="api_user_delete_multiple", methods={"DELETE"})
    */
    final public function delete(int $id=0, LogService $log)
    {
        $params = json_decode(file_get_contents("php://input"),true);
        if (!empty($params['ids'])) $toRemove = $params['ids'];
        elseif (!empty($id)) $toRemove = array($id);

        if (!empty($toRemove)) {
            foreach($toRemove as $userId) {

                $em = $this->getDoctrine()->getManager();
                $user = $em->getRepository(User::class)->find($userId);

                if ($user) {
                    $logMessage = '<i>Data:</i><br>';
                    $logMessage .= $this->serializer->serialize($user, 'json');

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
