<?php

namespace DualMedia\EsLogBundle\EasyAdmin;

use Doctrine\ORM\QueryBuilder;
use DualMedia\EsLogBundle\Model\Entry;
use DualMedia\EsLogBundle\Model\LoadedValue;
use DualMedia\EsLogBundle\Model\Results;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Orm\EntityPaginatorInterface;
use EasyCorp\Bundle\EasyAdminBundle\Dto\PaginatorDto;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGeneratorInterface;

class ElasticPaginator implements EntityPaginatorInterface
{
    public const string PAGE_PLACEHOLDER = 'magical_es_log_page_placeholder';

    private string $currentUrl;

    public function __construct(
        private readonly Results $results,
        private readonly AdminUrlGeneratorInterface $generator
    ) {
        $this->currentUrl = $this->generator->set('page', self::PAGE_PLACEHOLDER)->generateUrl();
    }

    public function paginate(
        PaginatorDto $paginatorDto,
        QueryBuilder $queryBuilder
    ): EntityPaginatorInterface {
        throw new \LogicException('Cannot be used');
    }

    public function generateUrlForPage(
        int $page
    ): string {
        return str_replace(self::PAGE_PLACEHOLDER, (string)$page, $this->currentUrl);
    }

    public function getCurrentPage(): int
    {
        return $this->results->page + 1;
    }

    public function getLastPage(): int
    {
        return (int)ceil($this->results->total / $this->results->perPage);
    }

    /**
     * @return iterable<int|null>
     */
    public function getPageRange(
        int|null $pagesOnEachSide = null,
        int|null $pagesOnEdges = null
    ): iterable {
        $pagesOnEachSide = 3;
        $pagesOnEdges = 1;

        if ($this->getLastPage() <= ($pagesOnEachSide + $pagesOnEdges) * 2) {
            return yield from range(1, $this->getLastPage());
        }

        if ($this->getCurrentPage() > ($pagesOnEachSide + $pagesOnEdges + 1)) {
            yield from range(1, $pagesOnEdges);
            yield null;
            yield from range($this->getCurrentPage() - $pagesOnEachSide, $this->getCurrentPage());
        } else {
            yield from range(1, $this->getCurrentPage());
        }

        if ($this->getCurrentPage() < ($this->getLastPage() - $pagesOnEachSide - $pagesOnEdges - 1)) {
            yield from range($this->getCurrentPage() + 1, $this->getCurrentPage() + $pagesOnEachSide);
            yield null;
            yield from range($this->getLastPage() - $pagesOnEdges + 1, $this->getLastPage());
        } elseif ($this->getCurrentPage() + 1 <= $this->getLastPage()) {
            yield from range($this->getCurrentPage() + 1, $this->getLastPage());
        }
    }

    public function getPageSize(): int
    {
        return $this->results->perPage;
    }

    public function hasPreviousPage(): bool
    {
        return $this->results->page > 0 && $this->results->total > 0;
    }

    public function getPreviousPage(): int
    {
        return $this->results->page;
    }

    public function hasNextPage(): bool
    {
        return $this->results->page < $this->getLastPage() - 1;
    }

    public function getNextPage(): int
    {
        return $this->results->page + 2;
    }

    public function hasToPaginate(): bool
    {
        return $this->results->total > $this->results->perPage;
    }

    public function isOutOfRange(): bool
    {
        return $this->results->page > $this->getLastPage();
    }

    public function getNumResults(): int
    {
        return $this->results->total;
    }

    /**
     * @return null|iterable<Entry<LoadedValue<mixed>>>
     */
    public function getResults(): iterable|null
    {
        return $this->results->entries;
    }

    public function getResultsAsJson(): string
    {
        throw new \LogicException('Cannot be used');
    }
}
