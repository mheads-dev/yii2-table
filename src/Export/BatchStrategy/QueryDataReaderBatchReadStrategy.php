<?php

declare(strict_types=1);

namespace Mheads\Yii2\Table\Export\BatchStrategy;

use InvalidArgumentException;
use Mheads\Yii\Table\Export\BatchStrategy\BatchStrategyInterface;
use Mheads\Yii2DataDb\QueryDataReaderInterface;
use Override;
use Yiisoft\Data\Reader\ReadableDataInterface;

final readonly class QueryDataReaderBatchReadStrategy implements BatchStrategyInterface
{
	/**
	 * @param positive-int $batchSize
	 */
	public function __construct(
		private int $batchSize,
	) {}

	#[Override]
	public function canRead(ReadableDataInterface $reader): bool
	{
		return $reader instanceof QueryDataReaderInterface;
	}

	#[Override]
	public function readBatched(ReadableDataInterface $reader): iterable
	{
		if (!$reader instanceof QueryDataReaderInterface)
		{
			throw new InvalidArgumentException(
				'Reader is not compatible with Yii2 QueryDataReader, got: ' . get_debug_type($reader) . '.',
			);
		}

		yield from $reader
			->withBatchSize($this->batchSize)
			->read();
	}
}
