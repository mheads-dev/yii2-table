# Руководство по использованию mheads/yii2-table

Этот пакет нужен, когда таблица описана через
[`mheads/yii-table`](https://github.com/mheads-dev/yii-table), а HTTP-слой
приложения остается на Yii2.

Для таблиц на основе Yii DB установите
[`mheads/yii2-data-db`](https://github.com/mheads-dev/yii2-data-db): эта
библиотека позволяет использовать запросы Yii DB как источник данных таблицы.

Типовая схема:

1. В DI регистрируются сервисы `mheads/yii-table`, фабрики PSR-17 и
   `mheads/yii2-psr7-bridge`.
2. В контроллере Yii2 действие подключается через `actions()`.
3. Класс действия приложения наследуется от одного из действий пакета.
4. Фабрика приложения создает и настраивает `TableProvider`.
5. Для пакетного экспорта данных из Yii DB можно использовать
   `QueryDataReaderBatchReadStrategy`.

## Доступные действия

`Mheads\Yii2\Table\Http\TableAction`
: Возвращает полную таблицу: конфигурацию, колонки, фильтры, сортировки,
пагинацию и строки. Если в запросе передан параметр экспорта, может вернуть
файл экспорта.

`Mheads\Yii2\Table\Http\TableConfigAction`
: Возвращает только конфигурацию таблицы.

`Mheads\Yii2\Table\Http\TableRowsAction`
: Возвращает строки и пагинацию.

`Mheads\Yii2\Table\Http\TableExportAction`
: Отдельная точка входа HTTP только для экспорта.

Все действия наследуются от `yii\base\Action` и требуют реализовать:

```php
protected function createTable(): TableProviderInterface&TableConfiguratorInterface;
```

## Настройка DI

Пример для `config/web.php`, `config/main.php` или другого конфигурационного
файла Yii2:

```php
return [
    'container' => [
        'definitions' => [
            // PSR-17
            \Psr\Http\Message\ResponseFactoryInterface::class      => \Nyholm\Psr7\Factory\Psr17Factory::class,
            \Psr\Http\Message\ServerRequestFactoryInterface::class => \Nyholm\Psr7\Factory\Psr17Factory::class,
            \Psr\Http\Message\StreamFactoryInterface::class        => \Nyholm\Psr7\Factory\Psr17Factory::class,

            // mheads/yii-table
            \Mheads\Yii\Table\Http\Request\TableRequestApplierInterface::class            => \Mheads\Yii\Table\Http\Request\TableRequestApplier::class,
            \Mheads\Yii\Table\Http\Response\TableHttpResponderInterface::class            => \Mheads\Yii\Table\Http\Response\TableHttpResponder::class,
            \Mheads\Yii\Table\Http\Response\Payload\TablePayloadResponderInterface::class => \Mheads\Yii\Table\Http\Response\Payload\JsonTablePayloadResponder::class,
            \Mheads\Yii\Table\Serialization\TableSerializerInterface::class               => \Mheads\Yii\Table\Serialization\TableArraySerializer::class,
            \Mheads\Yii\Table\Serialization\TableConfigSerializerInterface::class         => \Mheads\Yii\Table\Serialization\TableArraySerializer::class,
            \Mheads\Yii\Table\Serialization\TableRowsSerializerInterface::class           => \Mheads\Yii\Table\Serialization\TableArraySerializer::class,

            // mheads/yii2-psr7-bridge
            \Mheads\Yii2Psr7Bridge\HttpMessageBridgeInterface::class                    => \Mheads\Yii2Psr7Bridge\HttpMessageBridge::class,
            \Mheads\Yii2Psr7Bridge\Request\Yii2ToPsr7RequestConverterInterface::class   => \Mheads\Yii2Psr7Bridge\Request\Yii2ToPsr7RequestConverter::class,
            \Mheads\Yii2Psr7Bridge\Response\Psr7ToYii2ResponseConverterInterface::class => \Mheads\Yii2Psr7Bridge\Response\Psr7ToYii2ResponseConverter::class,
        ],
    ],
];
```

## Контроллер

Действие подключается обычным Yii2 способом:

```php
<?php

declare(strict_types=1);

namespace app\controllers;

use app\actions\ProductsTableAction;
use yii\web\Controller;

final class ProductsController extends Controller
{
    public function actions(): array
    {
        return [
            'table' => [
                'class' => ProductsTableAction::class,
            ],
        ];
    }
}
```

## Действие таблицы

Класс действия приложения наследуется от нужного действия пакета. Например, от
`TableAction`, если точка входа HTTP должна вернуть полную таблицу.

```php
<?php

declare(strict_types=1);

namespace app\actions;

use app\tables\ProductsTableFactory;
use Mheads\Yii\Table\Http\Orchestrator\TableHttpOrchestrator;
use Mheads\Yii\Table\Provider\TableConfiguratorInterface;
use Mheads\Yii\Table\Provider\TableProviderInterface;
use Mheads\Yii2\Table\Http\TableAction;
use Mheads\Yii2Psr7Bridge\HttpMessageBridgeInterface;
use yii\web\Controller;

final class ProductsTableAction extends TableAction
{
    public function __construct(
        string $id,
        Controller $controller,
        TableHttpOrchestrator $orchestrator,
        HttpMessageBridgeInterface $bridge,
        private readonly ProductsTableFactory $tableFactory,
        array $config = [],
    ) {
        parent::__construct($id, $controller, $orchestrator, $bridge, $config);
    }

    protected function createTable(): TableProviderInterface&TableConfiguratorInterface
    {
        return $this->tableFactory->create();
    }
}
```

Если для строк, конфигурации или экспорта нужны отдельные точки входа HTTP,
используйте соответствующий родительский класс:

```php
use Mheads\Yii\Table\Http\Orchestrator\TableRowsHttpOrchestrator;
use Mheads\Yii2\Table\Http\TableRowsAction;

final class ProductsTableRowsAction extends TableRowsAction
{
    public function __construct(
        string $id,
        Controller $controller,
        TableRowsHttpOrchestrator $orchestrator,
        HttpMessageBridgeInterface $bridge,
        private readonly ProductsTableFactory $tableFactory,
        array $config = [],
    ) {
        parent::__construct($id, $controller, $orchestrator, $bridge, $config);
    }

    protected function createTable(): TableProviderInterface&TableConfiguratorInterface
    {
        return $this->tableFactory->create();
    }
}
```

## Фабрика таблицы

Фабрика собирает `TableProvider`: источник данных, колонки, фильтры, сортировки,
пагинацию и экспорты.

```php
<?php

declare(strict_types=1);

namespace app\tables;

use app\models\Product;
use Mheads\Yii\Table\Column\Column;
use Mheads\Yii\Table\Export\Column\ExportColumnMode;
use Mheads\Yii\Table\Export\TableBoundExportGeneratorFactory;
use Mheads\Yii\Table\Export\TableBoundExportOptions;
use Mheads\Yii\Table\Filter\SearchFilter;
use Mheads\Yii\Table\Provider\TableProvider;
use Mheads\Yii\Table\Sort\SortDefinition;
use Mheads\Yii2\Table\Export\BatchStrategy\QueryDataReaderBatchReadStrategy;
use Mheads\Yii2DataDb\QueryDataReader;

final class ProductsTableFactory
{
    public function __construct(
        private readonly TableBoundExportGeneratorFactory $exportFactory = new TableBoundExportGeneratorFactory(),
    ) {}

    public function create(): TableProvider
    {
        $query = Product::find();

        $table = new TableProvider(
            id: 'products-list',
            reader: new QueryDataReader($query),
        );

        $table->setPageSize(20);
        $table->setPageSizeConstraint([10, 20, 30, 40, 50]);
        $table->setIgnoreMissingPage(false);

        $table->addColumn(
            new Column(
                key: 'id',
                title: 'ID',
                reader: static fn(Product $product): int => $product->id,
                isId: true,
            ),
        );

        $table->addColumn(
            new Column(
                key: 'name',
                title: 'Название',
                reader: static fn(Product $product): string => $product->name,
                sort: SortDefinition::byField('name', SORT_ASC),
                filter: new SearchFilter('name', 'Название', 'name'),
            ),
        );

        $table->addColumn(
            new Column(
                key: 'price',
                title: 'Цена',
                reader: static fn(Product $product): int => $product->price,
                sort: SortDefinition::byField('price', SORT_ASC),
            ),
        );

        $this->addExportGenerators($table);

        return $table;
    }

    private function addExportGenerators(TableProvider $table): void
    {
        $options = new TableBoundExportOptions(
            columnsMode: ExportColumnMode::MERGE,
            timeoutSeconds: 20,
            fileName: 'products-' . date('Y-m-d_H-i-s'),
            batchStrategy: new QueryDataReaderBatchReadStrategy(500),
        );

        $table->addExportGenerator(
            $this->exportFactory->csv($table, $options),
        );

        $table->addExportGenerator(
            $this->exportFactory->xlsx(
                table: $table,
                options: $options,
                sheetName: 'Товары',
            ),
        );
    }
}
```

## Экспорт и QueryDataReaderBatchReadStrategy

`QueryDataReaderBatchReadStrategy` нужен для экспорта больших выборок из
`Mheads\Yii2DataDb\QueryDataReaderInterface`.

Стратегия:

- проверяет, что источник данных реализует `QueryDataReaderInterface`;
- вызывает `withBatchSize($batchSize)`;
- читает данные через `read()`;
- выбрасывает `InvalidArgumentException`, если источник данных несовместим.

Пример:

```php
use Mheads\Yii\Table\Export\TableBoundExportOptions;
use Mheads\Yii2\Table\Export\BatchStrategy\QueryDataReaderBatchReadStrategy;

$options = new TableBoundExportOptions(
    batchStrategy: new QueryDataReaderBatchReadStrategy(500),
);
```

## Обработка ошибок экспорта

`TableAction` и `TableExportAction` позволяют переопределить обработку ошибок
экспорта через protected-метод `handleExportException()`.

Верните PSR-7-ответ, если ошибка обработана кодом приложения. Верните `null`,
чтобы сохранить стандартную обработку `mheads/yii-table`.

```php
use Mheads\Yii\Table\Provider\TableProviderInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

protected function handleExportException(
    Throwable $exception,
    TableProviderInterface $table,
    ServerRequestInterface $request,
    string $exportCode,
): ?ResponseInterface {
    $this->logger->error('Table export failed.', [
        'tableId' => $table->id(),
        'exportCode' => $exportCode,
        'exception' => $exception,
    ]);

    return null;
}
```

## Обработка ошибок пагинации

Действия по умолчанию превращают ошибки пагинации в
`yii\web\BadRequestHttpException`:

- `PageNotFoundException`: `Page 7 not found.` или `Page not found.`;
- `InvalidPageException`: `Incorrect page number`.

Если приложению нужна своя обработка, переопределите protected-метод:

```php
use yii\web\NotFoundHttpException;
use Yiisoft\Data\Paginator\InvalidPageException;
use Yiisoft\Data\Paginator\PageNotFoundException;

protected function handlePageNotFoundException(PageNotFoundException $e): never
{
    throw new NotFoundHttpException('Страница не найдена.', 0, $e);
}

protected function handleInvalidPageException(InvalidPageException $e): never
{
    throw new NotFoundHttpException('Некорректная страница.', 0, $e);
}
```

## Какое действие выбрать

Используйте `TableAction`, если клиентское приложение ожидает одну точку входа
HTTP для всего: конфигурации, строк и экспорта.

Используйте `TableConfigAction` и `TableRowsAction`, если клиентское приложение
загружает конфигурацию и строки отдельно.

Используйте `TableExportAction`, если экспорт должен быть доступен по отдельному
URL.
