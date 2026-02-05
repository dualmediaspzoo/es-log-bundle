<?php

namespace DualMedia\EsLogBundle\Tests\Unit\EventSubscriber;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\UnitOfWork;
use DualMedia\Common\Interface\IdentifiableInterface;
use DualMedia\EsLogBundle\Enum\ActionEnum;
use DualMedia\EsLogBundle\EventSubscriber\DoctrineSubscriber;
use DualMedia\EsLogBundle\LogCreator;
use DualMedia\EsLogBundle\LogStorage;
use DualMedia\EsLogBundle\UserContext;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use Pkly\ServiceMockHelperTrait;
use Symfony\Component\Security\Core\User\UserInterface;

#[Group('unit')]
#[Group('event_subscriber')]
#[CoversClass(DoctrineSubscriber::class)]
class DoctrineSubscriberTest extends TestCase
{
    use ServiceMockHelperTrait;

    private DoctrineSubscriber $service;

    protected function setUp(): void
    {
        $this->service = $this->createRealMockedServiceInstance(DoctrineSubscriber::class);
    }

    #[TestWith([0, 0, 0])]
    #[TestWith([1, 1, 1])]
    #[TestWith([123, 0, 0])]
    #[TestWith([0, 22, 0])]
    #[TestWith([0, 0, 50])]
    public function testOnFlush(
        int $insertions,
        int $updates,
        int $deletions
    ): void {
        $event = $this->createMock(OnFlushEventArgs::class);
        $manager = $this->createMock(EntityManagerInterface::class);
        $uow = $this->createMock(UnitOfWork::class);

        $event->expects(static::once())
            ->method('getObjectManager')
            ->willReturn($manager);

        $manager->expects(static::once())
            ->method('getUnitOfWork')
            ->willReturn($uow);

        $user = $this->createMock(UserInterface::class);

        $this->getMockedService(UserContext::class)
            ->expects(static::once())
            ->method('getUser')
            ->willReturn($user);

        $mockInsert = $mockUpdate = $mockDelete = [];

        for ($i = 0; $i < $insertions; $i++) {
            $mockInsert[] = $this->createMock(IdentifiableInterface::class);
        }

        for ($i = 0; $i < $updates; $i++) {
            $mockUpdate[] = $this->createMock(IdentifiableInterface::class);
        }

        for ($i = 0; $i < $deletions; $i++) {
            $mockDelete[] = $this->createMock(IdentifiableInterface::class);
        }

        $joined = array_merge(
            $mockInsert,
            $mockUpdate,
            $mockDelete,
        );

        $uow->expects(static::once())
            ->method('getScheduledEntityInsertions')
            ->willReturn($mockInsert);

        $uow->expects(static::once())
            ->method('getScheduledEntityUpdates')
            ->willReturn($mockUpdate);

        $uow->expects(static::once())
            ->method('getScheduledEntityDeletions')
            ->willReturn($mockDelete);

        $this->getMockedService(LogCreator::class)
            ->expects($invoke = static::exactly($insertions + $updates + $deletions))
            ->method('create')
            ->with(static::isInstanceOf(ActionEnum::class), static::callback(function ($o) use ($invoke, $joined): bool {
                $counter = $invoke->numberOfInvocations() - 1;

                return $joined[$counter] === $o;
            }), $uow, $user);

        $this->service->onFlush($event);
    }

    public function testPostFlush(): void
    {
        $this->getMockedService(LogStorage::class)
            ->expects(static::once())
            ->method('process');

        $this->service->postFlush($this->createMock(PostFlushEventArgs::class));
    }
}
