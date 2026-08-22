<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\FormOutputData;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\String\ByteString;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class FormHandler
{
    public function __construct(private RequestStack $requestStack, private ValidatorInterface $validator, private CoreInteractInterface $coreInteract) {}

    public function handle(mixed $data, string $coreMethod): FormOutputData
    {
        // Pass if not POST request (i.e. form not submitted)
        if ($this->requestStack->getMainRequest()->getMethod() != 'POST') {
            return new FormOutputData(false);
        }

        // Check form validation errors
        $validationErrorList = $this->validator->validate($data);
        if ($validationErrorList->count() > 0) {
            $formErrorList = [];
            foreach ($validationErrorList as $validationError) {
                $camelName = $validationError->getPropertyPath();
                $kebabName = (new ByteString($camelName))->kebab()->toString();
                $formErrorList[$kebabName] = $validationError->getMessage();
            }

            return new FormOutputData(false, $formErrorList);
        }

        // Call the core and check the errors
        $coreErrorData = $this->coreInteract->$coreMethod($data);
        if ($coreErrorData !== null) {
            return new FormOutputData(false, ['_top' => $coreErrorData->textKey]);
        }

        return new FormOutputData(true);
    }
}
