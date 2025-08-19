<?php

namespace DualMedia\EsLogBundle\Tests\Unit\Normalizer;

use DualMedia\EsLogBundle\Enum\ActionEnum;
use DualMedia\EsLogBundle\Enum\TypeEnum;
use DualMedia\EsLogBundle\Interface\DenormalizerInterface;
use DualMedia\EsLogBundle\Interface\NormalizerInterface;
use DualMedia\EsLogBundle\Model\Change;
use DualMedia\EsLogBundle\Model\Entry;
use DualMedia\EsLogBundle\Model\LoadedValue;
use DualMedia\EsLogBundle\Model\Value;
use DualMedia\EsLogBundle\Normalizer\EntryNormalizer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use Pkly\ServiceMockHelperTrait;

/**
 * @phpstan-import-type EntryDocument from EntryNormalizer
 */
#[Group('unit')]
#[Group('normalizer')]
#[CoversClass(EntryNormalizer::class)]
class EntryNormalizerTest extends TestCase
{
    use ServiceMockHelperTrait;

    /**
     * @param array{
     *       action: string,
     *       loggedAt: string,
     *       objectId: string,
     *       objectClass: string,
     *       changes: array<int,array{
     *           from: array{
     *               value: int,
     *               type: string
     *           },
     *           to: array{
     *               value: int,
     *               type: string
     *           }
     *       }>,
     *       userIdentifier: string|null,
     *       userIdentifierClass: string|null,
     *   } $expected
     * @param list<int|null> $normalizersData
     * @param list<int> $changesData
     */
    #[TestWith([
        [
            'action' => 'create',
            'type' => 'automatic',
            'loggedAt' => '2025-05-26T00:00:00.000000',
            'objectId' => '1',
            'objectClass' => 'TestClass',
            'changes' => [],
            'userIdentifier' => null,
            'userIdentifierClass' => null,
            'metadata' => [],
        ],
    ])]
    #[TestWith([
        [
            'action' => 'update',
            'type' => 'automatic',
            'loggedAt' => '2025-05-27T00:00:00.000000',
            'objectId' => '7',
            'objectClass' => 'TestClass1',
            'changes' => [
                0 => [
                    'from' => [
                        'value' => 1,
                        'type' => 'type',
                    ],
                    'to' => [
                        'value' => 1,
                        'type' => 'type',
                    ],
                ],
                1 => [
                    'from' => [
                        'value' => 1,
                        'type' => 'type',
                    ],
                    'to' => [
                        'value' => 1,
                        'type' => 'type',
                    ],
                ],
            ],
            'userIdentifier' => '11',
            'userIdentifierClass' => 'UserClass',
            'metadata' => [],
        ],
        [1, 1],
        [1, 1],
        ActionEnum::Update,
        new \DateTime('2025-05-27'),
        '7',
        'TestClass1',
        '11',
        'UserClass',
    ])]
    public function testNormalize(
        array $expected,
        array $normalizersData = [],
        array $changesData = [],
        ActionEnum $actionEnum = ActionEnum::Create,
        \DateTimeInterface $loggedAt = new \DateTime('2025-05-26'),
        string $objectId = '1',
        string $objectClass = 'TestClass',
        string|null $userId = null,
        string|null $userClass = null,
    ): void {
        $normalizers = [];
        $changes = [];

        foreach ($changesData as $data) {
            $change = $this->createMock(Change::class);
            $change->method('getTo')
                ->willReturn('to');
            $change->method('getFrom')
                ->willReturn('from');
            $changes[] = $change;
        }

        foreach ($normalizersData as $data) {
            $normalizer = $this->createMock(NormalizerInterface::class);
            $normalizer->method('normalize')
                ->willReturn(new Value($data, type: 'type'));
            $normalizers[] = $normalizer;
        }

        $service = $this->createRealMockedServiceInstance(EntryNormalizer::class, ['normalizers' => $normalizers, 'denormalizers' => []]);

        $entry = $this->createMock(Entry::class);
        $entry->method('getChanges')
            ->willReturn($changes);
        $entry->method('getAction')
            ->willReturn($actionEnum);
        $entry->method('getType')
            ->willReturn(TypeEnum::Automatic);
        $entry->method('getLoggedAt')
            ->willReturn($loggedAt);
        $entry->method('getObjectId')
            ->willReturn($objectId);
        $entry->method('getObjectClass')
            ->willReturn($objectClass);
        $entry->method('getUserIdentifier')
            ->willReturn($userId);
        $entry->method('getUserIdentifierClass')
            ->willReturn($userClass);

        static::assertSame($expected, $service->normalize($entry));
    }

    /**
     * @param EntryDocument $input
     * @param EntryDocument $expected
     * @param list<int> $denormalizersData
     */
    #[TestWith([
        [
            'action' => 'create',
            'type' => 'automatic',
            'loggedAt' => '2025-05-26T00:00:00.000000',
            'objectId' => '1',
            'objectClass' => 'TestClass',
            'changes' => [
                0 => [
                    'from' => [
                        'value' => 1,
                        'type' => 'type',
                    ],
                    'to' => [
                        'value' => 1,
                        'type' => 'type',
                    ],
                ],
                1 => [
                    'from' => [
                        'value' => 1,
                        'type' => 'type',
                    ],
                    'to' => [
                        'value' => 1,
                        'type' => 'type',
                    ],
                ],
            ],
            'userIdentifier' => null,
            'userIdentifierClass' => null,
            'documentId' => '1',
            'metadata' => [],
        ],
        [
            'action' => 'create',
            'type' => 'automatic',
            'loggedAt' => '2025-05-26T00:00:00.000000',
            'objectId' => '1',
            'objectClass' => 'TestClass',
            'changes' => [
                0 => [
                    'from' => [
                        'value' => 1,
                        'type' => 'type',
                    ],
                    'to' => [
                        'value' => 1,
                        'type' => 'type',
                    ],
                ],
                1 => [
                    'from' => [
                        'value' => 1,
                        'type' => 'type',
                    ],
                    'to' => [
                        'value' => 1,
                        'type' => 'type',
                    ],
                ],
            ],
            'userIdentifier' => null,
            'userIdentifierClass' => null,
            'documentId' => '1',
            'metadata' => [],
        ],
        [1, 1],
    ])]
    #[TestWith([
        [
            'action' => 'update',
            'type' => 'automatic',
            'loggedAt' => '2025-05-26T00:00:00.000000',
            'objectId' => '1',
            'objectClass' => 'TestClass',
            'changes' => [],
            'userIdentifier' => '1',
            'userIdentifierClass' => '1',
            'documentId' => '1',
            'metadata' => [],
        ],
        [
            'action' => 'update',
            'type' => 'automatic',
            'loggedAt' => '2025-05-26T00:00:00.000000',
            'objectId' => '1',
            'objectClass' => 'TestClass',
            'changes' => [],
            'userIdentifier' => '1',
            'userIdentifierClass' => '1',
            'documentId' => '1',
            'metadata' => [],
        ],
        [],
    ])]
    public function testDenormalize(
        array $input,
        array $expected,
        array $denormalizersData,
    ): void {
        $denormalizers = [];

        foreach ($denormalizersData as $data) {
            $denormalizer = $this->createMock(DenormalizerInterface::class);
            $denormalizer->method('denormalize')
                ->willReturn(new LoadedValue(new Value($data, type: 'type')));
            $denormalizers[] = $denormalizer;
        }

        $service = $this->createRealMockedServiceInstance(EntryNormalizer::class, ['normalizers' => [], 'denormalizers' => $denormalizers]);

        $result = $service->denormalize($input);

        $documentId = $input['documentId'] ?? 'invalid';
        static::assertInstanceOf(Entry::class, $result);
        static::assertSame($expected['objectId'], $result->getObjectId());
        static::assertSame($expected['action'], $result->getAction()->value);
        static::assertSame($expected['loggedAt'], $result->getLoggedAt()->format(EntryNormalizer::DATE_FORMAT_MICROTIME));
        static::assertSame($documentId, $result->getDocumentId());
        static::assertSame($expected['userIdentifier'], $result->getUserIdentifier());
        static::assertSame($expected['userIdentifierClass'], $result->getUserIdentifierClass());

        if (!empty($input['changes'])) {
            foreach ($result->getChanges() as $index => $change) {
                static::assertSame($expected['changes'][$index]['from']['value'], $change->getFrom()->value->value);
                static::assertSame($expected['changes'][$index]['from']['type'], $change->getFrom()->value->type);
                static::assertSame($expected['changes'][$index]['to']['value'], $change->getTo()->value->value);
                static::assertSame($expected['changes'][$index]['to']['type'], $change->getTo()->value->type);
            }
        }
    }
}
