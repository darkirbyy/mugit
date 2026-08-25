<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\DTO\ErrorData;
use App\DTO\FormData;
use App\DTO\RepoCreateData;
use App\Service\CoreInteractInterface;
use App\Service\FormHandler;
use PHPUnit\Framework\Attributes as PU;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class FormHandlerTest extends TestCase
{
    private RepoCreateData $data;
    private Request $request;
    private RequestStack $requestStack;
    private ValidatorInterface $validator;
    private CoreInteractInterface $coreInteract;
    private FormHandler $formHandler;

    #[\Override]
    protected function setUp(): void
    {
        $this->data = new RepoCreateData(null);
        $this->request = new Request();
        $this->requestStack = new RequestStack([$this->request]);
        $this->validator = $this->createMock(ValidatorInterface::class);
        $this->coreInteract = $this->createMock(CoreInteractInterface::class);
        $this->formHandler = new FormHandler($this->requestStack, $this->validator, $this->coreInteract);
    }

    #[PU\Test]
    public function handleGetRequest(): void
    {
        $this->request->setMethod('GET');
        $this->validator->expects($this->never())->method($this->anything());
        $this->coreInteract->expects($this->never())->method($this->anything());

        $formData = $this->formHandler->handle($this->data, 'repoCreate');

        $this->assertInstanceOf(FormData::class, $formData);
        $this->assertSame(false, $formData->proceed);
    }

    #[PU\Test]
    public function handleValidationErrors(): void
    {
        $this->request->setMethod('POST');
        $violation1 = new ConstraintViolation('error1', null, [], null, 'nameOne', null);
        $violation2 = new ConstraintViolation('error2', null, [], null, 'nameTwo', null);
        $this->validator->expects($this->once())->method('validate')->with($this->data)->willReturn(new ConstraintViolationList([$violation1, $violation2]));
        $this->coreInteract->expects($this->never())->method($this->anything());

        $formData = $this->formHandler->handle($this->data, 'repoCreate');

        $this->assertInstanceOf(FormData::class, $formData);
        $this->assertSame(false, $formData->proceed);
        $this->assertArraysAreEqual(['name-one' => new ErrorData('error1'), 'name-two' => new ErrorData('error2')], $formData->errorList);
    }

    #[PU\Test]
    public function handleCoreInteractError(): void
    {
        $this->request->setMethod('POST');
        $this->validator->expects($this->once())->method('validate')->with($this->data)->willReturn(new ConstraintViolationList([]));
        $this->coreInteract->expects($this->once())->method('repoCreate')->with($this->data)->willReturn(new ErrorData('error'));

        $formData = $this->formHandler->handle($this->data, 'repoCreate');

        $this->assertInstanceOf(FormData::class, $formData);
        $this->assertSame(false, $formData->proceed);
        $this->assertArraysAreEqual(['_top' => new ErrorData('error')], $formData->errorList);
    }

    #[PU\Test]
    public function handleSuccess(): void
    {
        $this->request->setMethod('POST');
        $this->validator->expects($this->once())->method('validate')->with($this->data)->willReturn(new ConstraintViolationList([]));
        $this->coreInteract->expects($this->once())->method('repoCreate')->with($this->data)->willReturn(null);

        $formData = $this->formHandler->handle($this->data, 'repoCreate');

        $this->assertInstanceOf(FormData::class, $formData);
        $this->assertSame(true, $formData->proceed);
        $this->assertArraysAreEqual([], $formData->errorList);
    }
}
