<?php

namespace App\Controller;

use App\DTO\FlashMessage;
use App\DTO\CoreError;
use App\DTO\RepoDeleteInput;
use App\DTO\RepoRenameInput;
use App\Service\CoreInteract;
use App\Service\CoreInteractInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsCsrfTokenValid;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/repo', name: 'repo_')]
class RepoController extends AbstractController
{
    /**
     * Index page of the repositories
     */
    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('repo/index.html.twig');
    }

    /**
     * List all repositories
     */
    #[Route('/list', name: 'list', methods: ['GET'])]
    public function list(Request $request, CoreInteract $coreInteract): Response
    {
        if (!$request->headers->has('Turbo-Frame')) {
            return $this->redirectToRoute('repo_index');
        }

        $repoListOutput = $coreInteract->repoList();
        return $this->render('repo/_list.html.twig', ['repoListOutput' => $repoListOutput]);
    }

    /**
     * Rename one repository
     */
    #[IsCsrfTokenValid('submit', methods: ['POST'])]
    #[Route('/rename/{oldName}', name: 'rename', methods: ['GET', 'POST'], requirements: ['oldName' => CoreInteractInterface::REGEX_NAME])]
    public function rename(string $oldName, Request $request, ValidatorInterface $validator, CoreInteract $coreInteract): Response
    {
        if (!$request->headers->has('Turbo-Frame')) {
            return $this->redirectToRoute('repo_index');
        }

        $repoRenameOutput = null;
        if ($request->getMethod() == 'POST') {
            $newName =  $request->getPayload()->get('new-name');
            $repoRenameInput = new RepoRenameInput($oldName, $newName);

            $errors = $validator->validate($repoRenameInput);
            $repoRenameOutput = count($errors) == 0 ?  $coreInteract->repoRename($repoRenameInput) : new CoreError('renameInvalid');

            if ($repoRenameOutput === true) {
                $this->addFlash('success', new FlashMessage('repo.rename.success'));
                return $this->redirectToRoute('repo_index');
            }
        }

        return $this->render('repo/_rename.html.twig', ['repoRenameOutput' => $repoRenameOutput]);
    }

    /**
     * Delete one repository
     */
    #[IsGranted('ROLE_ADMIN')]
    #[IsCsrfTokenValid('submit', methods: ['POST'])]
    #[Route('/delete/{name}', name: 'delete', methods: ['GET', 'POST'], requirements: ['name' => CoreInteractInterface::REGEX_NAME])]
    public function delete(string $name, Request $request, ValidatorInterface $validator, CoreInteract $coreInteract): Response
    {
        if (!$request->headers->has('Turbo-Frame')) {
            return $this->redirectToRoute('repo_index');
        }

        $repoDeleteOutput = null;
        if ($request->getMethod() == 'POST') {
            $repoDeleteInput = new RepoDeleteInput($name);

            $errors = $validator->validate($repoDeleteInput);
            $repoDeleteOutput = count($errors) == 0 ?  $coreInteract->repoDelete($repoDeleteInput) : new CoreError('deleteInvalid');

            if ($repoDeleteOutput === true) {
                $this->addFlash('success', new FlashMessage('repo.delete.success'));
                return $this->redirectToRoute('repo_index');
            }
        }

        return $this->render('repo/_delete.html.twig', ['repoDeleteOutput' => $repoDeleteOutput]);
    }
}
