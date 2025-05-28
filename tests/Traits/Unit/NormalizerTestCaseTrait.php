<?php

namespace DualMedia\EsLogBundle\Tests\Traits\Unit;

use DualMedia\EsLogBundle\Model\Entry;
use PHPUnit\Framework\Attributes\DataProvider;

trait NormalizerTestCaseTrait
{
    #[DataProvider('provideNormalizerNonSupportedCases')]
    public function testNormalizeUnsupported(
        mixed $value,
    ): void {
        static::assertNull($this->service->normalize($this->createMock(Entry::class), 'test', $value));
    }

    /**
     * @return iterable<mixed>
     */
    public static function provideNormalizerNonSupportedCases(): iterable
    {
        throw new \LogicException('You must override this method');
    }
}
