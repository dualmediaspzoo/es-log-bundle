<?php

declare(strict_types=1);

namespace DualMedia\EsLogBundle\Query;

use DualMedia\EsLogBundle\EsLogBundle;
use Elastica\Query\AbstractQuery;
use Elastica\Query\BoolQuery;
use Elastica\Query\Range;
use Elastica\Query\Term;

class Builder
{
    private BoolQuery|null $query = null;

    public function start(): self
    {
        assert(null === $this->query);

        $this->query = new BoolQuery();

        return $this;
    }

    public function build(): BoolQuery
    {
        assert(null !== $this->query);

        $query = $this->query;
        $this->query = null;

        return $query;
    }

    public function filter(
        AbstractQuery $filter
    ): self {
        assert(null !== $this->query);

        $this->query->addFilter($filter);

        return $this;
    }

    /**
     * @param class-string $className
     */
    public function class(
        string $className
    ): self {
        return $this->filter(new Term(['objectClass' => EsLogBundle::getRealClass($className)]));
    }

    public function id(
        string|int $id
    ): self {
        return $this->filter(new Term(['objectId' => $id]));
    }

    public function olderThan(
        \DateTimeInterface $dateTime
    ): self {
        return $this->filter(new Range('loggedAt', [
            'lt' => $dateTime->format('Y-m-d\TH:i:s.000000'),
        ]));
    }
}
