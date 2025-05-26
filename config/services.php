<?php

use Doctrine\ORM\Events;
use DualMedia\EsLogBundle\EsLogBundle as Bundle;
use Symfony\Component\DependencyInjection\Argument\AbstractArgument;
use Symfony\Component\DependencyInjection\Argument\TaggedIteratorArgument;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Reference;

return static function (ContainerConfigurator $configurator) {
    $services = $configurator->services()
        ->defaults()
        ->private();

    $services->set('.dualmedia.log.configuration', \DualMedia\EsLogBundle\Model\Configuration::class)
        ->arg('$index', new AbstractArgument('Set through configuration'));

    $services->set(\DualMedia\EsLogBundle\Command\CreateEsIndexCommand::class)
        ->arg('$configuration', new Reference('.dualmedia.log.configuration'))
        ->arg('$client', new Reference('.dualmedia.log.client'))
        ->tag('console.command');

    $services->set(\DualMedia\EsLogBundle\Command\DeleteEsIndexCommand::class)
        ->arg('$configuration', new Reference('.dualmedia.log.configuration'))
        ->arg('$client', new Reference('.dualmedia.log.client'))
        ->tag('console.command');

    $services->set(\DualMedia\EsLogBundle\LogStorage::class);

    $services->set(\DualMedia\EsLogBundle\ChangeSetProvider::class);

    $services->set(\DualMedia\EsLogBundle\EventSubscriber\DoctrineSubscriber::class)
        ->arg('$configProvider', new Reference(\DualMedia\EsLogBundle\Metadata\ConfigProvider::class))
        ->arg('$context', new Reference(\DualMedia\EsLogBundle\UserContext::class))
        ->arg('$storage', new Reference(\DualMedia\EsLogBundle\LogStorage::class))
        ->arg('$changeSetProvider', new Reference(\DualMedia\EsLogBundle\ChangeSetProvider::class))
        ->tag('doctrine.event_listener', [
            'event' => Events::onFlush,
        ])
        ->tag('doctrine.event_listener', [
            'event' => Events::postFlush,
        ]);

    $services->set(\DualMedia\EsLogBundle\EventSubscriber\SaveSubscriber::class)
        ->arg('$configuration', new Reference('.dualmedia.log.configuration'))
        ->arg('$client', new Reference('.dualmedia.log.client'))
        ->arg('$normalizer', new Reference(\DualMedia\EsLogBundle\Normalizer\EntryNormalizer::class))
        ->arg('$storage', new Reference(\DualMedia\EsLogBundle\LogStorage::class))
        ->tag('kernel.event_subscriber');

    $services->set(\DualMedia\EsLogBundle\UserContext::class)
        ->arg('$tokenStorage', new Reference('security.token_storage', \Symfony\Component\DependencyInjection\ContainerInterface::NULL_ON_INVALID_REFERENCE));

    $services->set(\DualMedia\EsLogBundle\Metadata\ConfigProvider::class)
        ->arg('$config', new AbstractArgument('Set through configuration'));

    $services->set(\DualMedia\EsLogBundle\Normalizer\EntryNormalizer::class)
        ->arg('$normalizers', new TaggedIteratorArgument(Bundle::NORMALIZER_TAG))
        ->arg('$denormalizers', new TaggedIteratorArgument(Bundle::DENORMALIZER_TAG));

    // normalizers
    $services->set(\DualMedia\EsLogBundle\Normalizer\DateTimeNormalizer::class)
        ->tag(Bundle::NORMALIZER_TAG)
        ->tag(Bundle::DENORMALIZER_TAG);

    $services->set(\DualMedia\EsLogBundle\Normalizer\BoolNormalizer::class)
        ->tag(Bundle::NORMALIZER_TAG);

    $services->set(\DualMedia\EsLogBundle\Normalizer\EntityNormalizer::class)
        ->arg('$registry', new Reference(\Doctrine\Persistence\ManagerRegistry::class))
        ->tag(Bundle::NORMALIZER_TAG)
        ->tag(Bundle::DENORMALIZER_TAG);

    $services->set(\DualMedia\EsLogBundle\Normalizer\EnumNormalizer::class)
        ->tag(Bundle::NORMALIZER_TAG)
        ->tag(Bundle::DENORMALIZER_TAG);

    $services->set(\DualMedia\EsLogBundle\Normalizer\IntNormalizer::class)
        ->tag(Bundle::NORMALIZER_TAG);

    $services->set(\DualMedia\EsLogBundle\Normalizer\FloatNormalizer::class)
        ->tag(Bundle::NORMALIZER_TAG);

    $services->set(\DualMedia\EsLogBundle\Normalizer\StringNormalizer::class)
        ->tag(Bundle::NORMALIZER_TAG);

    $services->set(\DualMedia\EsLogBundle\Normalizer\NullNormalizer::class)
        ->tag(Bundle::NORMALIZER_TAG);

    $services->set(\DualMedia\EsLogBundle\Search\Builder::class)
        ->arg('$configuration', new Reference('.dualmedia.log.configuration'))
        ->arg('$client', new Reference('.dualmedia.log.client'));

    $services->set(\DualMedia\EsLogBundle\Search\Processor::class)
        ->arg('$normalizer', new Reference(\DualMedia\EsLogBundle\Normalizer\EntryNormalizer::class));

    // optional EasyAdmin integration
    $services->set(\DualMedia\EsLogBundle\EasyAdmin\Field\LogEntryConfigurator::class)
        ->arg('$adminUrlGenerator', new Reference('EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator', \Symfony\Component\DependencyInjection\ContainerInterface::NULL_ON_INVALID_REFERENCE))
        ->tag('ea.field_configurator');
};
