<?php

namespace DualMedia\EsLogBundle\Normalizer;

use DualMedia\EsLogBundle\Interface\NormalizerInterface;
use DualMedia\EsLogBundle\Model\Entry;
use DualMedia\EsLogBundle\Model\Value;

class NullNormalizer implements NormalizerInterface
{
    public function normalize(
        Entry $entry,
        string $field,
        mixed $value
    ): Value|null {
        if (null !== $value) {
            return null;
        }

        return new Value(null, type: 'null');
    }
}
