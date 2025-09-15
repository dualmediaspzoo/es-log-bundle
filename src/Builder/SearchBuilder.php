<?php

namespace DualMedia\EsLogBundle\Builder;

use DualMedia\EsLogBundle\Model\Configuration;
use Elastica\Client;
use Elastica\Query;
use Elastica\Query\BoolQuery;
use Elastica\Search;

class SearchBuilder
{
    private Search|null $search = null;
    private string $sort = 'loggedAt';
    private bool $sortDesc = true;
    private int $page = 0;
    private int $perPage = 10;

    public function __construct(
        private readonly Client $client,
        private readonly Configuration $configuration
    ) {
    }

    public function start(): self
    {
        assert(null === $this->search);

        $index = $this->client->getIndex($this->configuration->index);
        $this->search = new Search($this->client);

        $this->sort = 'loggedAt';
        $this->sortDesc = true;
        $this->page = 0;
        $this->perPage = 10;

        $this->search->addIndex($index);

        return $this;
    }

    public function sort(
        string $field,
        bool $desc = true
    ): self {
        assert(null !== $this->search);

        $this->sort = $field;
        $this->sortDesc = $desc;

        return $this;
    }

    /**
     * @param int $page 0-indexed page number
     */
    public function page(
        int $page
    ): self {
        $this->page = $page;

        return $this;
    }

    public function perPage(
        int $perPage
    ): self {
        $this->perPage = $perPage;

        return $this;
    }

    public function build(
        BoolQuery|null $query = null
    ): Search {
        assert(null !== $this->search);

        $search = $this->search;

        $query = Query::create($query);
        $query->setSort([$this->sort => ['order' => $this->sortDesc ? 'desc' : 'asc']]);

        $query->setSize($this->perPage);
        $query->setFrom($this->perPage * $this->page);
        $query->setTrackTotalHits();

        $search->setQuery($query);

        $this->search = null;

        return $search;
    }
}
