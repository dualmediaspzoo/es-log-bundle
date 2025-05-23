<?php

namespace DualMedia\EsLogBundle\Attribute;

/**
 * Enables logging for an entity class.
 *
 * Use this in combination with {@see AsTrackedProperty} to track entity changes.
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
readonly class AsLoggedEntity
{
    /**
     * @param bool $create create logs on entity persist
     * @param bool $update create logs on entity update
     * @param bool $delete create logs on entity delete
     * @param bool $includeByDefault if true, all fields in entity will be tracked unless disabled with {@link AsIgnoredProperty}
     */
    public function __construct(
        public bool $create = true,
        public bool $update = true,
        public bool $delete = true,
        public bool $includeByDefault = false
    ) {
    }
}
