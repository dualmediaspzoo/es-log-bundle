<?php

namespace DualMedia\EsLogBundle\EventSubscriber;

use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use DualMedia\EsLogBundle\Enum\ActionEnum;
use DualMedia\EsLogBundle\Interface\IdentifiableInterface;
use DualMedia\EsLogBundle\LogCreator;
use DualMedia\EsLogBundle\LogStorage;
use DualMedia\EsLogBundle\UserContext;

class DoctrineSubscriber
{
    public function __construct(
        private readonly UserContext $context,
        private readonly LogStorage $storage,
        private readonly LogCreator $creator
    ) {
    }

    public function onFlush(
        OnFlushEventArgs $eventArgs
    ): void {
        $om = $eventArgs->getObjectManager();
        $uow = $om->getUnitOfWork();
        $user = $this->context->getUser();

        foreach ($uow->getScheduledEntityInsertions() as $object) {
            /** @var IdentifiableInterface $object */
            $this->creator->create(ActionEnum::Create, $object, $uow, $user);
        }

        foreach ($uow->getScheduledEntityUpdates() as $object) {
            /** @var IdentifiableInterface $object */
            $this->creator->create(ActionEnum::Update, $object, $uow, $user);
        }

        foreach ($uow->getScheduledEntityDeletions() as $object) {
            /** @var IdentifiableInterface $object */
            $this->creator->create(ActionEnum::Remove, $object, $uow, $user);
        }
    }

    public function postFlush(
        PostFlushEventArgs $args
    ): void {
        $this->storage->process();
    }
}
