<?php

namespace DualMedia\EsLogBundle\Tests\Unit\Normalizer;

use DualMedia\EsLogBundle\Model\Entry;
use DualMedia\EsLogBundle\Model\Value;
use DualMedia\EsLogBundle\Normalizer\BoolNormalizer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\TestWith;
use Pkly\ServiceMockHelperTrait;

#[Group('unit')]
#[Group('normalizer')]
#[CoversClass(BoolNormalizer::class)]
class BoolNormalizerTest extends AbstractNormalizerTestCase
{
    use ServiceMockHelperTrait;

    protected BoolNormalizer $service;

    protected function setUp(): void
    {
        $this->service = $this->createRealMockedServiceInstance(BoolNormalizer::class);
    }

    #[TestWith([true, true])]
    #[TestWith([false, false])]
    public function testNormalize(
        bool|null $expected,
        mixed $value,
        string $field = 'test'
    ): void {
        $result = $this->service->normalize($this->createMock(Entry::class), $field, $value);
        static::assertInstanceOf(Value::class, $result);
        static::assertSame($expected, $result->value);
    }

    public static function provideNonSupportedCases(): iterable
    {
        yield ['a'];
        yield [1];
    }
}
