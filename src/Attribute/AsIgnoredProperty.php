<?php

namespace DualMedia\EsLogBundle\Attribute;

/**
 * Sets a property as ignored, useful if specifying with {@link AsLoggedEntity::$includeByDefault}.
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
readonly class AsIgnoredProperty
{
}
