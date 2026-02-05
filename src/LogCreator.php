<?php

declare(strict_types=1);

namespace DualMedia\EsLogBundle;

use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\UnitOfWork;
use DualMedia\EsLogBundle\Enum\ActionEnum;
use DualMedia\EsLogBundle\Enum\TypeEnum;
use DualMedia\EsLogBundle\Metadata\ConfigProvider;
use DualMedia\EsLogBundle\Model\Entry;
use Symfony\Component\Security\Core\User\UserInterface;

class LogCreator
{
    public function __construct(
        private readonly ConfigProvider $configProvider,
        private readonly ChangeSetProvider $changeSetProvider,
        private readonly EntryCreator $entryCreator
    ) {
    }

    public function create(
        ActionEnum $action,
        object $object,
        UnitOfWork $uow,
        UserInterface|null $user
    ): void {
        $className = ClassUtils::getClass($object);

        if (null === ($config = $this->configProvider->provide($className))
            || !$config[$action->getConfigKey()]) {
            return;
        }

        $changes = [];

        if ($action->isUpdate() && !empty($config['properties'])) {
            $changes = $this->changeSetProvider->provide($uow, $config, $object);

            if (empty($changes)) {
                return;
            }
        }

        $this->entryCreator->create(
            new Entry(
                $action,
                $className,
                changes: $changes,
                type: TypeEnum::Automatic,
            ),
            $object,
            $user
        );
    }
}
