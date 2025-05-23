<?php

namespace DualMedia\EsLogBundle\Tests\Unit\Search;

use DualMedia\EsLogBundle\Model\Configuration;
use DualMedia\EsLogBundle\Search\Builder;
use Elastica\Client;
use Elastica\Index;
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

    #[TestWith([])]
    public function testClass(
        string $className = 'test'
    ): void {
        $this->builder->start();
        $this->builder->class($className);
        $search = $this->builder->build();
        static::assertSame($className, $search->getQuery()->getQuery()->getParam('filter')[0]->getParam('objectClass'));
    }
}
