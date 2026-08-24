<?php

declare(strict_types=1);

namespace App\Tests\Unit\Attribute;

use App\Attribute\TurboframeOnly;
use App\Attribute\TurboframeOnlyListener;
use PHPUnit\Framework\Attributes as PU;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class TurboframeOnlyListenerTest extends TestCase
{
    private HttpKernelInterface $httpKernel;
    private Request $request;
    private ControllerEvent $event;
    private UrlGeneratorInterface $urlGenerator;
    private TurboframeOnlyListener $turboframeOnlyListener;

    #[\Override]
    protected function setUp(): void
    {
        $this->httpKernel = $this->createMock(HttpKernelInterface::class);
        $this->request = new Request();
        $this->event = new ControllerEvent($this->httpKernel, fn() => null, $this->request, null);
        $this->urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $this->turboframeOnlyListener = new TurboframeOnlyListener($this->urlGenerator);
    }

    #[PU\Test]
    public function hasNotAttribute(): void
    {
        $this->httpKernel->expects($this->never())->method($this->anything());
        $controller = \Closure::fromCallable(self::class . '::withoutAttribute');
        $this->event->setController($controller);
        $this->urlGenerator->expects($this->never())->method($this->anything());

        $this->turboframeOnlyListener->onKernelController($this->event);

        $this->assertSame($controller, $this->event->getController());
    }

    #[PU\Test]
    public function isTurboFrame(): void
    {
        $this->httpKernel->expects($this->never())->method($this->anything());
        $controller = \Closure::fromCallable(self::class . '::withAttribute');
        $this->event->setController($controller);
        $this->request->headers->set('Turbo-Frame', 'true');
        $this->urlGenerator->expects($this->never())->method($this->anything());

        $this->turboframeOnlyListener->onKernelController($this->event);

        $this->assertSame($controller, $this->event->getController());
    }

    #[PU\Test]
    public function redirect(): void
    {
        $this->httpKernel->expects($this->never())->method($this->anything());
        $controller = \Closure::fromCallable(self::class . '::withAttribute');
        $this->event->setController($controller);
        $this->urlGenerator->expects($this->once())->method('generate')->with('route');

        $this->turboframeOnlyListener->onKernelController($this->event);

        $this->assertNotSame($controller, $this->event->getController());
    }

    #[TurboframeOnly('route')]
    public function withAttribute(): void {}

    public function withoutAttribute(): void {}
}
