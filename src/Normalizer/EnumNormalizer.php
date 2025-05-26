<?php

namespace DualMedia\EsLogBundle\Normalizer;

use DualMedia\EsLogBundle\Interface\DenormalizerInterface;
use DualMedia\EsLogBundle\Interface\NormalizerInterface;
use DualMedia\EsLogBundle\Model\Entry;
use DualMedia\EsLogBundle\Model\LoadedValue;
use DualMedia\EsLogBundle\Model\Value;

/**
 * @implements DenormalizerInterface<\BackedEnum>
 */
class EnumNormalizer implements NormalizerInterface, DenormalizerInterface
{
    public const string MARKER = 'isBackedEnum';

    public function normalize(
        Entry $entry,
        string $field,
        mixed $value
    ): Value|null {
        if (!($value instanceof \BackedEnum)) {
            return null;
        }

        return new Value($value->value, [self::MARKER => true], type: get_class($value));
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
            static fn () => call_user_func([$value->type, 'from'], $value->value) // @phpstan-ignore-line
        );
    }
}
