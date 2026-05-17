<?php

namespace Symfony\Component\Security\Guard;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * Local compatibility shim for bundles still referencing the legacy Guard API.
 *
 * The Lexik JWT bundle v2.x still loads its Guard authenticator service definition
 * on Symfony 7, so this interface keeps the class autoloadable without requiring
 * the deprecated symfony/security-guard package.
 */
interface AuthenticatorInterface
{
    public function supports(Request $request);

    public function getCredentials(Request $request);

    public function getUser($credentials, UserProviderInterface $userProvider);

    public function checkCredentials($credentials, UserInterface $user);

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey);

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception);

    public function start(Request $request, AuthenticationException $authException = null);

    public function supportsRememberMe();

    public function createAuthenticatedToken(UserInterface $user, $providerKey);
}

