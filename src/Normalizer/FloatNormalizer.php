<?php

namespace DualMedia\EsLogBundle\Normalizer;

use DualMedia\EsLogBundle\Interface\NormalizerInterface;
use DualMedia\EsLogBundle\Model\Entry;
use DualMedia\EsLogBundle\Model\Value;

class FloatNormalizer implements NormalizerInterface
{
    public function normalize(
        Entry $entry,
        string $field,
        mixed $value
    ): Value|null {
        if (!is_float($value)) {
            return null;
        }

        return new Value($value, type: 'float');
    }
}
