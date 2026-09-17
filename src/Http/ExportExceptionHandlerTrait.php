<?php

declare(strict_types=1);

namespace Mheads\Yii2\Table\Http;

use Mheads\Yii\Table\Provider\TableProviderInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

trait ExportExceptionHandlerTrait
{
	/**
	 * @return callable(Throwable, TableProviderInterface, ServerRequestInterface, string): ?ResponseInterface
	 */
	protected function createExportExceptionHandler(): callable
	{
		return fn(
			Throwable $exception,
			TableProviderInterface $table,
			ServerRequestInterface $request,
			string $exportCode,
		): ?ResponseInterface => $this->handleExportException($exception, $table, $request, $exportCode);
	}

	protected function handleExportException(
		Throwable $exception,
		TableProviderInterface $table,
		ServerRequestInterface $request,
		string $exportCode,
	): ?ResponseInterface {
		return null;
	}
}
