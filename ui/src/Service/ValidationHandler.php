<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\ErrorData;
use App\DTO\FormData;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\String\ByteString;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Very simplified version of the Symfony form handler, sufficient for this app.
 */
class ValidationHandler
{
    public function __construct(private RequestStack $requestStack, private ValidatorInterface $validator, private CoreInteractInterface $coreInteract) {}

    public function handleForm(?object $data, string $coreMethod): FormData
    {
        // Pass if not POST request (i.e. form not submitted)
        if ('POST' != $this->requestStack->getMainRequest()->getMethod()) {
            return new FormData(false);
        }

        // Check form validation errors
        $validationErrorList = null != $data ? $this->validator->validate($data) : new ConstraintViolationList();
        if ($validationErrorList->count() > 0) {
            $formErrorList = [];
            foreach ($validationErrorList as $validationError) {
                $camelName = $validationError->getPropertyPath();
                $kebabName = (new ByteString($camelName))->kebab()->toString();
                $formErrorList[$kebabName] = new ErrorData($validationError->getMessage());
            }

            return new FormData(false, $formErrorList);
        }

        // Call the core and check the errors
        $errorData = $this->coreInteract->$coreMethod($data);
        if (null !== $errorData) {
            return new FormData(false, ['_top' => $errorData]);
        }

        return new FormData(true);
    }

    public function handleQuery(?object $data, string $coreMethod): ?ErrorData
    {
        // Check validation errors
        $validationErrorList = null != $data ? $this->validator->validate($data) : new ConstraintViolationList();
        if ($validationErrorList->count() > 0) {
            return new ErrorData($validationErrorList->get(0)->getMessage());
        }

        // Call the core
        return $this->coreInteract->$coreMethod($data);
    }
}
