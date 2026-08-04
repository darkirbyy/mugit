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
        private KeycloakMockUserProvider $keycloakMockUserProvider,
        #[Autowire('%mock.keycloak_enable%')] private bool $mockKeycloakEnable,
        #[Autowire('%mock.keycloak_admin%')] private bool $mockKeycloakAdmin,
    ) {}

    public function start(Request $request, ?AuthenticationException $authException = null): RedirectResponse
    {
        if ($this->mockKeycloakEnable) {
            $user = $this->keycloakMockUserProvider->createUser($this->mockKeycloakAdmin);
            $token = new UsernamePasswordToken($user, 'main', $user->getRoles());
            $this->tokenStorage->setToken($token);
            $request->getSession()->set('_security_main', serialize($token));
            $request->getSession()->save();

            return new RedirectResponse('/');
        } else {
            return $this->inner->start($request, $authException);
        }
    }
}
