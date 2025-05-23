<?php

namespace DualMedia\EsLogBundle\Normalizer;

use DualMedia\EsLogBundle\Interface\DenormalizerInterface;
use DualMedia\EsLogBundle\Interface\NormalizerInterface;
use DualMedia\EsLogBundle\Model\Entry;
use DualMedia\EsLogBundle\Model\LoadedValue;
use DualMedia\EsLogBundle\Model\Value;
use DualMedia\EsLogBundle\Trait\UtcTrait;

/**
 * @implements DenormalizerInterface<\DateTimeImmutable>
 */
class DateTimeNormalizer implements NormalizerInterface, DenormalizerInterface
{
    use UtcTrait;

    public function normalize(
        Entry $entry,
        string $field,
        mixed $value
    ): Value|null {
        if (!($value instanceof \DateTimeInterface)) {
            return null;
        }

        return new Value(
            \DateTimeImmutable::createFromInterface($value)
                ->setTimezone($this->getUtc())
                ->format(\DateTimeInterface::ATOM),
            type: \DateTimeImmutable::class
        );
    }

    public function denormalize(
        Entry $entry,
        string $field,
        Value $value
    ): LoadedValue|null {
        if (\DateTimeImmutable::class !== $value->type) {
            return null;
        }

        return new LoadedValue(
            $value,
            fn () => new \DateTimeImmutable($value->value, $this->getUtc()),
        );
    }
}
