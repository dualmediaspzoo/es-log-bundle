<?php

namespace DualMedia\EsLogBundle\Tests\Resource;

class TestClass
{
    public function __construct(
        private readonly int $id
    ) {
    }

    public function getId(): int
    {
        return $this->id;
    }
}
