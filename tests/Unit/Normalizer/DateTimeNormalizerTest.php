<?php

namespace DualMedia\EsLogBundle\Tests\Unit\Normalizer;

use DualMedia\EsLogBundle\Interface\NormalizerInterface;
use DualMedia\EsLogBundle\Model\Entry;
use DualMedia\EsLogBundle\Model\Value;
use DualMedia\EsLogBundle\Normalizer\DateTimeNormalizer;
use DualMedia\EsLogBundle\Tests\Traits\Unit\DenormalizerTestCaseTrait;
use DualMedia\EsLogBundle\Tests\Traits\Unit\NormalizerTestCaseTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use Pkly\ServiceMockHelperTrait;

#[Group('unit')]
#[Group('normalizer')]
#[CoversClass(DateTimeNormalizer::class)]
class DateTimeNormalizerTest extends TestCase
{
    use ServiceMockHelperTrait;
    use NormalizerTestCaseTrait;
    use DenormalizerTestCaseTrait;

    /**
     * @var DateTimeNormalizer
     */
    protected NormalizerInterface $service;

    protected function setUp(): void
    {
        $this->service = $this->createRealMockedServiceInstance(DateTimeNormalizer::class);
    }

    #[TestWith(['2025-05-26T07:28:18+00:00', new \DateTime('2025-05-26T07:28:18+00:00')])]
    public function testNormalize(
        string|null $expected,
        mixed $value,
        string $field = 'test'
    ): void {
        $result = $this->service->normalize($this->createMock(Entry::class), $field, $value);
        static::assertInstanceOf(Value::class, $result);
        static::assertSame($expected, $result->value);
    }

    #[TestWith(['2025-05-26 07:28:18', new \DateTime('2025-05-26 07:28:18'), \DateTimeImmutable::class])]
    public function testDenormalize(
        string|null $expected,
        mixed $value,
        string $type,
        string $field = 'test'
    ): void {
        $result = $this->service->denormalize($this->createMock(Entry::class), $field, new Value($value, type: $type));
        static::assertSame($expected, $result->value->value->format('Y-m-d H:i:s'));
    }

    /**
     * @return iterable<mixed>
     */
    public static function provideNonSupportedCases(): iterable
    {
        yield ['a'];
        yield [1];
    }
}
