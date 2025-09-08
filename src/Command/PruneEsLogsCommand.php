<?php

namespace DualMedia\EsLogBundle\Command;

use DualMedia\EsLogBundle\Builder\QueryBuilder;
use DualMedia\EsLogBundle\Model\Configuration;
use Elastica\Client;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'dualmedia:logs:prune',
    description: 'Removes old logs from an Elasticsearch'
)]
class PruneEsLogsCommand extends Command
{
    public function __construct(
        private readonly Client $client,
        private readonly Configuration $configuration,
        private readonly QueryBuilder $builder
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('days', 'd', InputOption::VALUE_OPTIONAL, 'Number of days to keep documents', 60)
            ->addOption('class', 'c', InputOption::VALUE_OPTIONAL, 'Object class to remove documents for', null)
            ->addOption('limit', 'l', InputOption::VALUE_OPTIONAL, 'Limit number of rows', 1000);
    }

    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ): int {
        $io = new SymfonyStyle($input, $output);

        $days = (int)$input->getOption('days');
        $limit = (int)$input->getOption('limit');
        $className = $input->getOption('class');

        $builder = $this->builder->start()
            ->olderThan(new \DateTime(sprintf('-%d days', $days)));

        if ($className) {
            $builder->class($className);
        }

        $query = $builder->build();

        try {
            $response = $this->client->getIndex($this->configuration->index)
                ->deleteByQuery($query, [
                    'slices' => 'auto',
                    'max_docs' => $limit,
                ]);
        } catch (\Exception $e) {
            $io->error('An unexpected exception occurred: '.$e->getMessage());

            return Command::FAILURE;
        }

        if (!$response->isOk()) {
            $io->error('The delete_by_query operation failed: '.$response->getError());

            return Command::FAILURE;
        }

        $io->success(sprintf('Cleanup successful. Deleted %d documents.', $response->getData()['deleted'] ?? 0));

        return Command::SUCCESS;
    }
}
