<?php

namespace App\Controller;

use App\Service\CoreSSH;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('', name: 'home_')]
class HomeController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(CoreSSH $coreSSH): Response
    {
        ['output' => $lines] = $coreSSH->exec('repo list');

        return $this->render('default/index.html.twig', ['lines' => $lines]);
    }
}
