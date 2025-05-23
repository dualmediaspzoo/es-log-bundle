<?php

namespace DualMedia\EsLogBundle\Model;

/**
 * @template TValue
 */
readonly class Change
{
    /**
     * @param TValue $from
     * @param TValue $to
     */
    public function __construct(
        private mixed $from,
        private mixed $to
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'from' => $this->from,
            'to' => $this->to,
        ];
    }

    /**
     * @return TValue
     */
    public function getFrom(): mixed
    {
        return $this->from;
    }

    /**
     * @return TValue
     */
    public function getTo(): mixed
    {
        return $this->to;
    }
}
