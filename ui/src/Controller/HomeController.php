<?php

namespace App\Controller;

use App\Service\CoreSSH;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('', name: 'home_')]
class HomeController extends AbstractController
{
    /**
     * Welcome page of the application
     */
    #[Route('/', name: 'index')]
    public function index(CoreSSH $coreSSH): Response
    {
        ['output' => $lines] = $coreSSH->exec('repo list');
        $lines = array_map(function ($line) {
            $exploded = explode(' ', $line);
            $size = intval($exploded[1]);
            $unit = 'Ko';
            if ($size > 1024) {
                $size /= $size;
                $unit = 'Mo';
            }
            return 'Name: ' . $exploded[0] . ' - Size: ' . $size . $unit;
        }, $lines);

        return $this->render('home/index.html.twig', ['lines' => $lines]);
    }

    /**
     * Route to switch from standard user to admin user, ONLY available in dev/test environment and if keycloak is mocked
     */
    #[Route('/switch', name: 'switch', env: ['dev', 'test'], condition: "true == '%mock.keycloak_enable%'")]
    public function switch(Security $security, Request $request): Response
    {
        $isAdmin = $this->getUser()->getIsAdmin();
        $security->logout(false);

        $request->getSession()->set('is-admin', !$isAdmin);
        $request->getSession()->save();

        $url = $request->headers->get('referer', '/');

        return $this->redirect($url);
    }
}
