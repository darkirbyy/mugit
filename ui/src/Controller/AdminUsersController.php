<?php

namespace App\Controller;

use App\Attribute\TurboframeOnly;
use App\DTO\UserListData;
use App\Service\CoreInteractInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
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
    #[Route('/', name: 'index', methods: ['GET'])]
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
}
