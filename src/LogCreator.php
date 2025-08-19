<?php

declare(strict_types=1);

namespace DualMedia\EsLogBundle;

use Doctrine\ORM\UnitOfWork;
use DualMedia\EsLogBundle\Enum\ActionEnum;
use DualMedia\EsLogBundle\Enum\TypeEnum;
use DualMedia\EsLogBundle\Event\LogCreatedEvent;
use DualMedia\EsLogBundle\Metadata\ConfigProvider;
use DualMedia\EsLogBundle\Model\Entry;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class LogCreator
{
    public function __construct(
        private readonly ConfigProvider $configProvider,
        private readonly LogStorage $storage,
        private readonly ChangeSetProvider $changeSetProvider,
        private readonly EventDispatcherInterface $eventDispatcher
    ) {
    }

    public function create(
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

        if ($action->isUpdate() && !empty($config['properties'])) {
            $changes = $this->changeSetProvider->provide($uow, $config, $object);

            if (empty($changes)) {
                return;
            }
        }

        $event = $this->eventDispatcher->dispatch(new LogCreatedEvent(
            $user,
            $object,
            new Entry(
                $action,
                TypeEnum::Automatic,
                $objectId,
                $className,
                $changes,
                $identifier,
                $userClass
            )
        ));

        $this->storage->append($event->getEntry(), $object);
    }
}
