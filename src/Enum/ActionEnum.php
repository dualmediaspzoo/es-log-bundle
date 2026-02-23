<?php

namespace DualMedia\EsLogBundle\Enum;

enum ActionEnum: string
{
    case Create = 'create';

    case Update = 'update';

    case Delete = 'delete';

    case Info = 'info';

    public function isCreate(): bool
    {
        return self::Create === $this;
    }

    public function isUpdate(): bool
    {
        return self::Update === $this;
    }

    public function isDelete(): bool
    {
        return self::Delete === $this;
    }

    public function getConfigKey(): string
    {
        return 'track'.$this->name;
    }
}
