<?php

namespace DualMedia\EsLogBundle\Tests\Unit\Normalizer;

use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use DualMedia\EsLogBundle\Model\Change;
use DualMedia\EsLogBundle\Model\Entry;
use DualMedia\EsLogBundle\Model\Value;
use DualMedia\EsLogBundle\Normalizer\DateTimeNormalizer;
use DualMedia\EsLogBundle\Normalizer\EntityNormalizer;
use DualMedia\EsLogBundle\Tests\Resource\TestClass;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use Pkly\ServiceMockHelperTrait;

#[Group('unit')]
#[Group('normalizer')]
#[CoversClass(EntityNormalizer::class)]
class EntityNormalizerTest extends TestCase
{
    use ServiceMockHelperTrait;

    private EntityNormalizer $service;

    protected function setUp(): void
    {
        $this->service = $this->createRealMockedServiceInstance(EntityNormalizer::class);
    }

    #[TestWith([new TestClass(1),1,true])]
    #[TestWith([null,'a'])]
    #[TestWith([null,1])]
    public function testNormalize(
        object|null $expected,
        mixed $value,
        string $field = 'test',
        bool $hasObjectManager = true
    ): void
    {
        $this->getMockedService(ManagerRegistry::class)
            ->expects($this->once())
            ->method('getManagerForClass')
            ->willReturn($hasObjectManager ? $this->createMock(ObjectManager::class) : null);

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