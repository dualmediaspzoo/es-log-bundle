<?php

namespace DualMedia\EsLogBundle\Normalizer;

use Doctrine\Persistence\ManagerRegistry;
use DualMedia\EsLogBundle\EsLogBundle;
use DualMedia\EsLogBundle\Interface\DenormalizerInterface;
use DualMedia\EsLogBundle\Interface\NormalizerInterface;
use DualMedia\EsLogBundle\Model\Entry;
use DualMedia\EsLogBundle\Model\LoadedValue;
use DualMedia\EsLogBundle\Model\Value;

/**
 * @implements DenormalizerInterface<object>
 */
class EntityNormalizer implements NormalizerInterface, DenormalizerInterface
{
    public const string MARKER = 'isEntity';

    /**
     * @var array<class-string, bool>
     */
    private array $entityCache = [];

    public function __construct(
        private readonly ManagerRegistry $registry
    ) {
    }

    public function normalize(
        Entry $entry,
        string $field,
        mixed $value
    ): Value|null {
        if (!is_object($value)) {
            return null;
        }

        $class = EsLogBundle::getRealClass(get_class($value));
        $isEntity = $this->entityCache[$class] ??= null !== $this->registry->getManagerForClass($class);

        if (!$isEntity) {
            return null;
        }

        // we hope there's a getId method, otherwise we are going to cry
        return new Value(
            $value->getId(), // @phpstan-ignore-line
            [self::MARKER => true],
            type: $class
        );
    }

    public function denormalize(
        Entry $entry,
        string $field,
        Value $value
    ): LoadedValue|null {
        if (true !== ($value->metadata[self::MARKER] ?? null)) {
            return null;
        }

        return new LoadedValue(
            $value,
            fn () => $this->registry->getRepository($value->type)->find($value->value), // @phpstan-ignore-line
            true
        );
    }
}
