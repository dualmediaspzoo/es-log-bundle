<?php

namespace DualMedia\EsLogBundle\Tests\Unit\Normalizer;

use DateTimeImmutable;
use DateTimeInterface;
use DualMedia\EsLogBundle\Model\Entry;
use DualMedia\EsLogBundle\Model\Value;
use DualMedia\EsLogBundle\Normalizer\BoolNormalizer;
use DualMedia\EsLogBundle\Normalizer\DateTimeNormalizer;
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

    private DateTimeNormalizer $service;

    protected function setUp(): void
    {
        $this->service = $this->createRealMockedServiceInstance(DateTimeNormalizer::class);
    }

    #[TestWith(['2025-05-26T07:28:18+00:00',new \DateTime('2025-05-26T07:28:18+00:00')])]
    #[TestWith([null,'a'])]
    #[TestWith([null,1])]
    public function testNormalize(
        string|null $expected,
        mixed $value,
        string $field = 'test'
    ): void
    {
        $result = $this->service->normalize($this->createMock(Entry::class),$field,$value);
        $this->assertSame($expected,$result?->value);
    }

    #[TestWith(['2025-05-26 07:28:18',new \DateTime('2025-05-26 07:28:18'),\DateTimeImmutable::class])]
    #[TestWith([null,'a','string'])]
    #[TestWith([null,1,'integer'])]
    public function testDenormalize(
        string|null $expected,
        mixed $value,
        string $type,
        string $field = 'test'
    ): void
    {
        $result = $this->service->denormalize($this->createMock(Entry::class),$field,new Value($value,type: $type));
        $this->assertSame($expected,$result?->value?->value?->format('Y-m-d H:i:s'));
    }
}