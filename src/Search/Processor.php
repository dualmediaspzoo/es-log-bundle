<?php

namespace DualMedia\EsLogBundle\Search;

use DualMedia\EsLogBundle\Model\Results;
use DualMedia\EsLogBundle\Normalizer\EntryNormalizer;
use Elastica\Search;

class Processor
{
    public function __construct(
        private readonly EntryNormalizer $normalizer
    ) {
    }

    public function process(
        Search $search
    ): Results {
        $results = $search->search();
        $entries = [];

        foreach ($results->getResults() as $result) {
            $entries[] = $this->normalizer->denormalize([...$result->getSource(), 'documentId' => $result->getId()]); // @phpstan-ignore-line
        }

        $query = $search->getQuery();
        $size = $query->getParam('size');
        $from = $query->getParam('from');

        return new Results(
            $entries,
            $results->getTotalHits(),
            $from / $size,
            $from,
            $size
        );
    }
}
