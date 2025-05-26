<?php

namespace DualMedia\EsLogBundle\Command;

use DualMedia\EsLogBundle\Model\Configuration;
use Elastica\Client;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'dualmedia:logs:create-index',
    description: 'Creates set ES index'
)]
class CreateEsIndexCommand extends Command
{
    public function __construct(
        private readonly Client $client,
        private readonly Configuration $configuration
    ) {
        parent::__construct();
    }

    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ): int {
        $index = $this->client->getIndex($this->configuration->index);

        $index->create([
            'mappings' => [
                'properties' => [
                    'action' => ['type' => 'keyword'],
                    'loggedAt' => [
                        'type' => 'date',
                        'format' => 'yyyy-MM-dd\'T\'HH:mm:ss.SSSSSS',
                    ],
                    'objectId' => ['type' => 'keyword'],
                    'objectClass' => ['type' => 'keyword'],
                    'changes' => ['type' => 'object'],
                    'userIdentifier' => ['type' => 'keyword'],
                    'userIdentifierClass' => ['type' => 'keyword'],
                ],
            ],
        ], true);

        $output->writeln('Index created successfully.');

        return self::SUCCESS;
    }
}
