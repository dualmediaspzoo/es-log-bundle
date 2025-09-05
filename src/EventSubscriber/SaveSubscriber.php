<?php

declare(strict_types=1);

namespace DualMedia\EsLogBundle\EventSubscriber;

use DualMedia\EsLogBundle\LogStorage;
use DualMedia\EsLogBundle\Model\Configuration;
use DualMedia\EsLogBundle\Model\Entry;
use DualMedia\EsLogBundle\Normalizer\EntryNormalizer;
use Elastic\Elasticsearch\Exception\ElasticsearchException;
use Elastica\Client;
use Elastica\Document;
use Elastica\Exception\ExceptionInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Event\ConsoleTerminateEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\TerminateEvent;

class SaveSubscriber implements EventSubscriberInterface
{
    public const int CHUNK_SIZE = 10;

    public function __construct(
        private readonly Client $client,
        private readonly Configuration $configuration,
        private readonly EntryNormalizer $normalizer,
        private readonly LogStorage $storage,
        private readonly LoggerInterface|null $logger
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            TerminateEvent::class => ['onTerminate', -512],
            ConsoleTerminateEvent::class => ['onTerminate', -512],
        ];
    }

    public function onTerminate(): void
    {
        $entries = $this->storage->getEntries();

        if (empty($entries)) {
            return;
        }

        $index = $this->client->getIndex($this->configuration->index);

        foreach (array_chunk($entries, self::CHUNK_SIZE) as $chunk) {
            try {
                $index->addDocuments(array_map(
                    fn (Entry $e) => new Document(data: $this->normalizer->normalize($e)),
                    $chunk
                ));
            } catch (ExceptionInterface|ElasticsearchException $e) {
                $this->logger?->error('[EsLogBundle] Failed to save one or more documents because of an exception', [
                    'exception' => $e,
                ]);
            }
        }
    }
}
