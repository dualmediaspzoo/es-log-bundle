<?php

namespace DualMedia\EsLogBundle\Tests\Unit\Normalizer;

use DualMedia\EsLogBundle\Model\Entry;
use DualMedia\EsLogBundle\Normalizer\EnumNormalizer;
use DualMedia\EsLogBundle\Normalizer\FloatNormalizer;
use DualMedia\EsLogBundle\Normalizer\NullNormalizer;
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

    private NullNormalizer $service;

    protected function setUp(): void
    {
        $this->service = $this->createRealMockedServiceInstance(NullNormalizer::class);
    }

    #[TestWith([true])]
    #[TestWith([false])]
    #[TestWith(['a'])]
    #[TestWith([null])]
    public function testNormalize(
        mixed $value,
        string $field = 'test'
    ): void
    {
        $result = $this->service->normalize($this->createMock(Entry::class),$field,$value);
        $this->assertNull($result?->value);
    }
}