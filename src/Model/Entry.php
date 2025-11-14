<?php

namespace DualMedia\EsLogBundle\Model;

use DualMedia\EsLogBundle\Enum\ActionEnum;
use DualMedia\EsLogBundle\Enum\TypeEnum;
use DualMedia\EsLogBundle\Interface\IdentifiableInterface;
use Symfony\Component\Security\Core\User\UserInterface;

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
        private string|null $objectClass,
        private string|null $objectId = null,
        private array $changes = [],
        private string|null $userIdentifier = null,
        private string|null $userIdentifierClass = null,
        private array $metadata = [],
        private \DateTimeInterface $loggedAt = new \DateTimeImmutable(),
        private string|null $documentId = null,
        private TypeEnum $type = TypeEnum::Manual,
    ) {
    }

    /**
     * @return Entry<TChange>
     */
    public function withObjectAndUserIdentifier(
        IdentifiableInterface $object,
        UserInterface|null $user,
    ): self {
        $objectId = $object->getId();
        $objectId = match ($objectId) {
            null => null,
            default => (string)$objectId,
        };

        return new self(
            $this->action,
            $this->objectClass,
            $objectId,
            $this->changes,
            $user?->getUserIdentifier(),
            null !== $user ? get_class($user) : null,
            $this->metadata,
            $this->loggedAt,
            $this->documentId,
            $this->type,
        );
    }

    /**
     * @return Entry<TChange>
     */
    public function withId(
        string|null $id
    ): self {
        return new self(
            $this->action,
            $this->objectClass,
            $id,
            $this->changes,
            $this->userIdentifier,
            $this->userIdentifierClass,
            $this->metadata,
            $this->loggedAt,
            $this->documentId,
            $this->type,
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
            $this->objectClass,
            $this->objectId,
            $this->changes,
            $this->userIdentifier,
            $this->userIdentifierClass,
            $metadata,
            $this->loggedAt,
            $this->documentId,
            $this->type,
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
            $this->objectClass,
            $this->objectId,
            $changes,
            $this->userIdentifier,
            $this->userIdentifierClass,
            $this->metadata,
            $this->loggedAt,
            $this->documentId,
            $this->type,
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
