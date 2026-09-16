<?php

declare(strict_types=1);

namespace modules\orders\services;

use modules\orders\dto\OrderFilter;
use modules\orders\models\OrderMode;
use modules\orders\models\OrderStatus;
use modules\orders\providers\OrderProvider;
use Yii;
use yii\base\InvalidConfigException;

final readonly class OrderCsvExporter
{
    public function __construct(
        private OrderProvider $orderProvider,
    ) {
    }

    /**
     * @return \Generator<int, string>
     *
     * @throws InvalidConfigException
     */
    public function export(OrderFilter $orderFilter): \Generator
    {
        yield "\xEF\xBB\xBF";
        yield $this->createCsvLine([
            Yii::t('orders', 'ID'),
            Yii::t('orders', 'User'),
            Yii::t('orders', 'Link'),
            Yii::t('orders', 'Quantity'),
            Yii::t('orders', 'Service'),
            Yii::t('orders', 'Status'),
            Yii::t('orders', 'Mode'),
            Yii::t('orders', 'Created'),
        ]);

        foreach ($this->orderProvider->iterateOrders($orderFilter) as $order) {
            yield $this->createCsvLine([
                $order->id,
                $order->user->first_name . ' ' . $order->user->last_name,
                $order->link,
                $order->quantity,
                $order->service->name,
                Yii::t('orders', OrderStatus::from($order->status)->name),
                Yii::t('orders', OrderMode::from($order->mode)->name),
                Yii::$app->formatter->asDatetime($order->created_at, 'php:Y-m-d H:i:s'),
            ]);
        }
    }

    private function createCsvLine(array $columns): string
    {
        $stream = fopen('php://temp', 'w+b');

        fputcsv($stream, $columns, ',', '"', '');
        rewind($stream);
        $line = stream_get_contents($stream);
        fclose($stream);

        return $line === false ? '' : $line;
    }
}
