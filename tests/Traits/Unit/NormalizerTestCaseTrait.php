<?php

namespace DualMedia\EsLogBundle\Tests\Traits\Unit;

use DualMedia\EsLogBundle\Model\Entry;
use PHPUnit\Framework\Attributes\DataProvider;

trait NormalizerTestCaseTrait
{
    #[DataProvider('provideNonSupportedCases')]
    public function testNormalizeUnsupported(
        mixed $value,
    ): void {
        static::assertNull($this->service->normalize($this->createMock(Entry::class), 'test', $value));
    }

    /**
     * @return iterable<mixed>
     */
    public static function provideNonSupportedCases(): iterable
    {
        throw new \LogicException('You must override this method');
    }
}
