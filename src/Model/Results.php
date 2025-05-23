<?php

namespace DualMedia\EsLogBundle\Model;

readonly class Results
{
    /**
     * @param list<Entry<LoadedValue<mixed>>> $entries
     */
    public function __construct(
        public array $entries,
        public int $total,
        public int $page,
        public int $from,
        public int $perPage
    ) {
    }
}
