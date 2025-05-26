<?php

namespace DualMedia\EsLogBundle\Tests\Unit\Normalizer;

use BackedEnum;
use Doctrine\Persistence\ManagerRegistry;
use DualMedia\EsLogBundle\Model\Entry;
use DualMedia\EsLogBundle\Model\Value;
use DualMedia\EsLogBundle\Normalizer\EntityNormalizer;
use DualMedia\EsLogBundle\Normalizer\EntryNormalizer;
use DualMedia\EsLogBundle\Normalizer\EnumNormalizer;
use DualMedia\EsLogBundle\Tests\Resource\TestEnum;
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

    private EnumNormalizer $service;

    protected function setUp(): void
    {
        $this->service = $this->createRealMockedServiceInstance(EnumNormalizer::class);
    }

    #[TestWith(['b',TestEnum::B])]
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

    #[TestWith(['a',true,TestEnum::A,TestEnum::class])]
    #[TestWith([null,false,TestEnum::A,TestEnum::class])]
    #[TestWith([null,false,'a','string'])]
    #[TestWith([null,false,1,'integer'])]
    public function testDenormalize(
        string|null $expected,
        bool $isBackedEnum,
        mixed $value,
        string $type,
        string $field = 'test'
    ): void
    {

        $result = $this->service->denormalize($this->createMock(Entry::class),$field,new Value($value,metadata:['isBackedEnum' => $isBackedEnum] ,type: $type));
        $this->assertSame($expected,$result?->value->value->value);
    }
}