<?php

namespace DualMedia\EsLogBundle;

use DualMedia\Common\Interface\IdentifiableInterface;
use DualMedia\EsLogBundle\Event\LogCreatedEvent;
use DualMedia\EsLogBundle\Model\Entry;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class EntryCreator
{
    public function __construct(
        private readonly LogStorage $storage,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * @param Entry<mixed> $entry
     */
    public function create(
        Entry $entry,
        object $object,
        UserInterface|null $user = null
    ): void {
        if ($object instanceof IdentifiableInterface) {
            $id = $object->getId() ?? 'N/A';
        } elseif (method_exists($object, 'getId')) {
            $id = $object->getId() ?? 'N/A';
        } else {
            $this->logger->warning(sprintf('Object "%s" does not implement IdentifiableInterface and lacks getId() method', get_class($object)));
            $id = 'N/A';
        }

        $event = $this->eventDispatcher->dispatch(new LogCreatedEvent(
            $user,
            $object,
            $entry->withObjectAndUserIdentifier($object, $id, $user)
        ));

        $this->storage->append($event->getEntry(), $object);
    }
}
