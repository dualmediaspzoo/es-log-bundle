<?php

namespace DualMedia\EsLogBundle\Tests\Unit\EventSubscriber;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\UnitOfWork;
use DualMedia\EsLogBundle\ChangeSetProvider;
use DualMedia\EsLogBundle\EventSubscriber\DoctrineSubscriber;
use DualMedia\EsLogBundle\LogStorage;
use DualMedia\EsLogBundle\Metadata\ConfigProvider;
use DualMedia\EsLogBundle\Model\Entry;
use DualMedia\EsLogBundle\Tests\Resource\TestClass;
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

    /**
     * @param list<int> $entityInsertionsData
     * @param list<int> $entityUpdatesData
     * @param list<int> $entityDeletionsData
     */
    #[TestWith([[1, 2], [1], [1, 2, 3]])]
    #[TestWith([[1, 2], [1], [1, 2, 3], false])]
    #[TestWith([[1, 2], [1], [1, 2, 3], true, false])]
    #[TestWith([[1, 2], [1], [1, 2, 3], false, false])]
    public function testOnFlush(
        array $entityInsertionsData,
        array $entityUpdatesData,
        array $entityDeletionsData,
        bool $hasConfig = true,
        bool $hasChanges = true,
    ): void {
        $entityInsertions = [];
        $entityUpdates = [];
        $entityDeletions = [];

        foreach ($entityInsertionsData as $data) {
            $entity = $this->createMock(TestClass::class);
            $entity->expects(static::atMost(2))
                ->method('getId')
                ->willReturn($data);

            $entityInsertions[$data] = $entity;
        }

        foreach ($entityUpdatesData as $data) {
            $entity = $this->createMock(TestClass::class);
            $entity->expects(static::atMost(2))
                ->method('getId')
                ->willReturn($data);

            $entityUpdates[$data] = $entity;
        }

        foreach ($entityDeletionsData as $data) {
            $entity = $this->createMock(TestClass::class);
            $entity->expects(static::atMost(2))
                ->method('getId')
                ->willReturn($data);

            $entityDeletions[$data] = $entity;
        }

        $uow = $this->createMock(UnitOfWork::class);
        $uow->expects(static::once())
            ->method('getScheduledEntityInsertions')
            ->willReturn($entityInsertions);
        $uow->expects(static::once())
            ->method('getScheduledEntityDeletions')
            ->willReturn($entityDeletions);
        $uow->expects(static::once())
            ->method('getScheduledEntityUpdates')
            ->willReturn($entityUpdates);

        $om = $this->createMock(EntityManagerInterface::class);
        $om->expects(static::once())
            ->method('getUnitOfWork')
            ->willReturn($uow);

        $eventArgs = $this->createMock(OnFlushEventArgs::class);
        $eventArgs->expects(static::once())
            ->method('getObjectManager')
            ->willReturn($om);

        $user = $this->createMock(UserInterface::class);
        $user->method('getUserIdentifier')
            ->willReturn('user_id');

        $this->getMockedService(UserContext::class)
            ->method('getUser')
            ->willReturn($user);

        $this->getMockedService(ConfigProvider::class)
            ->method('provide')
            ->willReturn($hasConfig ? ['trackCreate' => true, 'trackUpdate' => true, 'trackRemove' => true] : null);

        $this->getMockedService(ChangeSetProvider::class)
            ->method('provide')
            ->willReturn($hasChanges ? ['changes'] : []);

        $allEntityActions = array_merge_recursive($entityDeletionsData, $entityInsertionsData, $entityUpdatesData);

        $this->getMockedService(LogStorage::class)
            ->expects(static::exactly($hasConfig ? count($allEntityActions) : 0))
            ->method('append')
            ->with(static::isInstanceOf(Entry::class), static::isInstanceOf(TestClass::class));

        $this->service->onFlush($eventArgs);
    }
}
