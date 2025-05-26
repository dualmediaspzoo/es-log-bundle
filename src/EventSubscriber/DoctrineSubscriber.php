<?php

namespace DualMedia\EsLogBundle\EventSubscriber;

use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\UnitOfWork;
use DualMedia\EsLogBundle\ChangeSetProvider;
use DualMedia\EsLogBundle\Enum\ActionEnum;
use DualMedia\EsLogBundle\EsLogBundle;
use DualMedia\EsLogBundle\LogStorage;
use DualMedia\EsLogBundle\Metadata\ConfigProvider;
use DualMedia\EsLogBundle\Model\Entry;
use DualMedia\EsLogBundle\UserContext;
use Symfony\Component\Security\Core\User\UserInterface;

class DoctrineSubscriber
{
    public function __construct(
        private readonly ConfigProvider $configProvider,
        private readonly UserContext $context,
        private readonly LogStorage $storage,
        private readonly ChangeSetProvider $changeSetProvider
    ) {
    }

    public function onFlush(
        OnFlushEventArgs $eventArgs
    ): void {
        $om = $eventArgs->getObjectManager();
        $uow = $om->getUnitOfWork();
        $user = $this->context->getUser();

        foreach ($uow->getScheduledEntityInsertions() as $object) {
            $this->createLogEntry(ActionEnum::Create, $object, $uow, $user);
        }

        foreach ($uow->getScheduledEntityUpdates() as $object) {
            $this->createLogEntry(ActionEnum::Update, $object, $uow, $user);
        }

        foreach ($uow->getScheduledEntityDeletions() as $object) {
            $this->createLogEntry(ActionEnum::Remove, $object, $uow, $user);
        }
    }

    public function postFlush(
        PostFlushEventArgs $args
    ): void {
        $this->storage->process();
    }

    private function createLogEntry(
        ActionEnum $action,
        object $object,
        UnitOfWork $uow,
        UserInterface|null $user
    ): void {
        $className = EsLogBundle::getRealClass(get_class($object));

        if (null === ($config = $this->configProvider->provide($className))
            || !$config[$action->getConfigKey()]) {
            return;
        }

        $identifier = $user?->getUserIdentifier();
        $userClass = null !== $user ? get_class($user) : null;

        $objectId = $object->getId(); // @phpstan-ignore-line
        $objectId = match ($objectId) {
            null => null,
            default => (string)$objectId,
        };

        $changes = [];

        if (ActionEnum::Update === $action && !empty($config['properties'])) {
            $changes = $this->changeSetProvider->provide($uow, $config, $object);

            if (empty($changes)) {
                return;
            }
        }

        $this->storage->append(
            new Entry(
                $action,
                $objectId,
                $className,
                $changes,
                $identifier,
                $userClass
            ),
            $object
        );
    }
}
