<?php

namespace DualMedia\EsLogBundle\Tests\Unit\Normalizer;

use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Persistence\ObjectRepository;
use DualMedia\EsLogBundle\Model\Change;
use DualMedia\EsLogBundle\Model\Entry;
use DualMedia\EsLogBundle\Model\Value;
use DualMedia\EsLogBundle\Normalizer\DateTimeNormalizer;
use DualMedia\EsLogBundle\Normalizer\EntityNormalizer;
use DualMedia\EsLogBundle\Tests\Resource\TestClass;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\MockObject\MockObject;
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

    #[TestWith([1, new TestClass(1)])]
    #[TestWith([7, new TestClass(7)])]
    #[TestWith([null, new TestClass(1),false])]
    #[TestWith([null, 'a'])]
    #[TestWith([null, 1])]
    public function testNormalize(
        int|null $expected,
        mixed $value,
        bool $hasObjectManager = true,
        string $field = 'test'
    ): void
    {
        $this->getMockedService(ManagerRegistry::class)
            ->expects($this->atMost(1))
            ->method('getManagerForClass')
            ->willReturn($hasObjectManager ? $this->createMock(ObjectManager::class) : null);

        $result = $this->service->normalize($this->createMock(Entry::class),$field,$value);
        $this->assertSame($expected,$result?->value);
    }

    #[TestWith([1,new TestClass(1),TestClass::class])]
    #[TestWith([null,new TestClass(1),TestClass::class,false])]
    public function testDenormalize(
        int|null $expected,
        mixed $value,
        string $type,
        bool $isEntity = true,
        string $field = 'test'
    ): void
    {
        $this->getMockedService(ManagerRegistry::class)
            ->expects($this->atMost(1))
            ->method('getRepository')
            ->with($type);

        $result = $this->service->denormalize($this->createMock(Entry::class),$field,new Value($value,metadata:['isEntity' => $isEntity] ,type: $type));
        $this->assertSame($expected,$result?->value->value->getId());
    }
}