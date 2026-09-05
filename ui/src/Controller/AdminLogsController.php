<?php

namespace App\Controller;

use App\Attribute\TurboframeOnly;
use App\DTO\FlashData;
use App\DTO\LogListData;
use App\DTO\LogSizeData;
use App\Service\CoreInteractInterface;
use App\Service\FormHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\ValueResolver;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsCsrfTokenValid;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Controller defining all routes relative to managing the logs for the admin.
 */
#[Route('/admin/logs', name: 'admin_logs_')]
#[IsGranted('ROLE_ADMIN')]
class AdminLogsController extends AbstractController
{
    /**
     * Index page of the logs.
     */
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('admin/logs/index.html.twig');
    }

    /**
     * List subset of the logs.
     */
    #[TurboframeOnly('admin_logs_index')]
    #[Route('/list', name: 'list', methods: ['GET'])]
    public function list(#[ValueResolver('data')] LogListData $logListData, CoreInteractInterface $coreInteract): Response
    {
        $logSizeData = new LogSizeData();
        $errorData = $coreInteract->logSize($logSizeData);
        $errorData = $errorData ?? $coreInteract->logList($logListData);

        return $this->render('admin/logs/_list.html.twig', ['logSizeData' => $logSizeData, 'logListData' => $logListData, 'errorData' => $errorData]);
    }

    /**
     * Purge all logs.
     */
    #[IsCsrfTokenValid('submit', methods: ['POST'])]
    #[TurboframeOnly('admin_logs_index')]
    #[Route('/purge', name: 'purge', methods: ['GET', 'POST'])]
    public function purge(FormHandler $formHandler): Response
    {
        $formData = $formHandler->handle(null, 'logPurge');
        if ($formData->proceed) {
            $this->addFlash('success', new FlashData('log.purge.success'));

            return $this->redirectToRoute('admin_logs_index');
        }

        return $this->render('admin/logs/_purge.html.twig', ['formData' => $formData]);
    }
}
