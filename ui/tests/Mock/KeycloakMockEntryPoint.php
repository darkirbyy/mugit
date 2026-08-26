<?php

declare(strict_types=1);

namespace App\Tests\Mock;

use Symfony\Component\Asset\Packages;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;
use Symfony\Component\Uid\UuidV4;

class KeycloakMockEntryPoint implements AuthenticationEntryPointInterface
{
    public function __construct(
        private AuthenticationEntryPointInterface $inner,
        private TokenStorageInterface $tokenStorage,
        private Packages $packages,
        #[Autowire('%mock.keycloak_enable%')] private bool $mockKeycloakEnable,
    ) {}

    public function start(Request $request, ?AuthenticationException $authException = null): RedirectResponse
    {
        if ($this->mockKeycloakEnable) {
            $session = $request->getSession();
            $isAdmin = $request->getSession()->get('is-admin', false);
            $this->loginFakeUser($session, $isAdmin);

            return new RedirectResponse('/');
        }

        return $this->inner->start($request, $authException);
    }

    public function loginFakeUser(SessionInterface $session, bool $isAdmin): UserInterface
    {
        $user = $this->createUser($isAdmin);

        $token = new UsernamePasswordToken($user, 'main', $user->getRoles());
        $this->tokenStorage->setToken($token);

        $session->set('is-admin', $isAdmin);
        $session->set('_security_main', serialize($token));
        $session->save();

        return $user;
    }

    private function createUser(bool $isAdmin): KeycloakMockUser
    {
        $number = 1;
        $uuid = UuidV4::fromString('11111111-1111-4111-8111-' . 111111111111 * $number);
        $username = 'user' . $number;
        $avatarPath = $this->packages->getUrl('build/tests/avatar' . $number . '.png');

        return new KeycloakMockUser($uuid, $username, $avatarPath, $isAdmin);
    }
}
