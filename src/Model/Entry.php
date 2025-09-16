<?php

namespace DualMedia\EsLogBundle\Model;

use DualMedia\EsLogBundle\Enum\ActionEnum;
use DualMedia\EsLogBundle\Enum\TypeEnum;

/**
 * @template TChange
 */
readonly class Entry
{
    /**
     * @param array<string, Change<TChange>> $changes
     * @param array<string, mixed> $metadata
     */
    public function __construct(
        private ActionEnum $action,
        private TypeEnum $type,
        private string|null $objectId,
        private string $objectClass,
        private array $changes,
        private string|null $userIdentifier,
        private string|null $userIdentifierClass,
        private array $metadata = [],
        private \DateTimeInterface $loggedAt = new \DateTimeImmutable(),
        private string|null $documentId = null
    ) {
    }

    /**
     * @return Entry<TChange>
     */
    public function withId(
        string|null $id
    ): self {
        return new self(
            $this->action,
            $this->type,
            $id,
            $this->objectClass,
            $this->changes,
            $this->userIdentifier,
            $this->userIdentifierClass,
            $this->metadata,
            $this->loggedAt,
            $this->documentId
        );
    }

    /**
     * @param array<string, mixed> $metadata
     *
     * @return Entry<TChange>
     */
    public function withMetadata(
        array $metadata
    ): self {
        return new self(
            $this->action,
            $this->type,
            $this->objectId,
            $this->objectClass,
            $this->changes,
            $this->userIdentifier,
            $this->userIdentifierClass,
            $metadata,
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
            $this->type,
            $this->objectId,
            $this->objectClass,
            $changes,
            $this->userIdentifier,
            $this->userIdentifierClass,
            $this->metadata,
            $this->loggedAt,
            $this->documentId
        );
    }

    public function getAction(): ActionEnum
    {
        return $this->action;
    }

    public function getType(): TypeEnum
    {
        return $this->type;
    }

    public function getLoggedAt(): \DateTimeInterface
    {
        return $this->loggedAt;
    }

    public function getObjectId(): string|null
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

    /**
     * @return array<string, mixed>
     */
    public function getMetadata(): array
    {
        return $this->metadata;
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
