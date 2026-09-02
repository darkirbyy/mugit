<?php

namespace App\Controller;

use App\Attribute\TurboframeOnly;
use App\DTO\FlashData;
use App\DTO\UserKeysAddData;
use App\DTO\UserKeysListData;
use App\DTO\UserKeysRemoveData;
use App\Service\CoreInteractInterface;
use App\Service\FormHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\ValueResolver;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsCsrfTokenValid;

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
     * List all SSH keys for the current user.
     */
    #[TurboframeOnly('user_keys_index')]
    #[Route('/list', name: 'list', methods: ['GET'])]
    public function list(CoreInteractInterface $coreInteract): Response
    {
        $userKeysListData = new UserKeysListData($this->getUser()->getId());
        $errorData = $coreInteract->userKeysList($userKeysListData);

        return $this->render('user/keys/_list.html.twig', ['userKeysListData' => $userKeysListData, 'errorData' => $errorData]);
    }

    /**
     * Add one SSH key for the current user.
     */
    #[IsCsrfTokenValid('submit', methods: ['POST'])]
    #[TurboframeOnly('user_keys_index')]
    #[Route('/add', name: 'add', methods: ['GET', 'POST'])]
    public function add(#[ValueResolver('data')] UserKeysAddData $userKeysAddData, FormHandler $formHandler): Response
    {
        $userKeysAddData->uuid = $this->getUser()->getId();
        $formData = $formHandler->handle($userKeysAddData, 'userKeysAdd');
        if ($formData->proceed) {
            $this->addFlash('success', new FlashData('user.keys.add.success'));

            return $this->redirectToRoute('user_keys_index');
        }

        return $this->render('user/keys/_add.html.twig', ['userKeysAddData' => $userKeysAddData, 'formData' => $formData]);
    }

    /**
     * Remove one SSH keys for the current user.
     */
    #[IsCsrfTokenValid('submit', methods: ['POST'])]
    #[TurboframeOnly('user_keys_index')]
    #[Route('/remove', name: 'remove', methods: ['GET', 'POST'])]
    public function remove(#[ValueResolver('data')] UserKeysRemoveData $userKeysRemoveData, FormHandler $formHandler): Response
    {
        $userKeysRemoveData->uuid = $this->getUser()->getId();
        $formData = $formHandler->handle($userKeysRemoveData, 'userKeysRemove');
        if ($formData->proceed) {
            $this->addFlash('success', new FlashData('user.keys.remove.success'));

            return $this->redirectToRoute('user_keys_index');
        }

        return $this->render('user/keys/_remove.html.twig', ['userKeysRemoveData' => $userKeysRemoveData, 'formData' => $formData]);
    }
}
