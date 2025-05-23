<?php

namespace DualMedia\EsLogBundle\Model;

use DualMedia\EsLogBundle\Enum\ActionEnum;

/**
 * @template TChange
 */
readonly class Entry
{
    /**
     * @param array<string, Change<TChange>> $changes
     */
    public function __construct(
        private ActionEnum $action,
        private string|null $objectId,
        private string $objectClass,
        private array $changes,
        private string|null $userIdentifier,
        private string|null $userIdentifierClass,
        private \DateTimeInterface $loggedAt = new \DateTimeImmutable(),
        private string|null $documentId = null
    ) {
    }

    /**
     * @return Entry<TChange>
     */
    public function withId(
        string $id
    ): self {
        return new self(
            $this->action,
            $id,
            $this->objectClass,
            $this->changes,
            $this->userIdentifier,
            $this->userIdentifierClass,
            $this->loggedAt,
            $this->documentId
        );
    }

    /**
     * @template T
     *
     * @param array<string, Change<T>> $changes
     *
     * @return Entry<T>
     */
    public function withChanges(
        array $changes
    ): self {
        return new self(
            $this->action,
            $this->objectId,
            $this->objectClass,
            $changes,
            $this->userIdentifier,
            $this->userIdentifierClass,
            $this->loggedAt,
            $this->documentId
        );
    }

    public function getAction(): ActionEnum
    {
        return $this->action;
    }

    public function getLoggedAt(): \DateTimeInterface
    {
        return $this->loggedAt;
    }

    public function getObjectId(): string
    {
        return $this->objectId;
    }

    public function getObjectClass(): string
    {
        return $this->objectClass;
    }

    /**
     * @return array<string, Change<TChange>>|null
     */
    public function getChanges(): array|null
    {
        return $this->changes;
    }

    public function getUserIdentifier(): string|null
    {
        return $this->userIdentifier;
    }

    public function getUserIdentifierClass(): string|null
    {
        return $this->userIdentifierClass;
    }

    public function getDocumentId(): string|null
    {
        return $this->documentId;
    }
}
