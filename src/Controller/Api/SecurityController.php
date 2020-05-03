<?php

namespace App\Controller\Api;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

use App\Entity\Locale;
use App\Entity\UserApiKey;
use App\Entity\User;
use App\Service\MailService;
use App\Service\SettingService;

class SecurityController extends AbstractController
{
    private $serializer;

    public function __construct() {
        $encoders = array(new XmlEncoder(), new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());
        $this->serializer = new Serializer($normalizers, $encoders);
    }

    /**
    * @Route("/api/v1/gettoken/", name="api_get_token"), methods={"POST"})
    */
    public function getToken(Request $request)
    {
        $params = json_decode(file_get_contents("php://input"),true);
        $error = array();

        if (!empty($params['email'])) $email = $params['email'];
        else $errors[] = 'Email cannot be empty';

        if (!empty($params['password'])) $password = $params['password'];
        else $errors[] = 'Password cannot be empty';

        if (!empty($params['callback'])) $callback = $params['callback'];
        else $callback = '';

        if (!empty($errors)) {
            $response = [
                'success' => false,
                'message' => $errors,
            ];
            $json = json_encode($response);
            return $this->json($json);
        }

        $user = $this->getDoctrine()
            ->getRepository(User::class)
            ->findOneBy(array('email' => $email));

        if ($user) {
            $hash = $user->getPassword();

            if (password_verify($password, $hash)) {

                $roles = $user->getUserRoles();
                if ($roles) {
                    $adminAccess = $user->getUserRoles()[0]->getAdminAccess();
                    $roleId = $user->getUserRoles()[0]->getId();
                } else {
                    $response = [
                        'success' => false,
                        'message' => 'User has no role',
                    ];
                    $json = json_encode($response);
                    return $this->json($json);
                }

                $token = md5($user->getEmail().rand(0,9999).time());
                $expire = date('Y-m-d H:i:s', strtotime('+ 1 day'));

                $userApiKey = new UserApiKey();
                $userApiKey->setUser($user);
                $userApiKey->setKeyName('Request token by API');
                $userApiKey->setToken($token);
                $userApiKey->setExpire(new \DateTime($expire));
                $userApiKey->setActive(true);

                $em = $this->getDoctrine()->getManager();
                $em->persist($userApiKey);
                $em->flush();

                $response = array(
                    'success' => true,
                    'token' => $token,
                    'expire' => $expire,
                    'adminAccess' => $adminAccess,
                    'role' => $roleId
                );
            } else {
                $response = array(
                    'success' => false,
                    'message' => 'password_incorrect'
                );
            }
        } else {
            $response = array(
                'success' => false,
                'message' => 'user_not_found'
            );
        }

        header('Access-Control-Allow-Origin: *');
        header('Content-Type: application/json');
        $json = $this->serializer->serialize($response, 'json');
        if (!empty($callback)) echo $callback . '(' . $json . ')';
        else echo $json;
        exit;
    }

    /**
    * @Route("/api/v1/logout/", name="api_logout"), methods={"POST"})
    */
    public function logout(Request $request)
    {
        if ($request->headers->has('X-AUTH-TOKEN')) $token = $request->headers->get('X-AUTH-TOKEN');
        elseif ($request->headers->has('X-Auth-Token')) $token = $request->headers->get('X-Auth-Token');

        if (empty($token)) {
            $response = [
                'success' => true,
                'message' => 'token_not_found'
            ];
        }

        $key = $this->getDoctrine()
            ->getRepository(UserApiKey::class)
            ->findOneBy(array('token' => $token));

        if ($key) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($key);
            $em->flush();
            $response = [
                'success' => true,
                'message' => 'token_deleted'
            ];
        }

        if (empty($response)) {
            $response = [
                'success' => false,
                'message' => 'token_not_found'
            ];
        }

        $json = json_encode($response);
        return $this->json($json);
    }

    /**
    * @Route("/api/v1/request-password/", name="api_request_password"), methods={"POST"})
    */
    public function requestPassword(Request $request, MailService $mail, SettingService $setting)
    {
        $params = json_decode(file_get_contents("php://input"),true);
        $error = array();

        if (!empty($params['email'])) $email = $params['email'];
        else {

            $response = [
                'success' => false,
                'message' => 'email_required'
            ];
            $json = json_encode($response);
            return $this->json($json);
        }

        $user = $this->getDoctrine()
            ->getRepository(User::class)
            ->findOneBy(['email' => $email]);

        if ($user) {

            if (!empty($params['locale'])) {
                $locale = $this->getDoctrine()
                    ->getRepository(Locale::class)
                    ->findOneBy(['locale' => $params['locale']]);
            } elseif (!empty($params['locale_id'])) {
                $locale = $this->getDoctrine()
                    ->getRepository(Locale::class)
                    ->findOneBy(['id' => $params['locale_id']]);
            }

            if (empty($locale)) {
                $locale = $this->getDoctrine()
                    ->getRepository(Locale::class)
                    ->findOneBy(['default' => true]);
            }

            $confirmKey = md5($email.time());
            $user->setConfirmKey($confirmKey);
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            $mail->addToQueue(
                $user->getEmail(),
                'password-forget',
                $locale->getId(),
                array(
                    'token' => $confirmKey,
                    'name' => $user->getFirstname() . ' ' . $user->getlastname(),
                )
            );
            $response = [
                'success' => true,
                'message' => 'mail_sent',
            ];
        }

        if (empty($response)) {
            $response = [
                'success' => false,
                'message' => 'user_not_found'
            ];
        }

        $json = json_encode($response);
        return $this->json($json);
    }

    /**
    * @Route("/api/v1/check-reset-token/", name="api_check_reset_token"), methods={"POST"})
    */
    public function checkResetToken(Request $request)
    {
        $params = json_decode(file_get_contents("php://input"),true);

        if (!empty($params['token'])) $token = $params['token'];
        else {

            $response = [
                'success' => false,
                'message' => 'token_required'
            ];
            $json = json_encode($response);
            return $this->json($json);
        }

        $user = $this->getDoctrine()
            ->getRepository(User::class)
            ->findOneBy(['confirmKey' => $token]);

        if ($user) {
            $response = array(
                'success' => true,
                'message' => 'token_found'
            );

            $json = json_encode($response);
            return $this->json($json);
        }

        $response = [
            'success' => false,
            'message' => 'token_not_found'
        ];

        $json = json_encode($response);
        return $this->json($json);
    }

    /**
    * @Route("/api/v1/set-password/", name="api_set_password"), methods={"POST"})
    */
    public function setPassword(Request $request, MailService $mail, SettingService $setting, UserPasswordEncoderInterface $encoder)
    {
        $params = json_decode(file_get_contents("php://input"),true);

        if (!empty($params['token'])) $token = $params['token'];
        else {

            $response = [
                'success' => false,
                'message' => 'token_required'
            ];
            $json = json_encode($response);
            return $this->json($json);
        }

        if (!empty($params['password'])) $password = $params['password'];
        else {

            $response = [
                'success' => false,
                'message' => 'password_required'
            ];
            $json = json_encode($response);
            return $this->json($json);
        }

        $user = $this->getDoctrine()
            ->getRepository(User::class)
            ->findOneBy(['confirmKey' => $token]);

        if ($user) {

            $encoded = $encoder->encodePassword($user, $password);
            $user->setPassword($encoded);
            $user->setEmailConfirmed(true);

            $confirmKey = md5($user->getEmail().time());
            $user->setConfirmKey($confirmKey);

            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            $roles = $user->getUserRoles();
            if ($roles) {
                $adminAccess = $user->getUserRoles()[0]->getAdminAccess();
                $roleId = $user->getUserRoles()[0]->getId();
            } else {
                $response = [
                    'success' => false,
                    'message' => 'User has no role',
                ];
                $json = json_encode($response);
                return $this->json($json);
            }

            $token = md5($user->getEmail().rand(0,9999).time());
            $expire = date('Y-m-d H:i:s', strtotime('+ 1 day'));

            $userApiKey = new UserApiKey();
            $userApiKey->setUser($user);
            $userApiKey->setKeyName('Request token by API');
            $userApiKey->setToken($token);
            $userApiKey->setExpire(new \DateTime($expire));
            $userApiKey->setActive(true);

            $em->persist($userApiKey);
            $em->flush();

            $response = array(
                'success' => true,
                'email' => $user->getEmail(),
                'token' => $token,
                'expire' => $expire,
                'adminAccess' => $adminAccess,
                'role' => $roleId
            );

            $json = json_encode($response);
            return $this->json($json);
        }

        $response = [
            'success' => false,
            'message' => 'user_not_found'
        ];

        $json = json_encode($response);
        return $this->json($json);
    }
}
