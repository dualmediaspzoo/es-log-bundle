<?php

namespace DualMedia\EsLogBundle\Tests\Unit\Search;

use DualMedia\EsLogBundle\Model\Configuration;
use DualMedia\EsLogBundle\Search\Builder;
use DualMedia\EsLogBundle\Tests\Resource\TestClass;
use Elastica\Client;
use Elastica\Index;
use Elastica\Query\AbstractQuery;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[Group('unit')]
#[Group('search')]
#[CoversClass(Builder::class)]
class BuilderTest extends TestCase
{
    private Builder $builder;
    private Client&MockObject $client;
    private Configuration $configuration;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = $this->createMock(Client::class);
        $this->configuration = new Configuration('index');
        $this->builder = new Builder($this->client, $this->configuration);
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

        $builder = new Builder($client, $configuration);
        $builder->start();
    }

    /**
     * @param class-string $className
     */
    #[TestWith([])]
    public function testClass(
        string $className = TestClass::class
    ): void {
        $this->builder->start();
        $this->builder->class($className);
        $search = $this->builder->build();
        /** @var AbstractQuery $query */
        $query = $search->getQuery()->getQuery();
        static::assertSame($className, $query->getParam('filter')[0]->getParam('objectClass'));
    }

    #[TestWith([])]
    public function testId(
        string|int $id = 1
    ): void {
        $this->builder->start();
        $this->builder->id($id);
        $search = $this->builder->build();
        /** @var AbstractQuery $query */
        $query = $search->getQuery()->getQuery();
        static::assertSame($id, $query->getParam('filter')[0]->getParam('objectId'));
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
