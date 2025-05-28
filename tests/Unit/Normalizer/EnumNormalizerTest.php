<?php

namespace DualMedia\EsLogBundle\Tests\Unit\Normalizer;

use DualMedia\EsLogBundle\Model\Entry;
use DualMedia\EsLogBundle\Model\Value;
use DualMedia\EsLogBundle\Normalizer\EnumNormalizer;
use DualMedia\EsLogBundle\Tests\Resource\TestEnum;
use DualMedia\EsLogBundle\Tests\Traits\Unit\DenormalizerTestCaseTrait;
use DualMedia\EsLogBundle\Tests\Traits\Unit\NormalizerTestCaseTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use Pkly\ServiceMockHelperTrait;

#[Group('unit')]
#[Group('normalizer')]
#[CoversClass(EnumNormalizer::class)]
class EnumNormalizerTest extends TestCase
{
    use ServiceMockHelperTrait;
    use NormalizerTestCaseTrait;
    use DenormalizerTestCaseTrait;

    protected EnumNormalizer $service;

    protected function setUp(): void
    {
        $this->service = $this->createRealMockedServiceInstance(EnumNormalizer::class);
    }

    #[TestWith(['b', TestEnum::B])]
    #[TestWith(['a', TestEnum::A])]
    public function testNormalize(
        string $expected,
        mixed $value,
        string $field = 'test'
    ): void {
        $result = $this->service->normalize($this->createMock(Entry::class), $field, $value);
        static::assertInstanceOf(Value::class, $result);
        static::assertSame($expected, $result->value);
    }

    #[TestWith(['a', true, TestEnum::A, TestEnum::class])]
    #[TestWith([null, false, TestEnum::A, TestEnum::class])]
    public function testDenormalize(
        string|null $expected,
        bool $isBackedEnum,
        mixed $value,
        string $type,
        string $field = 'test'
    ): void {
        $result = $this->service->denormalize($this->createMock(Entry::class), $field, new Value($value, metadata: ['isBackedEnum' => $isBackedEnum], type: $type));
        static::assertSame($expected, $result?->value->value->value);
    }

    /**
     * @return iterable<mixed>
     */
    public static function provideNormalizerNonSupportedCases(): iterable
    {
        yield ['a'];
        yield [1];
    }

    /**
     * @return iterable<mixed>
     */
    public static function provideDenormalizerNonSupportedCases(): iterable
    {
        yield ['a'];
        yield [1];
    }
}
