<?php

declare(strict_types=1);

namespace App\Security;

use Mainick\KeycloakClientBundle\Interface\IamClientInterface;
use Mainick\KeycloakClientBundle\Token\KeycloakResourceOwner;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Http\Event\LogoutEvent;

/**
 * Event to fix the URL computed in LogoutAuthListener.php from mainick/keycloak-bundle.
 */
#[AsEventListener(LogoutEvent::class, 'onLogout', -30)]
final readonly class KeycloakLogoutListener
{
    public function __construct(#[Autowire('%hub.base_url%')] private string $hubBaseUrl, private LoggerInterface $keycloakClientLogger, private IamClientInterface $iamClient) {}

    public function onLogout(LogoutEvent $event): void
    {
        if (null === $event->getToken() || null === $event->getToken()->getUser()) {
            return;
        }

        $user = $event->getToken()->getUser();
        if (!$user instanceof KeycloakResourceOwner) {
            return;
        }

        $accessToken = $user->getAccessToken();
        $logoutUrl = $this->iamClient->logoutUrl([
            'access_token' => $accessToken,
            'state' => $accessToken->getValues()['session_state'],
            'id_token_hint' => $accessToken->getValues()['id_token'],
            'post_logout_redirect_uri' => $this->hubBaseUrl,
        ]);
        $this->keycloakClientLogger->info('KeycloakLogoutListener::__invoke', [
            'logoutUrl' => $logoutUrl,
        ]);

        $event->setResponse(new RedirectResponse($logoutUrl));
    }
}
