<?php

namespace DualMedia\EsLogBundle\Tests\Unit\Search;

use DualMedia\EsLogBundle\Model\Entry;
use DualMedia\EsLogBundle\Normalizer\EntityNormalizer;
use DualMedia\EsLogBundle\Normalizer\EntryNormalizer;
use DualMedia\EsLogBundle\Search\Builder;
use DualMedia\EsLogBundle\Search\Processor;
use Elastica\Query;
use Elastica\Result;
use Elastica\ResultSet;
use Elastica\Search;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Pkly\ServiceMockHelperTrait;

#[Group('unit')]
#[Group('search')]
#[CoversClass(Processor::class)]
class ProcessorTest extends TestCase
{
    use ServiceMockHelperTrait;

    private Processor $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = $this->createRealMockedServiceInstance(Processor::class);
    }

    /**
     * @param list<array{id: int, source: string}> $resultData
     */
    #[TestWith([[['id' => 1,'source' => 'source1'],['id' => 2,'source' => 'source2']],1,1,1])]
    #[TestWith([[['id' => 1,'source' => 'source1']],10,2,10])]
    public function testProcess(
        array $resultData,
        int $from,
        int $size,
        int $totalHints
    ):void
    {
        $results = [];
        $entries = [];
        foreach ($resultData as $data) {
            $result = $this->createMock(Result::class);
            $result->expects(static::once())
                ->method('getId')
                ->willReturn($data['id']);
            $result->expects(static::once())
                ->method('getSource')
                ->willReturn([$data['source']]);
            $results[] = $result;
            $entries[] = $this->createMock(Entry::class);
        }

        $this->getMockedService(EntryNormalizer::class)
            ->expects($this->exactly(count($resultData)))
            ->method('denormalize')
            ->willReturn(...$entries);

        $resultSet = $this->createMock(ResultSet::class);
        $resultSet->expects(static::once())
            ->method('getResults')
            ->willReturn($results);
        $resultSet->expects(static::once())
            ->method('getTotalHits')
            ->willReturn($totalHints);

        $query = $this->createMock(Query::class);
        $query->expects(static::exactly(2))
            ->method('getParam')
            ->with($this->callback(fn ($key) => in_array($key, ['from', 'size'])))
            ->willReturnOnConsecutiveCalls($size,$from);

        $search = $this->createMock(Search::class);
        $search->expects(static::once())
            ->method('search')
            ->willReturn($resultSet);
        $search->expects($this->once())
            ->method('getQuery')
            ->willReturn($query);

        $result = $this->service->process($search);
        $this->assertSame($entries, $result->entries);
        $this->assertSame($totalHints, $result->total);
        $this->assertSame($from / $size, $result->page);
        $this->assertSame($from, $result->from);
        $this->assertSame($size, $result->perPage);
    }
}