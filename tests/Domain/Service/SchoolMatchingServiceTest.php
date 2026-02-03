<?php

namespace App\Tests\Domain\Service;

use App\Domain\School;
use App\Domain\SchoolRepositoryInterface;
use App\Domain\Service\SchoolMatchingService;
use PHPUnit\Framework\TestCase;

class SchoolMatchingServiceTest extends TestCase
{
    private SchoolMatchingService $matcher;
    private $repository;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(SchoolRepositoryInterface::class);
        $this->matcher = new SchoolMatchingService($this->repository);
    }

    public function testMatchExactName(): void
    {
        $school = new School('Test School', [], 'City', 'Type');
        $this->repository->method('findAll')->willReturn([$school]);

        $result = $this->matcher->match('Test School');

        $this->assertSame($school, $result);
    }

    public function testMatchCaseInsensitive(): void
    {
        $school = new School('Test School', [], 'City', 'Type');
        $this->repository->method('findAll')->willReturn([$school]);

        $result = $this->matcher->match('test school');

        $this->assertSame($school, $result);
    }

    public function testMatchExactAlias(): void
    {
        $school = new School('Official', ['Alias1', 'Alias2'], 'City', 'Type');
        $this->repository->method('findAll')->willReturn([$school]);

        $result = $this->matcher->match('Alias2');

        $this->assertSame($school, $result);
    }

    public function testMatchFuzzy(): void
    {
        $school = new School('Long Official Name', [], 'City', 'Type');
        $this->repository->method('findAll')->willReturn([$school]);

        // "Long Oficial Name" (1 char missing)
        $result = $this->matcher->match('Long Oficial Name');

        $this->assertSame($school, $result);
    }

    public function testMatchFuzzyAlias(): void
    {
        $school = new School('Data', ['VeryUniqueAlias'], 'City', 'Type');
        $this->repository->method('findAll')->willReturn([$school]);

        // "VeryUniqeAlias" (1 char missing)
        $result = $this->matcher->match('VeryUniqeAlias');

        $this->assertSame($school, $result);
    }

    public function testNoMatchForGibberish(): void
    {
        $school = new School('School One', [], 'City', 'Type');
        $this->repository->method('findAll')->willReturn([$school]);

        $result = $this->matcher->match('Completely Different String');

        $this->assertNull($result);
    }
}
