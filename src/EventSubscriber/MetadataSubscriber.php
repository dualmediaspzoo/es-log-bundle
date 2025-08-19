<?php

declare(strict_types=1);

namespace DualMedia\EsLogBundle\EventSubscriber;

use DualMedia\EsLogBundle\EsLogBundle;
use DualMedia\EsLogBundle\Event\LogCreatedEvent;
use DualMedia\EsLogBundle\Event\LogProcessedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class MetadataSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            LogCreatedEvent::class => ['onCreated', 128],
            LogProcessedEvent::class => 'onProcessed',
        ];
    }

    public function onCreated(
        LogCreatedEvent $event
    ): void {
        $entry = $event->getEntry();
        $metadata = $entry->getMetadata();

        if (!array_key_exists('objectIds', $metadata)) {
            $metadata['objectIds'] = [];
        }

        if (null !== ($user = $event->getUser())) {
            $metadata['objectIds']['user'] = [
                'class' => $entry->getUserIdentifierClass(),
                'id' => method_exists($user, 'getId') ? $user->getId() : null,
            ];
        }

        $object = $event->getObject();

        $metadata['objectIds']['object'] = [
            'class' => EsLogBundle::getRealClass(get_class($object)),
        ];

        if (!$entry->getAction()->isCreate()) {
            $metadata['objectIds']['object']['id'] = (string)$object->getId(); // @phpstan-ignore-line
        }

        $event->setEntry($entry->withMetadata($metadata));
    }

    public function onProcessed(
        LogProcessedEvent $event
    ): void {
        $entry = $event->getEntry();
        $metadata = $entry->getMetadata();

        $metadata['objectIds']['object']['id'] = (string)$event->getObject()->getId(); // @phpstan-ignore-line

        $event->setEntry($entry->withMetadata($metadata));
    }
}
