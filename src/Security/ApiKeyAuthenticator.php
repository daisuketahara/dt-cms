<?php

// https://symfony.com/doc/current/security/api_key_authentication.html

namespace App\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\PreAuthenticatedToken;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\Authentication\SimplePreAuthenticatorInterface;
use Doctrine\ORM\EntityManager;

use App\Security\ApiKeyUserProvider;
use App\Entity\UserApiKey;
use App\Entity\User;

class ApiKeyAuthenticator implements SimplePreAuthenticatorInterface
{
    protected $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function createToken(Request $request, $providerKey)
    {
        if ($request->getPathInfo() == '/api/gettoken/') {

            $username = $request->query->get('username', '');
            $password = $request->query->get('password', '');
            $callback = $request->query->get('callback', '');

            $user = $this->em->getRepository(User::class)
                ->findOneBy(array('email' => $username));

            if ($user) {
                $hash = $user->getPassword();

                if (password_verify($password, $hash)) {

                    $token = md5($user->getEmail().rand(0,9999).time());
                    $expire = date('Y-m-d H:i:s', strtotime('+ 30 minutes'));

                    $userApiKey = new UserApiKey();
                    $userApiKey->setUser($user);
                    $userApiKey->setKeyName('Request token by API');
                    $userApiKey->setToken($token);
                    $userApiKey->setExpire(new \DateTime($expire));
                    $userApiKey->setActive(true);
                    $this->em->persist($userApiKey);
                    $this->em->flush();

                    $response = array(
                        'result' => 'valid',
                        'token' => $token,
                        'expire' => $expire,
                    );
                }
            }

            if (empty($response)) {
                $response = array(
                    'result' => 'invalid',
                );
            }

            header('Access-Control-Allow-Origin: *');
            header('Content-Type: application/json');
            $json = json_encode($response);
            if (!empty($callback)) echo $callback . '(' . $json . ')';
            else echo $json;
            exit;
        }

        $authHeader = $request->headers->get('Authorization');
        $token = $request->query->get('token');
        if (empty($token)) $token = $request->request->get('token');

        if (!empty($authHeader) && strpos($authHeader, 'Bearer ') !== false) {
            $apiKey = substr($authHeader, 7);
        } elseif (!empty($token)) {
            $apiKey = $token;
        }

        if (empty($apiKey)) {
            throw new BadCredentialsException();

            // or to just skip api key authentication
            // return null;
        }

        return new PreAuthenticatedToken(
            'anon.',
            $apiKey,
            $providerKey
        );
    }

    public function supportsToken(TokenInterface $token, $providerKey)
    {
        return $token instanceof PreAuthenticatedToken && $token->getProviderKey() === $providerKey;
    }

    public function authenticateToken(TokenInterface $token, UserProviderInterface $userProvider, $providerKey)
    {
        if (!$userProvider instanceof ApiKeyUserProvider) {
            throw new \InvalidArgumentException(
                sprintf(
                    'The user provider must be an instance of ApiKeyUserProvider (%s was given).',
                    get_class($userProvider)
                )
            );
        }

        $apiKey = $token->getCredentials();
        $username = $userProvider->getUsernameForApiKey($apiKey);

        if (!$username) {
            // CAUTION: this message will be returned to the client
            // (so don't put any un-trusted messages / error strings here)
            throw new CustomUserMessageAuthenticationException(
                sprintf('API Key "%s" does not exist.', $apiKey)
            );
        }

        $user = $userProvider->loadUserByUsername($username);

        return new PreAuthenticatedToken(
            $user,
            $apiKey,
            $providerKey,
            $user->getRoles()
        );
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        return new Response(
            // this contains information about *why* authentication failed
            // use it, or return your own message
            strtr($exception->getMessageKey(), $exception->getMessageData()),
            401
        );
    }
}
