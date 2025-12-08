<?php

namespace DualMedia\EsLogBundle\Enum;

enum ActionEnum: string
{
    case Create = 'create';

    case Update = 'update';

    case Remove = 'remove';

    case Info = 'info';

    public function isCreate(): bool
    {
        return self::Create === $this;
    }

    public function isUpdate(): bool
    {
        return self::Update === $this;
    }

    public function isRemove(): bool
    {
        return self::Remove === $this;
    }

    public function getConfigKey(): string
    {
        return 'track'.$this->name;
    }
}
