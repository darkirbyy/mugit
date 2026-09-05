<?php

namespace App\Controller;

use App\Attribute\TurboframeOnly;
use App\DTO\FlashData;
use App\DTO\UserKeysListData;
use App\DTO\UserKeysRemoveData;
use App\DTO\UserListData;
use App\Service\CoreInteractInterface;
use App\Service\ValidationHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\ValueResolver;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsCsrfTokenValid;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Controller defining all routes relative to managing the users for the admin.
 */
#[Route('/admin/users', name: 'admin_users_')]
#[IsGranted('ROLE_ADMIN')]
class AdminUsersController extends AbstractController
{
    /**
     * Index page of the users.
     */
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('admin/users/index.html.twig');
    }

    /**
     * List all users having registred at least one key.
     */
    #[TurboframeOnly('admin_users_index')]
    #[Route('/list', name: 'list', methods: ['GET'])]
    public function list(CoreInteractInterface $coreInteract): Response
    {
        $userListData = new UserListData();
        $errorData = $coreInteract->userList($userListData);

        return $this->render('admin/users/_list.html.twig', ['userListData' => $userListData, 'errorData' => $errorData]);
    }

    /**
     * List all keys of one given user.
     */
    #[TurboframeOnly('admin_users_index')]
    #[Route('/keys', name: 'keys', methods: ['GET'])]
    public function keys(#[ValueResolver('data')] UserKeysListData $userKeysListData, ValidationHandler $validationHandler): Response
    {
        $errorData = $validationHandler->handleQuery($userKeysListData, 'userKeysList');

        return $this->render('admin/users/_keys.html.twig', ['userKeysListData' => $userKeysListData, 'errorData' => $errorData]);
    }

    /**
     * Remove one SSH keys for the given user.
     */
    #[IsCsrfTokenValid('submit', methods: ['POST'])]
    #[TurboframeOnly('admin_users_index')]
    #[Route('/keys/remove', name: 'keys_remove', methods: ['GET', 'POST'])]
    public function keys_remove(#[ValueResolver('data')] UserKeysRemoveData $userKeysRemoveData, ValidationHandler $validationHandler): Response
    {
        $formData = $validationHandler->handleForm($userKeysRemoveData, 'userKeysRemove');
        if ($formData->proceed) {
            $this->addFlash('success', new FlashData('user.keys.remove.success'));

            return $this->redirectToRoute('admin_users_index');
        }

        return $this->render('user/keys/_remove.html.twig', ['userKeysRemoveData' => $userKeysRemoveData, 'formData' => $formData]);
    }
}
