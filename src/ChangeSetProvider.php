<?php

declare(strict_types=1);

namespace DualMedia\EsLogBundle;

use Doctrine\ORM\UnitOfWork;
use DualMedia\EsLogBundle\Metadata\ConfigProvider;
use DualMedia\EsLogBundle\Model\Change;

/**
 * @phpstan-import-type MetadataConfig from ConfigProvider
 */
class ChangeSetProvider
{
    /**
     * @param MetadataConfig $config
     *
     * @return array<string, Change<mixed>>
     */
    public function provide(
        UnitOfWork $uow,
        array $config,
        object $object
    ): array {
        $changeSet = [];

        foreach ($uow->getEntityChangeSet($object) as $field => $changes) {
            if (null === ($metadata = $config['properties'][$field] ?? null)) {
                continue;
            }

            $from = $changes[0];
            $to = $changes[1];

            if (null !== ($enumClass = $metadata['enumClass'] ?? null)) {
                if (null !== $from && !($from instanceof \BackedEnum)) {
                    $from = $enumClass::from($from);
                }

                if (null !== $to && !($to instanceof \BackedEnum)) {
                    $to = $enumClass::from($to);
                }
            }

            $changeSet[$field] = new Change($from, $to);
        }

        return $changeSet;
    }
}
