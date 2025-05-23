<?php

namespace DualMedia\EsLogBundle\Interface;

use DualMedia\EsLogBundle\Model\Entry;
use DualMedia\EsLogBundle\Model\Value;

interface NormalizerInterface
{
    /**
     * @param Entry<mixed> $entry
     *
     * @return Value|null If model is returned, the value will be written to ES
     */
    public function normalize(
        Entry $entry,
        string $field,
        mixed $value
    ): Value|null;
}
