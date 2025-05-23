<?php

namespace DualMedia\EsLogBundle\EventSubscriber;

use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\UnitOfWork;
use DualMedia\EsLogBundle\Enum\ActionEnum;
use DualMedia\EsLogBundle\EsLogBundle;
use DualMedia\EsLogBundle\Metadata\ConfigProvider;
use DualMedia\EsLogBundle\Model\Change;
use DualMedia\EsLogBundle\Model\Configuration;
use DualMedia\EsLogBundle\Model\Entry;
use DualMedia\EsLogBundle\Normalizer\EntryNormalizer;
use DualMedia\EsLogBundle\UserContext;
use Elastica\Client;
use Elastica\Document;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @phpstan-import-type MetadataConfig from ConfigProvider
 */
class DoctrineSubscriber
{
    private const int CHUNK_SIZE = 5;

    /**
     * @var array<integer, mixed>
     */
    private array $entityReferences = [];

    /**
     * @var list<Entry<mixed>>
     */
    private array $logs = [];

    /**
     * @var array<integer, integer>
     */
    private array $logEntityMap = [];

    public function __construct(
        private readonly Client $client,
        private readonly Configuration $configuration,
        private readonly ConfigProvider $metadataProvider,
        private readonly EntryNormalizer $normalizer,
        private readonly UserContext $context
    ) {
    }

    public function getActualId(
        object $entity
    ): string|null {
        $entityId = spl_object_id($entity);

        $value = $this->entityReferences[$entityId]?->getId() ?? null;

        return match ($value) {
            null => null,
            default => (string)$value,
        };
    }

    public function onFlush(
        OnFlushEventArgs $eventArgs
    ): void {
        $this->logs = []; // refresh
        $this->logEntityMap = [];

        $om = $eventArgs->getObjectManager();
        $uow = $om->getUnitOfWork();
        $user = $this->context->getUser();

        foreach ($uow->getScheduledEntityInsertions() as $object) {
            $this->storeEntityReference($object);
            $this->createLogEntry(ActionEnum::Create, $object, $uow, $user);
        }

        foreach ($uow->getScheduledEntityUpdates() as $object) {
            $this->storeEntityReference($object);
            $this->createLogEntry(ActionEnum::Update, $object, $uow, $user);
        }

        foreach ($uow->getScheduledEntityDeletions() as $object) {
            $this->createLogEntry(ActionEnum::Remove, $object, $uow, $user);
        }
    }

    public function postFlush(
        PostFlushEventArgs $args
    ): void {
        $this->updateInsertIds();
        $this->flushLogsEntries();
    }

    private function storeEntityReference(
        object $entity
    ): void {
        $entityId = spl_object_id($entity);
        $this->entityReferences[$entityId] = $entity;
    }

    private function createLogEntry(
        ActionEnum $action,
        object $object,
        UnitOfWork $uow,
        UserInterface|null $user
    ): void {
        $className = EsLogBundle::getRealClass(get_class($object));

        if (null === ($config = $this->metadataProvider->provide($className))
            || !$config[$action->getConfigKey()]) {
            return;
        }

        $identifier = $user?->getUserIdentifier();
        $userClass = null !== $user ? get_class($user) : null;
        $objectId = $this->getActualId($object);

        $changes = [];

        if (ActionEnum::Update === $action && !empty($config['properties'])) {
            $changes = $this->getObjectChangeSetData($uow, $config, $object);

            if (empty($changes)) {
                return;
            }
        }

        $entry = new Entry(
            $action,
            $objectId,
            $className,
            $changes,
            $identifier,
            $userClass
        );

        $this->logs[] = $entry;
        $this->logEntityMap[spl_object_id($entry)] = spl_object_id($object);
    }

    private function updateInsertIds(): void
    {
        foreach ($this->logs as $index => $log) {
            if (!$log->getAction()->isCreate()) {
                continue;
            }

            $this->logs[$index] = $log->withId($this->getActualId($this->entityReferences[$this->logEntityMap[spl_object_id($log)]])); // @phpstan-ignore-line
        }
    }

    private function flushLogsEntries(): void
    {
        $index = $this->client->getIndex($this->configuration->index);

        foreach (array_chunk($this->logs, self::CHUNK_SIZE) as $chunk) {
            $index->addDocuments(array_map(
                fn (Entry $e) => new Document(data: $this->normalizer->normalize($e)),
                $chunk
            ));
        }

        $this->logs = [];
        $this->logEntityMap = [];
    }

    /**
     * @param MetadataConfig $config
     *
     * @return array<string, Change<mixed>>
     */
    private function getObjectChangeSetData(
        UnitOfWork $uow,
        array $config,
        object $object
    ): array {
        $changeSet = [];

        foreach ($uow->getEntityChangeSet($object) as $field => $changes) {
            if (!in_array($field, $config['properties'], true)) {
                continue;
            }

            $changeSet[$field] = new Change(...$changes);
        }

        return $changeSet;
    }
}
