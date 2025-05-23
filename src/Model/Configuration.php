<?php

namespace DualMedia\EsLogBundle\Model;

readonly class Configuration
{
    public function __construct(
        public string $index
    ) {
    }
}
