<?php

namespace DualMedia\EsLogBundle\Model;

/**
 * @template T
 */
class LoadedValue
{
    /**
     * @var T
     */
    private readonly mixed $loaded; // @phpstan-ignore property.uninitializedReadonly

    private bool $initialized = false;

    /**
     * @param null|callable(): T $loader
     */
    public function __construct(
        public readonly Value $value,
        private readonly mixed $loader = null,
        private readonly bool $complex = false
    ) {
    }

    /**
     * @return T
     */
    public function getLoaded(): mixed
    {
        if (!$this->initialized) {
            /** @phpstan-ignore property.readOnlyAssignNotInConstructor */
            $this->loaded = match ($this->loader) {
                null => $this->value->value,
                default => call_user_func($this->loader),
            };
            $this->initialized = true;
        }

        return $this->loaded;
    }

    public function isComplex(): bool
    {
        return $this->complex;
    }
}
