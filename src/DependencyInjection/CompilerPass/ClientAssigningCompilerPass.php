<?php

namespace DualMedia\EsLogBundle\DependencyInjection\CompilerPass;

use Elastica\Client;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ClientAssigningCompilerPass implements CompilerPassInterface
{
    public function process(
        ContainerBuilder $container
    ): void {
        $id = (string)$container->getParameter('.dualmedia.es_log.client.service'); // @phpstan-ignore-line

        if (!$container->hasDefinition($id)) {
            throw new \LogicException(sprintf(
                'Service %s not found through %s',
                $id,
                'dm_es_logs.client_service'
            ));
        }

        $client = $container->getDefinition($id);

        if ($client instanceof ChildDefinition) {
            $client = $container->getDefinition($client->getParent());
        }

        if (!is_a($client->getClass(), Client::class, true)) {
            throw new \LogicException(sprintf(
                'Service %s set as parameter in %s must be an instance of %s',
                $id,
                'dm_es_logs.client_service',
                Client::class
            ));
        }

        $container->setAlias('.dualmedia.log.client', $id);
    }
}
