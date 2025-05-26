<?php

namespace DualMedia\EsLogBundle\Tests\Unit\Normalizer;

use DualMedia\EsLogBundle\Model\Entry;
use DualMedia\EsLogBundle\Normalizer\EnumNormalizer;
use DualMedia\EsLogBundle\Normalizer\FloatNormalizer;
use DualMedia\EsLogBundle\Normalizer\IntNormalizer;
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

    private IntNormalizer $service;

    protected function setUp(): void
    {
        $this->service = $this->createRealMockedServiceInstance(IntNormalizer::class);
    }

    #[TestWith([1,1])]
    #[TestWith([100,100])]
    #[TestWith([null,'a'])]
    #[TestWith([null,1.5])]
    public function testNormalize(
        int|null $expected,
        mixed $value,
        string $field = 'test'
    ): void
    {
        $result = $this->service->normalize($this->createMock(Entry::class),$field,$value);
        $this->assertSame($expected,$result?->value);
    }
}