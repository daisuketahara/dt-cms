<?php

namespace App\Controller\Api;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

use App\Entity\UserApiKey;
use App\Entity\User;
use App\Service\MailService;
use App\Service\SettingService;

class SecurityController extends AbstractController
{

    /**
    * @Route("/api/v1/gettoken/", name="api_get_token"), methods={"GET","HEAD"})
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
                );
            }
        } else {
            $response = array(
                'success' => false,
            );
        }

        header('Access-Control-Allow-Origin: *');
        header('Content-Type: application/json');
        $json = json_encode($response);
        if (!empty($callback)) echo $callback . '(' . $json . ')';
        else echo $json;
        exit;
    }

    /**
    * @Route("/api/v1/logout/", name="api_logout"), methods={"GET","HEAD"})
    */
    public function logout(Request $request)
    {
        if ($request->headers->has('X-AUTH-TOKEN')) $token = $request->headers->get('X-AUTH-TOKEN');
        elseif ($request->headers->has('X-Auth-Token')) $token = $request->headers->get('X-Auth-Token');

        if (empty($token)) {
            $response = [
                'success' => false,
            ];
        }

        $key = $this->getDoctrine()
            ->getRepository(UserApiKey::class)
            ->findOneBy(array('token' => $token));

        if ($key) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($key);
            $em->flush();
            $response = ['success' => true];
        }

        if (empty($response)) {
            $response = [
                'success' => false,
            ];
        }

        $json = json_encode($response);
        return $this->json($json);
    }

    /**
    * @Route("/api/v1/request-password/", name="api_request_password"), methods={"GET","HEAD"})
    */
    public function requestPassword(Request $request, MailService $mail, SettingService $setting)
    {
        $params = json_decode(file_get_contents("php://input"),true);
        $error = array();

        if (!empty($params['email'])) $email = $params['email'];

        $user = $this->getDoctrine()
            ->getRepository(User::class)
            ->findOneBy(array('email' => $email));

        if ($user) {

            $confirmKey = md5($email.time());
            $user->setConfirmKey($confirmKey);
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            $mail->addToQueue(
                $this->setting->getSetting('site.email'),
                'password-forget',
                $locale->getId(),
                array(
                    'confirm_key' => $confirmKey,
                    'firstname' => $user->getFirstname(),
                    'lastname' => $user->getlastname(),
                )
            );
            $response = [
                'success' => true,
                'message' => true,
            ];
        }

        if (empty($response)) {
            $response = [
                'success' => false,
            ];
        }

        $json = json_encode($response);
        return $this->json($json);
    }
}
