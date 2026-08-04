<?php

declare(strict_types=1);

namespace App\Tests\Mock;

use App\Service\KeycloakManagerInterface;
use Mainick\KeycloakClientBundle\Security\User\KeycloakUserProvider;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;

class KeycloakMockEntryPoint implements AuthenticationEntryPointInterface
{
    public function __construct(
        private AuthenticationEntryPointInterface $inner,
        private TokenStorageInterface $tokenStorage,
        private KeycloakMockUserCreate $keycloakMockUserCreate,
        #[Autowire('%mock.keycloak_enable%')] private bool $mockKeycloakEnable,
    ) {}

    public function start(Request $request, ?AuthenticationException $authException = null): RedirectResponse
    {
        if ($this->mockKeycloakEnable) {
            $isAdmin = $request->getSession()->get('is-admin', true);
            $user = $this->keycloakMockUserCreate->createUser($isAdmin);
            
            $token = new UsernamePasswordToken($user, 'main', $user->getRoles());
            $this->tokenStorage->setToken($token);
            
            $request->getSession()->set('is-admin', $isAdmin);
            $request->getSession()->set('_security_main', serialize($token));
            $request->getSession()->save();

            return new RedirectResponse('/');
        } else {
            return $this->inner->start($request, $authException);
        }
    }
}
