<?php

declare(strict_types=1);

namespace App\Service;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsTargetedValueResolver;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\ObjectMapper\ObjectMapperInterface;

/**
 * Resolver that allows to autowire DTO into controllers arguments,
 * trying to create and populate them using query string or payload.
 * Must be specifically targetted by adding `#[ValueResolver('data')]`.
 */
#[AsTargetedValueResolver('data')]
class DataResolver implements ValueResolverInterface
{
    public function __construct(private ObjectMapperInterface $objectMapper) {}

    #[\Override]
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        // Retrieve the data from query string or from payload depending on method
        if ('GET' == $request->getMethod()) {
            $sourceKebabed = $request->query->all();
        } else {
            $sourceKebabed = $request->getPayload()->all();
        }

        // Camelize all variables names
        $sourceCamelized = [];
        foreach ($sourceKebabed as $name => $value) {
            $sourceCamelized[$this->camelize($name)] = $value;
        }

        // Map to the required DTO
        $data = $this->objectMapper->map((object) $sourceCamelized, $argument->getType());

        return [$data];
    }

    /**
     * Transform a kebab-case variable name into a camelCase one
     */
    private function camelize(string $name): string
    {
        return lcfirst(str_replace('-', '', ucwords($name, '-')));
    }
}
