<?php

namespace DualMedia\EsLogBundle\Command;

use DualMedia\EsLogBundle\Model\Configuration;
use Elastica\Client;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'dualmedia:logs:delete-index',
    description: 'Deletes set ES index'
)]
class DeleteEsIndexCommand extends Command
{
    public function __construct(
        private readonly Client $client,
        private readonly Configuration $configuration
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('force', 'f', InputOption::VALUE_NONE)
            ->addOption('if-exists', 'i', InputOption::VALUE_NONE, 'Only delete the index if it exists');
    }

    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ): int {
        $io = new SymfonyStyle($input, $output);

        if (!$input->getOptions()['force']) {
            $io->error('You must specify --force option to run this command.');

            return self::FAILURE;
        }

        $index = $this->client->getIndex($this->configuration->index);

        if (!$index->exists()) {
            if ($input->getOption('if-exists')) {
                $io->writeln('Index does not exist. Skipping deletion.');

                return self::SUCCESS;
            } else {
                $io->error('The index does not exist.');

                return self::FAILURE;
            }
        }

        $index->delete();

        $output->writeln('Index deleted successfully.');

        return self::SUCCESS;
    }
}
