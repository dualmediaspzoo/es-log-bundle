<?php

declare(strict_types=1);

namespace DualMedia\EsLogBundle\Tests\Unit\Query;

use DualMedia\EsLogBundle\Query\Builder;
use DualMedia\EsLogBundle\Tests\Resource\TestClass;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

#[Group('unit')]
#[Group('query')]
#[CoversClass(Builder::class)]
class BuilderTest extends TestCase
{
    private Builder $builder;

    protected function setUp(): void
    {
        parent::setUp();

        $this->builder = new Builder();
    }

    public function testStart(): void
    {
        static::assertInstanceOf(Builder::class, $this->builder->start());
    }

    /**
     * @param class-string $className
     */
    #[TestWith([TestClass::class])]
    public function testClass(
        string $className
    ): void {
        $query = $this->builder->start()
            ->class($className)
            ->build();

        static::assertSame($className, $query->getParam('filter')[0]->getParam('objectClass'));
    }

    #[TestWith([1])]
    #[TestWith(['1'])]
    public function testId(
        string|int $id
    ): void {
        $query = $this->builder->start()
            ->id($id)
            ->build();

        static::assertSame($id, $query->getParam('filter')[0]->getParam('objectId'));
    }

    #[TestWith([new \DateTime()])]
    public function testOlderThan(
        \DateTime $dateTime
    ): void {
        $query = $this->builder->start()
            ->olderThan($dateTime)
            ->build();

        static::assertSame(['lt' => $dateTime->format('Y-m-d\TH:i:s.000000')], $query->getParam('filter')[0]->getParam('loggedAt'));
    }
}
