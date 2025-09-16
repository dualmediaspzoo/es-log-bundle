<?php

namespace DualMedia\EsLogBundle\Tests\Unit;

use DualMedia\EsLogBundle\Enum\ActionEnum;
use DualMedia\EsLogBundle\Enum\TypeEnum;
use DualMedia\EsLogBundle\LogStorage;
use DualMedia\EsLogBundle\Model\Entry;
use DualMedia\EsLogBundle\Tests\Resource\TestClass;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use Pkly\ServiceMockHelperTrait;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

#[Group('unit')]
#[CoversClass(LogStorage::class)]
class LogStorageTest extends TestCase
{
    use ServiceMockHelperTrait;

    private LogStorage $service;

    protected function setUp(): void
    {
        $this->service = $this->createRealMockedServiceInstance(LogStorage::class);
    }

    /**
     * @param list<array{enum: ActionEnum, id: int}> $entryData
     */
    #[TestWith([
        [
            [
                'enum' => ActionEnum::Update,
                'id' => 12,
            ],
        ],
    ])]
    #[TestWith([
        [
            [
                'enum' => ActionEnum::Create,
                'id' => 12,
            ],
            [
                'enum' => ActionEnum::Update,
                'id' => 13,
            ],
            [
                'enum' => ActionEnum::Remove,
                'id' => 14,
            ],
        ],
    ])]
    public function testAppendAndProcess(
        array $entryData
    ): void {
        $expectedEntries = [];

        foreach ($entryData as $data) {
            $entryWithId = $this->createMock(Entry::class);
            $entry = $this->createMock(Entry::class);
            $entry->method('getAction')
                ->willReturn($data['enum']);
            $entry->method('withId')
                ->willReturn($entryWithId);
            $entry->method('getType')
                ->willReturn(TypeEnum::Automatic);
            $object = $this->createMock(TestClass::class);
            $object->method('getId')
                ->willReturn($data['id']);

            $expectedEntries[] = $entryWithId;
            $this->service->append($entry, $object);
        }

        $this->getMockedService(EventDispatcherInterface::class)
            ->method('dispatch')
            ->willReturnArgument(0);

        $this->service->process();

        static::assertEquals($expectedEntries, $this->service->getEntries());
    }
}
