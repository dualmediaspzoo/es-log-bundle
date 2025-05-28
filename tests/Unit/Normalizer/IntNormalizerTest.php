<?php

namespace DualMedia\EsLogBundle\Tests\Unit\Normalizer;

use DualMedia\EsLogBundle\Model\Entry;
use DualMedia\EsLogBundle\Model\Value;
use DualMedia\EsLogBundle\Normalizer\IntNormalizer;
use DualMedia\EsLogBundle\Tests\Traits\Unit\NormalizerTestCaseTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use Pkly\ServiceMockHelperTrait;

#[Group('unit')]
#[Group('normalizer')]
#[CoversClass(IntNormalizer::class)]
class IntNormalizerTest extends TestCase
{
    use ServiceMockHelperTrait;
    use NormalizerTestCaseTrait;

    protected IntNormalizer $service;

    protected function setUp(): void
    {
        $this->service = $this->createRealMockedServiceInstance(IntNormalizer::class);
    }

    #[TestWith([1, 1])]
    #[TestWith([100, 100])]
    public function testNormalize(
        int|null $expected,
        mixed $value,
        string $field = 'test'
    ): void {
        $result = $this->service->normalize($this->createMock(Entry::class), $field, $value);
        static::assertInstanceOf(Value::class, $result);
        static::assertSame($expected, $result->value);
    }

    /**
     * @return iterable<mixed>
     */
    public static function provideNormalizerNonSupportedCases(): iterable
    {
        yield ['a'];
        yield [1.5];
    }

    /**
     * @return iterable<mixed>
     */
    public static function provideDenormalizerNonSupportedCases(): iterable
    {
        yield ['a'];
        yield [1.5];
    }
}
