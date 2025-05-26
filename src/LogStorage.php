<?php

declare(strict_types=1);

namespace DualMedia\EsLogBundle;

use DualMedia\EsLogBundle\Model\Entry;

class LogStorage
{
    private int $counter = 0;

    private int $last = 0;

    /**
     * @var array<int, Entry<mixed>>
     */
    private array $entries = [];

    /**
     * @var array<int, object>
     */
    private array $entityReferences = [];

    /**
     * @param Entry<mixed> $entry
     */
    public function append(
        Entry $entry,
        object $entity
    ): void {
        $this->entries[$this->counter] = $entry;

        if ($entry->getAction()->isCreate()) {
            $this->entityReferences[$this->counter] = $entity;
        }

        $this->counter++;
    }

    public function process(): void
    {
        for ($i = $this->last; $i < $this->counter; $i++) {
            $entry = $this->entries[$i];

            if (!$entry->getAction()->isCreate()) {
                continue;
            }

            $value = $this->entityReferences[$i]->getId(); // @phpstan-ignore-line
            $this->entries[$i] = $entry->withId(match ($value) {
                null => null,
                default => (string)$value,
            });

            $this->entityReferences[$i] = null; // remove reference
        }

        $this->last = $this->counter;
    }

    /**
     * @return array<int, Entry<mixed>>
     */
    public function getEntries(): array
    {
        return $this->entries;
    }
}
