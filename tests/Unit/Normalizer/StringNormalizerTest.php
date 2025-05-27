<?php

namespace DualMedia\EsLogBundle\Tests\Unit\Normalizer;

use DualMedia\EsLogBundle\Model\Entry;
use DualMedia\EsLogBundle\Model\Value;
use DualMedia\EsLogBundle\Normalizer\StringNormalizer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\TestWith;
use Pkly\ServiceMockHelperTrait;

#[Group('unit')]
#[Group('normalizer')]
#[CoversClass(StringNormalizer::class)]
class StringNormalizerTest extends AbstractNormalizerTestCase
{
    use ServiceMockHelperTrait;

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

    public static function provideNonSupportedCases(): iterable
    {
        yield [null];
        yield [1];
    }
}
