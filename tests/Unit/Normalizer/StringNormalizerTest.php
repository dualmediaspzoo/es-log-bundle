<?php

namespace DualMedia\EsLogBundle\Tests\Unit\Normalizer;

use DualMedia\EsLogBundle\Model\Entry;
use DualMedia\EsLogBundle\Normalizer\EnumNormalizer;
use DualMedia\EsLogBundle\Normalizer\FloatNormalizer;
use DualMedia\EsLogBundle\Normalizer\StringNormalizer;
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

    private StringNormalizer $service;

    protected function setUp(): void
    {
        $this->service = $this->createRealMockedServiceInstance(StringNormalizer::class);
    }

    #[TestWith(['abc','abc'])]
    #[TestWith(['',''])]
    #[TestWith([null,null])]
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
}