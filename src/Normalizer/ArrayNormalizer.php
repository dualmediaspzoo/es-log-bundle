<?php

namespace DualMedia\EsLogBundle\Normalizer;

use DualMedia\EsLogBundle\Interface\DenormalizerInterface;
use DualMedia\EsLogBundle\Interface\NormalizerInterface;
use DualMedia\EsLogBundle\Model\Entry;
use DualMedia\EsLogBundle\Model\LoadedValue;
use DualMedia\EsLogBundle\Model\Value;

/**
 * @implements DenormalizerInterface<array<\BackedEnum>>
 */
class ArrayNormalizer implements NormalizerInterface, DenormalizerInterface
{
    public const string MARKER = 'isArrayOfBackedEnums';
    public const string ITEM_TYPE = 'itemType';

    public function normalize(
        Entry $entry,
        string $field,
        mixed $value
    ): Value|null {
        if (!is_array($value)) {
            return null;
        }

        $enumClass = null;
        $isArrayOfBackedEnums = true;

        foreach ($value as $item) {
            if (!($item instanceof \BackedEnum)) {
                $isArrayOfBackedEnums = false;
                break;
            }
            $enumClass ??= get_class($item);
        }

        if ($isArrayOfBackedEnums) {
            $value = array_map(fn (\BackedEnum $item): mixed => $item->value, $value);
        }

        return new Value(
            $value,
            [
                self::MARKER => $isArrayOfBackedEnums,
                self::ITEM_TYPE => $enumClass,
            ],
            type: 'array'
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
            static function () use ($value) {
                return array_map(function ($v) use ($value) {
                    /** @var \BackedEnum $backedEnum */
                    $backedEnum = call_user_func([$value->metadata[self::ITEM_TYPE], 'from'], $v); // @phpstan-ignore-line

                    return $backedEnum;
                }, $value->value);
            }
        );
    }
}
