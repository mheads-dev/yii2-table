<?php

declare(strict_types=1);

namespace Mheads\Yii2\Table\Http;

use Mheads\Yii\Table\Http\Orchestrator\TableHttpOrchestrator;
use Mheads\Yii\Table\Provider\TableConfiguratorInterface;
use Mheads\Yii\Table\Provider\TableProviderInterface;
use Mheads\Yii2Psr7Bridge\HttpMessageBridgeInterface;
use yii\base\Action;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\Response;
use Yiisoft\Data\Paginator\InvalidPageException;
use Yiisoft\Data\Paginator\PageNotFoundException;

/**
 * @extends Action<Controller>
 */
abstract class TableAction extends Action
{
	use ExportExceptionHandlerTrait;
	use PaginationExceptionHandlerTrait;

	/**
	 * @param array<string, mixed> $config
	 */
	public function __construct(
		string $id,
		Controller $controller,
		protected readonly TableHttpOrchestrator $orchestrator,
		protected readonly HttpMessageBridgeInterface $bridge,
		array $config = [],
	) {
		parent::__construct($id, $controller, $config);
	}

	abstract protected function createTable(): TableProviderInterface&TableConfiguratorInterface;

	/**
	 * @throws BadRequestHttpException
	 */
	public function run(): Response
	{
		try
		{
			$psrResponse = $this->orchestrator->respond(
				$this->createTable(),
				$this->bridge->toPsrRequest($this->controller->request),
				$this->createExportExceptionHandler(),
			);

			return $this->bridge->toYiiResponse($psrResponse, $this->controller->response);
		}
		catch (PageNotFoundException $e)
		{
			$this->handlePageNotFoundException($e);
		}
		catch (InvalidPageException $e)
		{
			$this->handleInvalidPageException($e);
		}
	}
}
