<?php

namespace App\Attribute;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Event to redirect when fetching directly a turbo-frame only route
 */
#[AsEventListener(KernelEvents::CONTROLLER, 'onKernelController', 25)]
final class TurboframeOnlyListener
{
    public function __construct(private UrlGeneratorInterface $urlGenerator) {}

    public function onKernelController(ControllerEvent $event): void
    {
        // Act only if TurboframeOnly attribute is set
        $attributes = $event->getAttributes(TurboframeOnly::class);
        if ($attributes === null || !array_key_exists(0, $attributes)) {
            return;
        }

        // Pass if it is actually a turbo-frame
        if ($event->getRequest()->headers->has('Turbo-Frame')) {
            return;
        }

        // Retrieve the route defined in the attribute and generate the URL
        $redirectRoute = $attributes[0]->redirectRoute;
        $url = $this->urlGenerator->generate($redirectRoute);

        // Generate a lambda controller with the redirection
        $event->setController(fn() => new RedirectResponse($url));
    }
}
