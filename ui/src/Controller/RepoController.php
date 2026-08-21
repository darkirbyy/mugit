<?php

namespace App\Controller;

use App\Attribute\TurboframeOnly;
use App\DTO\FlashMessage;
use App\DTO\RepoDeleteData;
use App\DTO\RepoListData;
use App\DTO\RepoRenameData;
use App\Service\CoreInteract;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\ValueResolver;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsCsrfTokenValid;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Controller defining all routes relative to managing the repositories.
 */
#[Route('/repo', name: 'repo_')]
class RepoController extends AbstractController
{
    /**
     * Index page of the repositories.
     */
    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('repo/index.html.twig');
    }

    /**
     * List all repositories.
     */
    #[TurboframeOnly('repo_index')]
    #[Route('/list', name: 'list', methods: ['GET'])]
    public function list(Request $request, CoreInteract $coreInteract): Response
    {
        $repoListData = new RepoListData([]);
        $coreError = $coreInteract->repoList($repoListData);

        return $this->render('repo/_list.html.twig', ['repoListData' => $repoListData, 'coreError' => $coreError]);
    }

    /**
     * Rename one repository.
     */
    #[IsCsrfTokenValid('submit', methods: ['POST'])]
    #[TurboframeOnly('repo_index')]
    #[Route('/rename', name: 'rename', methods: ['GET', 'POST'])]
    public function rename(#[ValueResolver('data')] RepoRenameData $repoRenameData, Request $request, CoreInteract $coreInteract): Response
    {
        $coreError = null;
        if ('POST' == $request->getMethod()) {
            $coreError = $coreInteract->repoRename($repoRenameData);

            if (null === $coreError) {
                $this->addFlash('success', new FlashMessage('repo.rename.success'));

                return $this->redirectToRoute('repo_index');
            }
        }

        return $this->render('repo/_rename.html.twig', ['repoRenameData' => $repoRenameData, 'coreError' => $coreError]);
    }

    /**
     * Delete one repository.
     */
    #[IsGranted('ROLE_ADMIN')]
    #[IsCsrfTokenValid('submit', methods: ['POST'])]
    #[TurboframeOnly('repo_index')]
    #[Route('/delete', name: 'delete', methods: ['GET', 'POST'])]
    public function delete(#[ValueResolver('data')] RepoDeleteData $repoDeleteData, Request $request, CoreInteract $coreInteract): Response
    {
        $coreError = null;
        if ('POST' == $request->getMethod()) {
            $coreError = $coreInteract->repoDelete($repoDeleteData);

            if (null === $repoDeleteData) {
                $this->addFlash('success', new FlashMessage('repo.delete.success'));

                return $this->redirectToRoute('repo_index');
            }
        }

        return $this->render('repo/_delete.html.twig', ['repoDeleteData' => $repoDeleteData, 'coreError' => $coreError]);
    }
}
