<?php

namespace DualMedia\EsLogBundle\Tests\Unit\Builder;

use DualMedia\EsLogBundle\Builder\SearchBuilder;
use DualMedia\EsLogBundle\Model\Configuration;
use Elastica\Client;
use Elastica\Index;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[Group('unit')]
#[Group('search')]
#[CoversClass(SearchBuilder::class)]
class SearchBuilderTest extends TestCase
{
    private SearchBuilder $builder;
    private Client&MockObject $client;
    private Configuration $configuration;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = $this->createMock(Client::class);
        $this->configuration = new Configuration('index');
        $this->builder = new SearchBuilder($this->client, $this->configuration);
    }

    #[TestWith([])]
    public function testStart(
        string $index = 'test'
    ): void {
        $indexEntity = $this->createMock(Index::class);
        $client = $this->getMockBuilder(Client::class)
            ->onlyMethods(['getIndex'])
            ->getMock();
        $client->expects(static::once())
            ->method('getIndex')
            ->with($index)
            ->willReturn($indexEntity);
        $configuration = new Configuration($index);

        $builder = new SearchBuilder($client, $configuration);
        $builder->start();
    }

    #[TestWith([])]
    #[TestWith(['field', true])]
    public function testSort(
        string $field = 'test',
        bool $desc = false
    ): void {
        $this->builder->start();
        $this->builder->sort($field, $desc);
        $search = $this->builder->build();
        static::assertSame([$field => ['order' => $desc ? 'desc' : 'asc']], $search->getQuery()->getParam('sort'));
    }

    #[TestWith([])]
    public function testPage(
        int $page = 1
    ): void {
        $this->builder->start();
        $this->builder->page($page);
        $search = $this->builder->build();
        static::assertSame(10 * $page, $search->getQuery()->getParam('from'));
    }

    #[TestWith([])]
    public function testPerPage(
        int $perPage = 1
    ): void {
        $this->builder->start();
        $this->builder->perPage($perPage);
        $this->builder->page(1);
        $search = $this->builder->build();
        static::assertSame($perPage, $search->getQuery()->getParam('from'));
    }

    public function testBuild(): void
    {
        $this->builder->start();
        $this->builder->page(1);
        $search = $this->builder->build();
        static::assertSame(10, $search->getQuery()->getParam('from'));
        static::assertSame(10, $search->getQuery()->getParam('size'));
        static::assertSame(['loggedAt' => ['order' => 'desc']], $search->getQuery()->getParam('sort'));
    }
}
