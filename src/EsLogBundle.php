<?php

namespace DualMedia\EsLogBundle;

use Doctrine\Persistence\Proxy;
use DualMedia\EsLogBundle\DependencyInjection\CompilerPass\ClientAssigningCompilerPass;
use DualMedia\EsLogBundle\DependencyInjection\CompilerPass\EntityProvideCompilerPass;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

class EsLogBundle extends AbstractBundle
{
    public const string NORMALIZER_TAG = 'dualmedia.es_log.normalizer';
    public const string DENORMALIZER_TAG = 'dualmedia.es_log.denormalizer';

    protected string $extensionAlias = 'dm_es_logs';

    public function configure(
        DefinitionConfigurator $definition
    ): void {
        $definition->rootNode() // @phpstan-ignore-line
        ->children()
            ->scalarNode('client_service')
            ->defaultValue('elastica_client')
            ->end()
            ->scalarNode('cache')
            ->defaultValue('cache.app')
            ->end()
            ->arrayNode('entity_paths')
            ->useAttributeAsKey('name')
            ->scalarPrototype()
            ->end()
            ->end()
            ->scalarNode('index_name')
            ->defaultValue('dm_entity_logs')
            ->end()
            ->end();
    }

    /**
     * @param array<string, mixed> $config
     */
    public function loadExtension(
        array $config,
        ContainerConfigurator $container,
        ContainerBuilder $builder
    ): void {
        $loader = new PhpFileLoader(
            $builder,
            new FileLocator(__DIR__.'/../config')
        );

        $loader->load('services.php');

        $builder->setParameter('.dualmedia.es_log.client.service', $config['client_service']);
        $builder->setParameter('.dualmedia.es_log.entity_paths', $config['entity_paths']);

        $services = $container->services();

        $services->get('.dualmedia.log.configuration')
            ->arg('$index', $config['index_name']);
    }

    /**
     * Copied over from Doctrine since they remove it later.
     *
     * Gets the real class name of a class name that could be a proxy.
     *
     * @template T of object
     *
     * @param class-string<Proxy<T>>|class-string<T> $className
     *
     * @return class-string<T>
     */
    public static function getRealClass(
        string $className
    ): string {
        $pos = strrpos($className, '\\'.Proxy::MARKER.'\\');

        if (false === $pos) {
            /** @psalm-var class-string<T> */
            return $className;
        }

        return substr($className, $pos + Proxy::MARKER_LENGTH + 2); // @phpstan-ignore-line
    }

    public function build(
        ContainerBuilder $container
    ): void {
        $container->addCompilerPass(new ClientAssigningCompilerPass(), priority: -100);
        $container->addCompilerPass(new EntityProvideCompilerPass(), priority: -101);
    }
}
