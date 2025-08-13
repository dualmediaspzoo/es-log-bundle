<?php

declare(strict_types=1);

namespace DualMedia\EsLogBundle\Event;

use DualMedia\EsLogBundle\Model\Entry;
use Symfony\Contracts\EventDispatcher\Event;

class LogProcessedEvent extends Event
{
    /**
     * @param Entry<mixed> $entry
     */
    public function __construct(
        private readonly object $object,
        private Entry $entry
    ) {
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
