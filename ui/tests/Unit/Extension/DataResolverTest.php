<?php

declare(strict_types=1);

namespace App\Tests\Unit\Attribute;

use App\DTO\RepoRenameData;
use App\Extension\DataResolver;
use PHPUnit\Framework\Attributes as PU;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\ObjectMapper\ObjectMapper;
use Symfony\Component\ObjectMapper\ObjectMapperInterface;

final class DataResolverTest extends TestCase
{
    private Request $request;
    private ArgumentMetadata $argument;
    private ObjectMapperInterface $objectMapper;
    private DataResolver $dataResolver;

    #[\Override]
    protected function setUp(): void
    {
        $this->request = new Request();
        $this->argument = new ArgumentMetadata('data', RepoRenameData::class, false, false, null);
        $this->objectMapper = new ObjectMapper();
        $this->dataResolver = new DataResolver($this->objectMapper);
    }

    #[PU\Test]
    public function resolveGetRequest(): void
    {
        $this->request->setMethod('GET');
        $this->request->query->set('old-name', 'repo-1');
        $this->request->query->set('new-name', 'repo-2');

        $data = $this->dataResolver->resolve($this->request, $this->argument);

        $this->assertCount(1, $data);
        $this->assertArrayHasKey(0, $data);
        $this->assertInstanceOf(RepoRenameData::class, $data[0]);
        $this->assertEquals(new RepoRenameData('repo-1', 'repo-2'), $data[0]);
    }

    #[PU\Test]
    public function resolvePostRequest(): void
    {
        $this->request->setMethod('POST');
        $this->request->request->set('old-name', 'repo-1');
        $this->request->request->set('new-name', 'repo-2');

        $data = $this->dataResolver->resolve($this->request, $this->argument);

        $this->assertCount(1, $data);
        $this->assertArrayHasKey(0, $data);
        $this->assertInstanceOf(RepoRenameData::class, $data[0]);
        $this->assertEquals(new RepoRenameData('repo-1', 'repo-2'), $data[0]);
    }
}
