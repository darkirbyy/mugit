<?php

namespace App\Controller;

use App\Attribute\TurboframeOnly;
use App\DTO\FlashData;
use App\DTO\UserKeysListData;
use App\DTO\UserKeysRemoveData;
use App\Service\CoreInteract;
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
     * List all SSH keys of the current user.
     */
    #[TurboframeOnly('user_keys_index')]
    #[Route('/list', name: 'list', methods: ['GET'])]
    public function list(CoreInteract $coreInteract): Response
    {
        $userKeysListData = new UserKeysListData($this->getUser()->getId());
        $errorData = $coreInteract->userKeysList($userKeysListData);

        return $this->render('user/keys/_list.html.twig', ['userKeysListData' => $userKeysListData, 'errorData' => $errorData]);
    }

    /**
     * Remove one SSH keys  of the current user.
     */
    #[IsCsrfTokenValid('submit', methods: ['POST'])]
    #[TurboframeOnly('user_keys_index')]
    #[Route('/remove', name: 'remove', methods: ['GET', 'POST'])]
    public function remove(#[ValueResolver('data')] UserKeysRemoveData $userKeysRemoveData, FormHandler $formHandler): Response
    {
        $formData = $formHandler->handle($userKeysRemoveData, 'userKeysRemove');
        if ($formData->proceed) {
            $this->addFlash('success', new FlashData('user.keys.remove.success'));

            return $this->redirectToRoute('user_keys_index');
        }

        return $this->render('user/keys/_remove.html.twig', ['userKeysRemoveData' => $userKeysRemoveData, 'formData' => $formData]);
    }
}
