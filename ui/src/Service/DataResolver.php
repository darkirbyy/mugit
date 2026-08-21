<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\RepoDeleteData;
use App\DTO\RepoRenameData;
use Symfony\Component\HttpFoundation\Request;
use Override;
use RuntimeException;
use Symfony\Component\HttpKernel\Attribute\AsTargetedValueResolver;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

#[AsTargetedValueResolver('data')]
class DataResolver implements ValueResolverInterface
{
    #[Override]
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        if ($argument->getType() === RepoRenameData::class) {
            if ($request->getMethod() == 'GET') {
                $oldName = $request->query->get('oldName');
                $newName = '';
            } else {
                $oldName = $request->getPayload()->get('old-name');
                $newName = $request->getPayload()->get('new-name');
            }

            if ($oldName === null) {
                throw new RuntimeException('TODO');
            }

            return [new RepoRenameData($oldName, $newName)];
        } elseif ($argument->getType() === RepoDeleteData::class) {
            if ($request->getMethod() == 'GET') {
                $name = $request->query->get('name');
            } else {
                $name = $request->getPayload()->get('name');
            }

            if ($name === null) {
                throw new RuntimeException('TODO');
            }

            return [new RepoDeleteData($name)];
        } else {
            return [];
        }
    }
}
