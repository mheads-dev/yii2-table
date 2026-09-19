<?php

declare(strict_types=1);

namespace Mheads\Yii2\Table\I18n;

use Mheads\Yii\Table\I18n\TableTranslatorInterface;
use Override;
use Yii;

final readonly class Yii2TableTranslator implements TableTranslatorInterface
{
	public function __construct(
		private string $category = TableTranslatorInterface::CATEGORY,
	) {}

	/**
	 * @param array<string, mixed> $parameters
	 */
	#[Override]
	public function translate(string $id, array $parameters = []): string
	{
		return Yii::t($this->category, $id, $parameters);
	}
}
