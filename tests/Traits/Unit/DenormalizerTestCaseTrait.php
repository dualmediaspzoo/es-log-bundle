<?php

namespace DualMedia\EsLogBundle\Tests\Traits\Unit;

use DualMedia\EsLogBundle\Model\Entry;
use DualMedia\EsLogBundle\Model\Value;
use PHPUnit\Framework\Attributes\DataProvider;

trait DenormalizerTestCaseTrait
{
    #[DataProvider('provideNonSupportedCases')]
    public function testDenormalizeUnsupported(
        mixed $value,
    ): void {
        static::assertNull($this->service->denormalize($this->createMock(Entry::class), 'test', new Value($value)));
    }

    /**
     * @return iterable<mixed>
     */
    public static function provideNonSupportedCases(): iterable
    {
        throw new \LogicException('You must override this method');
    }
}
