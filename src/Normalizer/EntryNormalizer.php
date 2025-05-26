<?php

namespace DualMedia\EsLogBundle\Normalizer;

use DualMedia\EsLogBundle\Enum\ActionEnum;
use DualMedia\EsLogBundle\Interface\DenormalizerInterface;
use DualMedia\EsLogBundle\Interface\NormalizerInterface;
use DualMedia\EsLogBundle\Model\Change;
use DualMedia\EsLogBundle\Model\Entry;
use DualMedia\EsLogBundle\Model\LoadedValue;
use DualMedia\EsLogBundle\Model\Value;
use DualMedia\EsLogBundle\Trait\UtcTrait;

/**
 * @phpstan-type EntryDocument array{
 *      action: string,
 *      loggedAt: string,
 *      objectId: string,
 *      objectClass: string,
 *      changes: array<string, mixed>,
 *      userIdentifier: string|null,
 *      userIdentifierClass: string|null,
 *      documentId?: string
 *  }
 */
class EntryNormalizer
{
    use UtcTrait;

    public const string DATE_FORMAT_MICROTIME = 'Y-m-d\TH:i:s.u';

    /**
     * @param iterable<NormalizerInterface> $normalizers
     * @param iterable<DenormalizerInterface<mixed>> $denormalizers
     */
    public function __construct(
        private readonly iterable $normalizers,
        private readonly iterable $denormalizers
    ) {
    }

    /**
     * @param Entry<mixed> $entry
     *
     * @return EntryDocument
     */
    public function normalize(
        Entry $entry
    ): array {
        $changes = [];

        $normalize = function (Entry $entry, string $field, mixed $value): Value {
            foreach ($this->normalizers as $normalizer) {
                if (null !== ($output = $normalizer->normalize($entry, $field, $value))) {
                    return $output;
                }
            }

            return new Value('UNKNOWN', type: is_object($value) ? get_class($value) : null);
        };

        foreach ($entry->getChanges() as $index => $change) {
            $changes[$index] = [
                'from' => $normalize($entry, $index, $change->getFrom())->toArray(),
                'to' => $normalize($entry, $index, $change->getTo())->toArray(),
            ];
        }

        return [
            'action' => $entry->getAction()->value,
            'loggedAt' => \DateTimeImmutable::createFromInterface($entry->getLoggedAt())
                ->setTimezone($this->getUtc())
                ->format(self::DATE_FORMAT_MICROTIME),
            'objectId' => $entry->getObjectId(),
            'objectClass' => $entry->getObjectClass(),
            'changes' => $changes,
            'userIdentifier' => $entry->getUserIdentifier(),
            'userIdentifierClass' => $entry->getUserIdentifierClass(),
        ];
    }

    /**
     * @param EntryDocument $input
     *
     * @return Entry<LoadedValue<mixed>>
     */
    public function denormalize(
        array $input
    ): Entry {
        $action = ActionEnum::from($input['action']);
        $loggedAt = \DateTimeImmutable::createFromFormat(self::DATE_FORMAT_MICROTIME, $input['loggedAt'], $this->getUtc());
        /** @var \DateTimeImmutable $loggedAt */
        $objectId = $input['objectId'];
        $objectClass = $input['objectClass'];
        $userIdentifier = $input['userIdentifier'];
        $userIdentifierClass = $input['userIdentifierClass'];
        $documentId = $input['documentId'] ?? null;

        $changes = [];

        $denormalize = function (Entry $entry, string $field, Value $value): LoadedValue {
            foreach ($this->denormalizers as $denormalizer) {
                if (null !== ($output = $denormalizer->denormalize($entry, $field, $value))) {
                    return $output;
                }
            }

            return new LoadedValue($value);
        };

        $entry = new Entry(
            $action,
            $objectId,
            $objectClass,
            [],
            $userIdentifier,
            $userIdentifierClass,
            $loggedAt,
            $documentId
        );

        foreach ($input['changes'] as $index => $data) {
            $changes[$index] = new Change(
                $denormalize($entry, $index, Value::fromArray($data['from'])),
                $denormalize($entry, $index, Value::fromArray($data['to']))
            );
        }

        return $entry->withChanges($changes);
    }
}
