<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\DTO\ErrorData;
use App\DTO\FormData;
use App\DTO\LogListData;
use App\DTO\RepoCreateData;
use App\Service\CoreInteractInterface;
use App\Service\ValidationHandler;
use PHPUnit\Framework\Attributes as PU;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class ValidationHandlerTest extends TestCase
{
    private RepoCreateData $formData;
    private LogListData $queryData;
    private Request $request;
    private RequestStack $requestStack;
    private ValidatorInterface $validator;
    private CoreInteractInterface $coreInteract;
    private ValidationHandler $validationHandler;

    #[\Override]
    protected function setUp(): void
    {
        $this->formData = new RepoCreateData(null);
        $this->queryData = new LogListData();
        $this->request = new Request();
        $this->requestStack = new RequestStack([$this->request]);
        $this->validator = $this->createMock(ValidatorInterface::class);
        $this->coreInteract = $this->createMock(CoreInteractInterface::class);
        $this->validationHandler = new ValidationHandler($this->requestStack, $this->validator, $this->coreInteract);
    }

    #[PU\Test]
    public function handleFormGetRequest(): void
    {
        $this->request->setMethod('GET');
        $this->validator->expects($this->never())->method($this->anything());
        $this->coreInteract->expects($this->never())->method($this->anything());

        $formData = $this->validationHandler->handleForm($this->formData, 'repoCreate');

        $this->assertInstanceOf(FormData::class, $formData);
        $this->assertSame(false, $formData->proceed);
    }

    #[PU\Test]
    public function handleFormValidationErrors(): void
    {
        $this->request->setMethod('POST');
        $violation1 = new ConstraintViolation('error1', null, [], null, 'nameOne', null);
        $violation2 = new ConstraintViolation('error2', null, [], null, 'nameTwo', null);
        $this->validator->expects($this->once())->method('validate')->with($this->formData)->willReturn(new ConstraintViolationList([$violation1, $violation2]));
        $this->coreInteract->expects($this->never())->method($this->anything());

        $formData = $this->validationHandler->handleForm($this->formData, 'repoCreate');

        $this->assertInstanceOf(FormData::class, $formData);
        $this->assertSame(false, $formData->proceed);
        $this->assertArraysAreEqual(['name-one' => new ErrorData('error1'), 'name-two' => new ErrorData('error2')], $formData->errorList);
    }

    #[PU\Test]
    public function handleFormCoreInteractError(): void
    {
        $this->request->setMethod('POST');
        $this->validator->expects($this->once())->method('validate')->with($this->formData)->willReturn(new ConstraintViolationList([]));
        $this->coreInteract->expects($this->once())->method('repoCreate')->with($this->formData)->willReturn(new ErrorData('error'));

        $formData = $this->validationHandler->handleForm($this->formData, 'repoCreate');

        $this->assertInstanceOf(FormData::class, $formData);
        $this->assertSame(false, $formData->proceed);
        $this->assertArraysAreEqual(['_top' => new ErrorData('error')], $formData->errorList);
    }

    #[PU\Test]
    public function handleFormSuccess(): void
    {
        $this->request->setMethod('POST');
        $this->validator->expects($this->once())->method('validate')->with($this->formData)->willReturn(new ConstraintViolationList([]));
        $this->coreInteract->expects($this->once())->method('repoCreate')->with($this->formData)->willReturn(null);

        $formData = $this->validationHandler->handleForm($this->formData, 'repoCreate');

        $this->assertInstanceOf(FormData::class, $formData);
        $this->assertSame(true, $formData->proceed);
        $this->assertArraysAreEqual([], $formData->errorList);
    }

    #[PU\Test]
    public function handleQueryValidationErrors(): void
    {
        $violation1 = new ConstraintViolation('error1', null, [], null, 'nameOne', null);
        $violation2 = new ConstraintViolation('error2', null, [], null, 'nameTwo', null);
        $this->validator->expects($this->once())->method('validate')->with($this->queryData)->willReturn(new ConstraintViolationList([$violation1, $violation2]));
        $this->coreInteract->expects($this->never())->method($this->anything());

        $errorData = $this->validationHandler->handleQuery($this->queryData, 'logList');

        $this->assertInstanceOf(ErrorData::class, $errorData);
        $this->assertSame('error1', $errorData->textKey);
    }

    #[PU\Test]
    public function handleQueryCoreInteractError(): void
    {
        $this->validator->expects($this->once())->method('validate')->with($this->queryData)->willReturn(new ConstraintViolationList([]));
        $this->coreInteract->expects($this->once())->method('logList')->with($this->queryData)->willReturn(new ErrorData('error'));

        $errorData = $this->validationHandler->handleQuery($this->queryData, 'logList');

        $this->assertInstanceOf(ErrorData::class, $errorData);
        $this->assertSame('error', $errorData->textKey);
    }

    #[PU\Test]
    public function handleQuerySuccess(): void
    {
        $this->validator->expects($this->once())->method('validate')->with($this->queryData)->willReturn(new ConstraintViolationList([]));
        $this->coreInteract->expects($this->once())->method('logList')->with($this->queryData)->willReturn(null);

        $errorData = $this->validationHandler->handleQuery($this->queryData, 'logList');

        $this->assertNull($errorData);
    }
}
