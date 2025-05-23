<?php

namespace DualMedia\EsLogBundle\Enum;

enum ActionEnum: string
{
    case Create = 'create';

    case Update = 'update';

    case Remove = 'remove';

    public function isCreate(): bool
    {
        return self::Create === $this;
    }

    public function getConfigKey(): string
    {
        return 'track'.$this->name;
    }
}
