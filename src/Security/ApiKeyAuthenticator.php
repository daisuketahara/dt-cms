<?php

// https://symfony.com/doc/current/security/api_key_authentication.html

namespace App\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\Authentication\Token\PreAuthenticatedToken;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\Authentication\SimplePreAuthenticatorInterface;
use Doctrine\ORM\EntityManager;

use App\Entity\Permission;
use App\Security\ApiKeyUserProvider;

class ApiKeyAuthenticator implements SimplePreAuthenticatorInterface
{
    protected $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function createToken(Request $request, $providerKey)
    {
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
        }

        $path = '/';
        $pathArray = explode('/', $request->getPathInfo());

        foreach ($pathArray as $part) {
            if (!empty($part)) {
                if (is_numeric($part)) $path .= '%/';
                else $path .= $part .'/';
            }
        }

        $permission = $this->em->getRepository(Permission::class)
            ->checkUserPermission($path, $apiKey);

        if (empty($permission)) {
            throw new CustomUserMessageAuthenticationException('You do not have permisssion to use this function.');
        }

        return new PreAuthenticatedToken(
            '$token.',
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
