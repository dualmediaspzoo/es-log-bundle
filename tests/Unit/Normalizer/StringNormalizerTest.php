<?php

namespace DualMedia\EsLogBundle\Tests\Unit\Normalizer;

use DualMedia\EsLogBundle\Model\Entry;
use DualMedia\EsLogBundle\Model\Value;
use DualMedia\EsLogBundle\Normalizer\StringNormalizer;
use DualMedia\EsLogBundle\Tests\Traits\Unit\NormalizerTestCaseTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use Pkly\ServiceMockHelperTrait;

#[Group('unit')]
#[Group('normalizer')]
#[CoversClass(StringNormalizer::class)]
class StringNormalizerTest extends TestCase
{
    use ServiceMockHelperTrait;
    use NormalizerTestCaseTrait;

    protected StringNormalizer $service;

    protected function setUp(): void
    {
        $this->service = $this->createRealMockedServiceInstance(StringNormalizer::class);
    }

    #[TestWith(['abc', 'abc'])]
    #[TestWith(['', ''])]
    public function testNormalize(
        string|null $expected,
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
        yield [null];
        yield [1];
    }

    /**
     * @return iterable<mixed>
     */
    public static function provideDenormalizerNonSupportedCases(): iterable
    {
        yield [null];
        yield [1];
    }
}
