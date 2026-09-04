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
            $this->loginFakeUser($session, false);

            $url = $session->get('_security.main.target_path', '/');

            return new RedirectResponse($url);
        }

        return $this->inner->start($request, $authException);
    }

    public function loginFakeUser(SessionInterface $session, bool $isAdmin): UserInterface
    {
        $user = $this->createFakeUser(1, $isAdmin);

        $token = new UsernamePasswordToken($user, 'main', $user->getRoles());
        $this->tokenStorage->setToken($token);

        $session->set('_security_main', serialize($token));
        $session->save();

        return $user;
    }

    public function createFakeUser(int $userNumber, bool $isAdmin): KeycloakMockUser
    {
        $uuid = UuidV4::fromString(self::userNumberToUuid(1));
        $username = 'user' . $userNumber;
        $avatarPath = $this->packages->getUrl('build/tests/avatar' . $userNumber . '.png');

        return new KeycloakMockUser($uuid, $username, $avatarPath, $isAdmin);
    }

    public static function createTestUser(int $userNumber, bool $isAdmin): KeycloakMockUser
    {
        return new KeycloakMockUser(UuidV4::fromString(self::userNumberToUuid($userNumber)), 'test', '', $isAdmin);
    }

    public static function userNumberToUuid(int $userNumber): string
    {
        return '11111111-1111-4111-8111-' . str_repeat((string) $userNumber, 12);
    }
}
