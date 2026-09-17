<?php

declare(strict_types=1);

namespace Mheads\Yii2\Table\Http;

use yii\web\BadRequestHttpException;
use Yiisoft\Data\Paginator\InvalidPageException;
use Yiisoft\Data\Paginator\PageNotFoundException;

trait PaginationExceptionHandlerTrait
{
	/**
	 * @throws BadRequestHttpException
	 */
	protected function handlePageNotFoundException(PageNotFoundException $e): never
	{
		$message = $e->getMessage();
		$message = preg_match('~^Page \d+ not found\.$~', $message) ? $message : 'Page not found.';

		throw new BadRequestHttpException($message, 0, $e);
	}

	/**
	 * @throws BadRequestHttpException
	 */
	protected function handleInvalidPageException(InvalidPageException $e): never
	{
		throw new BadRequestHttpException('Incorrect page number', 0, $e);
	}
}
