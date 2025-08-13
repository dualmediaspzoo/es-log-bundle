<?php

declare(strict_types=1);

namespace DualMedia\EsLogBundle\Tests\Unit;

use DualMedia\EsLogBundle\LogCreator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Pkly\ServiceMockHelperTrait;

#[Group('unit')]
#[CoversClass(LogCreator::class)]
class LogCreatorTest extends TestCase
{
    use ServiceMockHelperTrait;

    private LogCreator $service;

    protected function setUp(): void
    {
        $this->service = $this->createRealMockedServiceInstance(LogCreator::class);
    }
}
