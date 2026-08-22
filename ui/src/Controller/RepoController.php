<?php

namespace App\Controller;

use App\Attribute\TurboframeOnly;
use App\DTO\FlashData;
use App\DTO\RepoCreateData;
use App\DTO\RepoDeleteData;
use App\DTO\RepoListData;
use App\DTO\RepoRenameData;
use App\Service\CoreInteract;
use App\Service\FormHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
    public function list(CoreInteract $coreInteract): Response
    {
        $repoListData = new RepoListData();
        $errorData = $coreInteract->repoList($repoListData);

        return $this->render('repo/_list.html.twig', ['repoListData' => $repoListData, 'errorData' => $errorData]);
    }

    /**
     * Create one repository.
     */
    #[IsCsrfTokenValid('submit', methods: ['POST'])]
    #[TurboframeOnly('repo_index')]
    #[Route('/create', name: 'create', methods: ['GET', 'POST'])]
    public function create(#[ValueResolver('data')] RepoCreateData $repoCreateData, FormHandler $formHandler): Response
    {
        $formData = $formHandler->handle($repoCreateData, 'repoCreate');
        if ($formData->proceed) {
            $this->addFlash('success', new FlashData('repo.create.success'));

            return $this->redirectToRoute('repo_index');
        }

        return $this->render('repo/_create.html.twig', ['repoCreateData' => $repoCreateData, 'formData' => $formData]);
    }

    /**
     * Rename one repository.
     */
    #[IsCsrfTokenValid('submit', methods: ['POST'])]
    #[TurboframeOnly('repo_index')]
    #[Route('/rename', name: 'rename', methods: ['GET', 'POST'])]
    public function rename(#[ValueResolver('data')] RepoRenameData $repoRenameData, FormHandler $formHandler): Response
    {
        $formData = $formHandler->handle($repoRenameData, 'repoRename');
        if ($formData->proceed) {
            $this->addFlash('success', new FlashData('repo.rename.success'));

            return $this->redirectToRoute('repo_index');
        }

        return $this->render('repo/_rename.html.twig', ['repoRenameData' => $repoRenameData, 'formData' => $formData]);
    }

    /**
     * Delete one repository.
     */
    #[IsGranted('ROLE_ADMIN')]
    #[IsCsrfTokenValid('submit', methods: ['POST'])]
    #[TurboframeOnly('repo_index')]
    #[Route('/delete', name: 'delete', methods: ['GET', 'POST'])]
    public function delete(#[ValueResolver('data')] RepoDeleteData $repoDeleteData, FormHandler $formHandler): Response
    {
        $formData = $formHandler->handle($repoDeleteData, 'repoDelete');
        if ($formData->proceed) {
            $this->addFlash('success', new FlashData('repo.delete.success'));

            return $this->redirectToRoute('repo_index');
        }

        return $this->render('repo/_delete.html.twig', ['repoDeleteData' => $repoDeleteData, 'formData' => $formData]);
    }
}
