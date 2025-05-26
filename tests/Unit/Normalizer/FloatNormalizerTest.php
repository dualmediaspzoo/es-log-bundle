<?php

namespace DualMedia\EsLogBundle\Tests\Unit\Normalizer;

use DualMedia\EsLogBundle\Model\Entry;
use DualMedia\EsLogBundle\Normalizer\BoolNormalizer;
use DualMedia\EsLogBundle\Normalizer\FloatNormalizer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use Pkly\ServiceMockHelperTrait;

#[Group('unit')]
#[Group('normalizer')]
#[CoversClass(FloatNormalizer::class)]
class FloatNormalizerTest extends TestCase
{
    use ServiceMockHelperTrait;

    private FloatNormalizer $service;

    protected function setUp(): void
    {
        $this->service = $this->createRealMockedServiceInstance(FloatNormalizer::class);
    }

    #[TestWith([1.1,1.1])]
    #[TestWith([2.23,2.23])]
    #[TestWith([null,'a'])]
    #[TestWith([null,1])]
    public function testNormalize(
        float|null $expected,
        mixed $value,
        string $field = 'test'
    ): void
    {
        $result = $this->service->normalize($this->createMock(Entry::class),$field,$value);
        $this->assertSame($expected,$result?->value);
    }
}