<?php

namespace DualMedia\EsLogBundle\Trait;

trait UtcTrait
{
    protected \DateTimeZone|null $utc = null;

    protected function getUtc(): \DateTimeZone
    {
        if (null === $this->utc) {
            $this->utc = new \DateTimeZone('UTC');
        }

        return $this->utc;
    }
}
