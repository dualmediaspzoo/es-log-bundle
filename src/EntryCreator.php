<?php

namespace DualMedia\EsLogBundle;

use DualMedia\EsLogBundle\Event\LogCreatedEvent;
use DualMedia\EsLogBundle\Interface\IdentifiableInterface;
use DualMedia\EsLogBundle\Model\Entry;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class EntryCreator
{
    public function __construct(
        private readonly LogStorage $storage,
        private readonly EventDispatcherInterface $eventDispatcher
    ) {
    }

    /**
     * @param Entry<mixed> $entry
     */
    public function create(
        Entry $entry,
        IdentifiableInterface $object,
        UserInterface|null $user = null
    ): void {
        $event = $this->eventDispatcher->dispatch(new LogCreatedEvent(
            $user,
            $object,
            $entry->withObjectAndUserIdentifier($object, $user)
        ));

        $this->storage->append($event->getEntry(), $object);
    }
}
