<?php

namespace DualMedia\EsLogBundle\Tests\Unit\Normalizer;

use DualMedia\EsLogBundle\Model\Entry;
use DualMedia\EsLogBundle\Model\Value;
use DualMedia\EsLogBundle\Normalizer\NullNormalizer;
use DualMedia\EsLogBundle\Tests\Traits\Unit\NormalizerTestCaseTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use Pkly\ServiceMockHelperTrait;

#[Group('unit')]
#[Group('normalizer')]
#[CoversClass(NullNormalizer::class)]
class NullNormalizerTest extends TestCase
{
    use ServiceMockHelperTrait;
    use NormalizerTestCaseTrait;

    protected NullNormalizer $service;

    protected function setUp(): void
    {
        $this->service = $this->createRealMockedServiceInstance(NullNormalizer::class);
    }

    #[TestWith([null])]
    public function testNormalize(
        mixed $value,
        string $field = 'test'
    ): void {
        $result = $this->service->normalize($this->createMock(Entry::class), $field, $value);
        static::assertInstanceOf(Value::class, $result);
        static::assertNull($result->value);
    }

    /**
     * @return iterable<mixed>
     */
    public static function provideNormalizerNonSupportedCases(): iterable
    {
        yield [true];
        yield [false];
        yield ['a'];
    }

    /**
     * @return iterable<mixed>
     */
    public static function provideDenormalizerNonSupportedCases(): iterable
    {
        yield [true];
        yield [false];
        yield ['a'];
    }
}
