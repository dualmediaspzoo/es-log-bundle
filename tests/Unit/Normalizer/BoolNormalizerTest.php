<?php

namespace DualMedia\EsLogBundle\Tests\Unit\Normalizer;

use DualMedia\EsLogBundle\Model\Entry;
use DualMedia\EsLogBundle\Normalizer\BoolNormalizer;
use DualMedia\EsLogBundle\UserContext;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Pkly\ServiceMockHelperTrait;

#[Group('unit')]
#[Group('normalizer')]
#[CoversClass(BoolNormalizer::class)]
class BoolNormalizerTest extends TestCase
{
    use ServiceMockHelperTrait;

    private BoolNormalizer $service;

    protected function setUp(): void
    {
        $this->service = $this->createRealMockedServiceInstance(BoolNormalizer::class);
    }

    #[TestWith([true,true])]
    #[TestWith([false,false])]
    #[TestWith([null,'a'])]
    #[TestWith([null,1])]
    public function testNormalize(
        bool|null $expected,
        mixed $value,
        string $field = 'test'
    ): void
    {
        $result = $this->service->normalize($this->createMock(Entry::class),$field,$value);
        $this->assertSame($expected,$result?->value);
    }
}