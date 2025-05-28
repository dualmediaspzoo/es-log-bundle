<?php

namespace DualMedia\EsLogBundle\Tests\Unit\Normalizer;

use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use DualMedia\EsLogBundle\Model\Entry;
use DualMedia\EsLogBundle\Model\Value;
use DualMedia\EsLogBundle\Normalizer\EntityNormalizer;
use DualMedia\EsLogBundle\Tests\Resource\TestClass;
use DualMedia\EsLogBundle\Tests\Traits\Unit\DenormalizerTestCaseTrait;
use DualMedia\EsLogBundle\Tests\Traits\Unit\NormalizerTestCaseTrait;
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
    use NormalizerTestCaseTrait;
    use DenormalizerTestCaseTrait;

    private EntityNormalizer $service;

    protected function setUp(): void
    {
        $this->service = $this->createRealMockedServiceInstance(EntityNormalizer::class);
    }

    #[TestWith([1, new TestClass(1)])]
    #[TestWith([7, new TestClass(7)])]
    #[TestWith([null, new TestClass(1), false])]
    public function testNormalize(
        int|null $expected,
        mixed $value,
        bool $hasObjectManager = true,
        string $field = 'test'
    ): void {
        $this->getMockedService(ManagerRegistry::class)
            ->expects(static::atMost(1))
            ->method('getManagerForClass')
            ->willReturn($hasObjectManager ? $this->createMock(ObjectManager::class) : null);

        $result = $this->service->normalize($this->createMock(Entry::class), $field, $value);
        static::assertSame($expected, $result?->value);
    }

    #[TestWith([1, new TestClass(1), TestClass::class])]
    #[TestWith([null, new TestClass(1), TestClass::class, false])]
    public function testDenormalize(
        int|null $expected,
        mixed $value,
        string $type,
        bool $isEntity = true,
        string $field = 'test'
    ): void {
        $this->getMockedService(ManagerRegistry::class)
            ->expects(static::atMost(1))
            ->method('getRepository')
            ->with($type);

        $result = $this->service->denormalize($this->createMock(Entry::class), $field, new Value($value, metadata: ['isEntity' => $isEntity], type: $type));
        static::assertSame($expected, $result?->value->value->getId());
    }

    /**
     * @return iterable<mixed>
     */
    public static function provideNormalizerNonSupportedCases(): iterable
    {
        yield ['a'];
        yield [1];
    }

    /**
     * @return iterable<mixed>
     */
    public static function provideDenormalizerNonSupportedCases(): iterable
    {
        yield ['a'];
        yield [1];
    }
}
