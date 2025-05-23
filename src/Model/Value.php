<?php

namespace DualMedia\EsLogBundle\Model;

/**
 * @phpstan-type SerializedValue array{value: mixed, metadata?: array<string, mixed>, type?: string}
 */
readonly class Value
{
    /**
     * @param array<string, mixed> $metadata
     */
    public function __construct(
        public mixed $value,
        public array $metadata = [],
        public string|null $type = null
    ) {
    }

    /**
     * @return SerializedValue
     */
    public function toArray(): array
    {
        $output = [
            'value' => $this->value,
        ];

        if (null !== $this->type) {
            $output['type'] = $this->type;
        }

        if (!empty($this->metadata)) {
            $output['metadata'] = $this->metadata;
        }

        return $output;
    }

    /**
     * @param SerializedValue $input
     */
    public static function fromArray(
        array $input
    ): self {
        return new self(
            $input['value'] ?? null,
            $input['metadata'] ?? [],
            $input['type'] ?? null
        );
    }
}
