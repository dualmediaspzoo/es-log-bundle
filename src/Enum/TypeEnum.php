<?php

declare(strict_types=1);

namespace DualMedia\EsLogBundle\Enum;

enum TypeEnum: string
{
    case Automatic = 'automatic';
    case Manual = 'manual';

    public function isAutomatic(): bool
    {
        return self::Automatic === $this;
    }

    public function isManual(): bool
    {
        return self::Manual === $this;
    }
}
