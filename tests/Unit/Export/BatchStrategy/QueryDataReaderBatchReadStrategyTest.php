<?php

declare(strict_types=1);

namespace Mheads\Yii2\Table\Tests\Unit\Export\BatchStrategy;

use InvalidArgumentException;
use Mheads\Yii2\Table\Export\BatchStrategy\QueryDataReaderBatchReadStrategy;
use Mheads\Yii2DataDb\QueryDataReaderInterface;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;
use Yiisoft\Data\Reader\ReadableDataInterface;

/**
 * @internal
 */
#[AllowMockObjectsWithoutExpectations]
final class QueryDataReaderBatchReadStrategyTest extends TestCase
{
	public function testCanReadReturnsTrueForQueryDataReader(): void
	{
		$reader = $this->createMock(QueryDataReaderInterface::class);
		$strategy = new QueryDataReaderBatchReadStrategy(100);

		self::assertTrue($strategy->canRead($reader));
	}

	public function testCanReadReturnsFalseForOtherReadableData(): void
	{
		$reader = $this->createMock(ReadableDataInterface::class);
		$strategy = new QueryDataReaderBatchReadStrategy(100);

		self::assertFalse($strategy->canRead($reader));
	}

	public function testReadBatchedAppliesBatchSizeAndReadsData(): void
	{
		$reader = $this->createMock(QueryDataReaderInterface::class);
		$strategy = new QueryDataReaderBatchReadStrategy(50);
		$rows = [
			['id' => 1],
			['id' => 2],
		];

		$reader
			->expects(self::once())
			->method('withBatchSize')
			->with(50)
			->willReturnSelf();

		$reader
			->expects(self::once())
			->method('read')
			->willReturn($rows);

		self::assertSame($rows, iterator_to_array($strategy->readBatched($reader)));
	}

	public function testReadBatchedFailsForIncompatibleReader(): void
	{
		$reader = $this->createMock(ReadableDataInterface::class);
		$strategy = new QueryDataReaderBatchReadStrategy(100);

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Reader is not compatible with Yii2 QueryDataReader');

		iterator_to_array($strategy->readBatched($reader));
	}
}
