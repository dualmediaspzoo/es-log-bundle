<?php

namespace DualMedia\EsLogBundle\Tests\Unit;

use DualMedia\EsLogBundle\Enum\ActionEnum;
use DualMedia\EsLogBundle\LogStorage;
use DualMedia\EsLogBundle\Model\Entry;
use DualMedia\EsLogBundle\Tests\Resource\TestClass;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use Pkly\ServiceMockHelperTrait;

#[Group('unit')]
#[CoversClass(LogStorage::class)]
class LogStorageTest extends TestCase
{
    use ServiceMockHelperTrait;

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
        $logStorage = new LogStorage();

        $expectedEntries = [];

        foreach ($entryData as $data) {
            $entryWithId = $this->createMock(Entry::class);
            $entry = $this->createMock(Entry::class);
            $entry->method('getAction')
                ->willReturn($data['enum']);
            $entry->method('withId')
                ->willReturn($entryWithId);
            $object = $this->createMock(TestClass::class);
            $object->method('getId')
                ->willReturn($data['id']);

            $expectedEntries[] = $entryWithId;
            $logStorage->append($entry, $object);
        }

        $logStorage->process();

        static::assertEquals($expectedEntries, $logStorage->getEntries());
    }
}
