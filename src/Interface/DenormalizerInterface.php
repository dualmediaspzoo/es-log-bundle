<?php

namespace DualMedia\EsLogBundle\Interface;

use DualMedia\EsLogBundle\Model\Entry;
use DualMedia\EsLogBundle\Model\LoadedValue;
use DualMedia\EsLogBundle\Model\Value;

/**
 * @template TLoadedValue
 */
interface DenormalizerInterface
{
    /**
     * @param Entry<mixed> $entry
     *
     * @return LoadedValue<TLoadedValue>|null If model is returned, the value will be replaced in the entry
     */
    public function denormalize(
        Entry $entry,
        string $field,
        Value $value
    ): LoadedValue|null;
}
