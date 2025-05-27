<?php

namespace DualMedia\EsLogBundle\Tests\Unit\Normalizer;

use DualMedia\EsLogBundle\Interface\NormalizerInterface;
use DualMedia\EsLogBundle\Model\Entry;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

abstract class AbstractNormalizerTestCase extends TestCase
{
    protected NormalizerInterface $service;

    #[DataProvider('provideNonSupportedCases')]
    public function testNormalizeUnsupported(
        mixed $value,
    ): void {
        static::assertNull($this->service->normalize($this->createMock(Entry::class), 'test', $value));
    }

    /**
     * @return iterable<mixed>
     */
    abstract public static function provideNonSupportedCases(): iterable;
}
