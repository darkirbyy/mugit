<?php

namespace App\Controller;

use App\DTO\CoreError;
use App\Service\CoreInteract;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/repo', name: 'repo_')]
class RepoController extends AbstractController
{
    /**
     * Index page of the repositories
     */
    #[Route('/', name: 'index')]
    public function index(): Response
    {
        return $this->render('repo/index.html.twig');
    }

    /**
     * List of all repositories
     */
    #[Route('/list', name: 'list')]
    public function list(Request $request, CoreInteract $coreInteract): Response
    {
        if (!$request->headers->has('Turbo-Frame')) {
            return $this->redirectToRoute('repo_index');
        }

        $repoInfoList = $coreInteract->repoList();
        if ($repoInfoList instanceof CoreError) {
            return $this->render('repo/_error.html.twig', ['coreError' => $repoInfoList]);
        }
        return $this->render('repo/_list.html.twig', ['repoInfoList' => $repoInfoList]);
    }
}
