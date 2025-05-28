<?php

namespace DualMedia\EsLogBundle\Tests\Unit;

use Doctrine\ORM\UnitOfWork;
use DualMedia\EsLogBundle\ChangeSetProvider;
use DualMedia\EsLogBundle\Metadata\ConfigProvider;
use DualMedia\EsLogBundle\Tests\Resource\TestClass;
use DualMedia\EsLogBundle\Tests\Resource\TestEnum;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Pkly\ServiceMockHelperTrait;

/**
 * @phpstan-import-type MetadataConfig from ConfigProvider
 *
 * @phpstan-type Expected array<string,list<mixed>>
 * @phpstan-type Changes array<string,list<mixed>>
 */
#[Group('unit')]
#[CoversClass(ChangeSetProvider::class)]
class ChangeSetProviderTest extends TestCase
{
    use ServiceMockHelperTrait;

    protected ChangeSetProvider $service;

    protected function setUp(): void
    {
        $this->service = $this->createRealMockedServiceInstance(ChangeSetProvider::class);
    }

    /**
     * @param Expected $expected
     * @param MetadataConfig $config
     * @param Changes $changes
     */
    #[DataProvider('provideDataProvider')]
    public function testProvide(
        array $expected,
        array $config,
        array $changes = []
    ): void {
        $object = $this->createMock(TestClass::class);

        $uow = $this->createMock(UnitOfWork::class);
        $uow->expects(static::once())
            ->method('getEntityChangeSet')
            ->with($object)
            ->willReturn($changes);

        $result = $this->service->provide(
            $uow,
            $config,
            $object
        );

        foreach ($result as $key => $value) {
            static::assertSame(
                $expected[$key][0],
                $value->getTo()
            );
            static::assertSame(
                $expected[$key][1],
                $value->getFrom()
            );
        }
    }

    /**
     * @return iterable<array{Expected, MetadataConfig, Changes}>
     */
    public static function provideDataProvider(): iterable
    {
        yield [
            [
                'field1' => [
                    TestEnum::B,
                    TestEnum::A,
                ],
            ],
            [
                'trackCreate' => true,
                'trackUpdate' => true,
                'trackDelete' => true,
                'properties' => [
                    'field1' => [
                        'enumClass' => TestEnum::class,
                    ],
                ],
            ],
            [
                'field1' => ['a', 'b'],
            ],
        ];

        yield [
            [
                'field1' => [
                    TestEnum::B,
                    TestEnum::A,
                ],
                'field2' => [
                    TestEnum::A,
                    TestEnum::B,
                ],
            ],
            [
                'trackCreate' => true,
                'trackUpdate' => true,
                'trackDelete' => false,
                'properties' => [
                    'field1' => [
                        'enumClass' => TestEnum::class,
                    ],
                    'field2' => [
                        'enumClass' => TestEnum::class,
                    ],
                ],
            ],
            [
                'field1' => ['a', 'b'],
                'field2' => ['b', 'a'],
            ],
        ];

        yield [
            [
                'field1' => [
                    null,
                    'test-string',
                ],
                'field2' => [
                    1,
                    1.1,
                ],
            ],
            [
                'trackCreate' => true,
                'trackUpdate' => true,
                'trackDelete' => false,
                'properties' => [
                    'field1' => [],
                    'field2' => [],
                ],
            ],
            [
                'field1' => ['test-string', null],
                'field2' => [1.1, 1],
            ],
        ];
    }
}
