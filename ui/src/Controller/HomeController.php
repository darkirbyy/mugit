<?php

namespace App\Controller;

use App\Tests\Mock\KeycloakMockEntryPoint;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Controller defining the welcome route as well as dev/test only routes.
 */
#[Route('', name: 'home_')]
class HomeController extends AbstractController
{
    /**
     * Welcome page of the application.
     */
    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->redirectToRoute('repo_index');
    }

    /**
     * Route to switch from standard user to admin user, ONLY available in dev/test environment and if keycloak is mocked.
     */
    #[Route('/switch', name: 'switch', methods: ['GET'], env: ['dev', 'test'], condition: "true == '%mock.keycloak_enable%'")]
    public function switch(Security $security, Request $request, KeycloakMockEntryPoint $keycloakMockEntryPoint): Response
    {
        if (null !== $request->query->get('is-admin')) {
            $isAdmin = $request->query->getBoolean('is-admin');
        } else {
            $isAdmin = !$this->getUser()->getIsAdmin();
        }

        $security->logout(false);
        $security->login($keycloakMockEntryPoint->createFakeUser(1, $isAdmin));

        $url = $request->headers->get('referer', '/');

        return $this->redirect($url);
    }
}
