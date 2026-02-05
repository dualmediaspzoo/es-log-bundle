<?php

namespace DualMedia\EsLogBundle\Tests\Unit;

use DualMedia\Common\Interface\IdentifiableInterface;
use DualMedia\EsLogBundle\EntryCreator;
use DualMedia\EsLogBundle\Event\LogCreatedEvent;
use DualMedia\EsLogBundle\LogStorage;
use DualMedia\EsLogBundle\Model\Entry;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use Pkly\ServiceMockHelperTrait;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

#[Group('unit')]
#[CoversClass(EntryCreator::class)]
class EntryCreatorTest extends TestCase
{
    use ServiceMockHelperTrait;

    private EntryCreator $service;

    protected function setUp(): void
    {
        $this->service = $this->createRealMockedServiceInstance(EntryCreator::class);
    }

    #[TestWith(['N/A',true, true, true])]
    #[TestWith([1,true, true, true, 1])]
    #[TestWith([2,false, true, true, 1,2])]
    #[TestWith(['N/A',false, false, true, 1,2])]
    #[TestWith([5,true, true, true, 5])]
    public function testCreate(
        int|string $expectedId,
        bool $hasInterface,
        bool $hasIdMethod,
        bool $hasUser,
        int|string|null $interfaceId = null,
        int|string|null $objectId = null,
    ): void
    {
        $interface = $this->createMock(IdentifiableInterface::class);
        $interface->method('getId')
            ->willReturn($interfaceId);

        $objectWithGetId = new class($objectId) {

            public function __construct(
                private readonly int|string|null $objectId
            )
            {}

            public function getId(): int|string|null
            {
                return $this->objectId;
            }
        };

        $objectWithoutGetId = new \stdClass();

        $object = $hasIdMethod ? $objectWithGetId : $objectWithoutGetId;
        $user = $hasUser ? $this->createMock(UserInterface::class) : null;

        $entry = $this->createMock(Entry::class);
        $entry->expects(static::once())
            ->method('withObjectAndUserIdentifier')
            ->with(
                self::isObject(),
                $expectedId,
                $user
            )
            ->willReturnSelf();

        $this->getMockedService(LoggerInterface::class)
            ->expects(static::exactly((int) (!$hasInterface && !$hasIdMethod)))
            ->method('warning')
            ->with(static::stringContains('Object "stdClass" does not implement IdentifiableInterface and lacks getId() method'));

        $entryFromEvent = $this->createMock(Entry::class);

        $logCreatedEvent = $this->createMock(LogCreatedEvent::class);
        $logCreatedEvent->expects(static::once())
            ->method('getEntry')
            ->willReturn($entryFromEvent);

        $this->getMockedService(EventDispatcherInterface::class)
            ->expects(static::once())
            ->method('dispatch')
            ->with(self::isInstanceOf(LogCreatedEvent::class))
            ->willReturn($logCreatedEvent);

        $this->getMockedService(LogStorage::class)
            ->expects(static::once())
            ->method('append')
            ->with($entryFromEvent, self::isObject());

        $this->service->create(
            $entry,
            $hasInterface ? $interface : $object,
            $user
        );
    }
}