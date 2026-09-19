# mheads/yii2-table

Связующий слой для использования
[`mheads/yii-table`](https://github.com/mheads-dev/yii-table) в
Yii2-приложениях.

Пакет добавляет:

- действия Yii2 для HTTP-таблиц: `TableAction`, `TableConfigAction`,
  `TableRowsAction`, `TableExportAction`;
- опциональную поддержку запросов Yii DB как источника данных таблицы через
  [`mheads/yii2-data-db`](https://github.com/mheads-dev/yii2-data-db);
- адаптер пакетного чтения `QueryDataReaderBatchReadStrategy` для экспорта из
  источников `mheads/yii2-data-db`;
- единое преобразование Yii-запросов и ответов через `mheads/yii2-psr7-bridge`;
- точку расширения для обработки ошибок экспорта в `TableAction` и
  `TableExportAction`.

## Установка

```bash
composer require mheads/yii2-table
```

Также нужна реализация PSR-7 и PSR-17, например:

```bash
composer require nyholm/psr7
```

Если таблица читает данные из Yii DB или использует
`QueryDataReaderBatchReadStrategy`, установите дополнительный пакет:

```bash
composer require mheads/yii2-data-db
```

## Настройка DI

В конфигурации Yii2-приложения зарегистрируйте зависимости `mheads/yii-table`,
`mheads/yii2-psr7-bridge` и фабрики PSR-17.

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
            \Mheads\Yii\Table\I18n\TableTranslatorInterface::class                        => \Mheads\Yii2\Table\I18n\Yii2TableTranslator::class,

            // mheads/yii2-psr7-bridge
            \Mheads\Yii2Psr7Bridge\HttpMessageBridgeInterface::class                    => \Mheads\Yii2Psr7Bridge\HttpMessageBridge::class,
            \Mheads\Yii2Psr7Bridge\Request\Yii2ToPsr7RequestConverterInterface::class   => \Mheads\Yii2Psr7Bridge\Request\Yii2ToPsr7RequestConverter::class,
            \Mheads\Yii2Psr7Bridge\Response\Psr7ToYii2ResponseConverterInterface::class => \Mheads\Yii2Psr7Bridge\Response\Psr7ToYii2ResponseConverter::class,
        ],
    ],
];
```

## Переводы фильтров

Фильтры `mheads/yii-table` используют
`Mheads\Yii\Table\I18n\TableTranslatorInterface` для локализации системных
подписей. `Yii2TableTranslator` адаптирует этот интерфейс к `Yii::t()`.

Зарегистрируйте категорию переводов Yii2:

```php
return [
    'components' => [
        'i18n' => [
            'translations' => [
                'mheads-yii-table' => [
                    'class' => \yii\i18n\PhpMessageSource::class,
                    'basePath' => '@vendor/mheads/yii-table/resources/messages',
                ],
            ],
        ],
    ],
];
```

Пакет `mheads/yii-table` уже содержит стандартные переводы. Если приложению
нужно переопределить тексты, укажите собственный `basePath`, например
`@common/i18n/messages`.

## Быстрый пример

Создайте действие, унаследованное от `TableAction`, и верните таблицу из
`createTable()`.

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

Подключите действие в контроллере:

```php
public function actions(): array
{
    return [
        'table' => [
            'class' => \app\actions\ProductsTableAction::class,
        ],
    ];
}
```

Подробное руководство: [docs/guide-ru/README.md](docs/guide-ru/README.md).
