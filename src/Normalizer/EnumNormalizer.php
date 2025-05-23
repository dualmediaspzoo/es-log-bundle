<?php

namespace DualMedia\EsLogBundle\Normalizer;

use DualMedia\EsLogBundle\Interface\DenormalizerInterface;
use DualMedia\EsLogBundle\Interface\NormalizerInterface;
use DualMedia\EsLogBundle\Model\Entry;
use DualMedia\EsLogBundle\Model\LoadedValue;
use DualMedia\EsLogBundle\Model\Value;

/**
 * @implements DenormalizerInterface<\UnitEnum>
 */
class EnumNormalizer implements NormalizerInterface, DenormalizerInterface
{
    public const string MARKER = 'isUnitEnum';

    public function normalize(
        Entry $entry,
        string $field,
        mixed $value
    ): Value|null {
        if (!($value instanceof \UnitEnum)) {
            return null;
        }

        return new Value($value->name, [self::MARKER => true], type: get_class($value));
    }

    public function denormalize(
        Entry $entry,
        string $field,
        Value $value
    ): LoadedValue|null {
        if (true !== ($value->metadata[self::MARKER] ?? null)) {
            return null;
        }

        /**
         * @param class-string<\UnitEnum> $class
         */
        $fromName = function (string $class, string $name): \UnitEnum|null {
            foreach ($class::cases() as $case) {
                if ($name === $case->name) {
                    return $case;
                }
            }

            return null;
        };

        return new LoadedValue(
            $value,
            static fn () => $fromName($value->type, $value->value)
        );
    }
}
