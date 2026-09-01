<?php

namespace App\Controller;

use App\Attribute\TurboframeOnly;
use App\DTO\UserKeysListData;
use App\Service\CoreInteract;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Uid\Uuid;

/**
 * Controller defining all routes relative to managing the user SSH keys.
 */
#[Route('/user/keys', name: 'user_keys_')]
class UserKeysController extends AbstractController
{
    /**
     * Index page of the SSH keys.
     */
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('user/keys/index.html.twig');
    }

    /**
     * List all SSH keys.
     */
    #[TurboframeOnly('user_keys_index')]
    #[Route('/list', name: 'list', methods: ['GET'])]
    public function list(CoreInteract $coreInteract): Response
    {
        $userUuid = new Uuid($this->getUser()->getId());
        $userKeysListData = new UserKeysListData($userUuid);
        $errorData = $coreInteract->userKeysList($userKeysListData);

        return $this->render('user/keys/_list.html.twig', ['userKeysListData' => $userKeysListData, 'errorData' => $errorData]);
    }
}
