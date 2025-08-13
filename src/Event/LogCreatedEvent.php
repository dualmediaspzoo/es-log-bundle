<?php

declare(strict_types=1);

namespace DualMedia\EsLogBundle\Event;

use DualMedia\EsLogBundle\Model\Entry;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Contracts\EventDispatcher\Event;

class LogCreatedEvent extends Event
{
    /**
     * @param Entry<mixed> $entry
     */
    public function __construct(
        private readonly UserInterface|null $user,
        private readonly object $object,
        private Entry $entry
    ) {
    }

    public function getUser(): UserInterface|null
    {
        return $this->user;
    }

    public function getObject(): object
    {
        return $this->object;
    }

    /**
     * @return Entry<mixed>
     */
    public function getEntry(): Entry
    {
        return $this->entry;
    }

    /**
     * @param Entry<mixed> $entry
     */
    public function setEntry(
        Entry $entry
    ): static {
        $this->entry = $entry;

        return $this;
    }
}
