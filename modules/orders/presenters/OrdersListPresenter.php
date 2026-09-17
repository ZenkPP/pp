<?php

declare(strict_types=1);

namespace modules\orders\presenters;

use modules\orders\models\OrderMode;
use modules\orders\models\OrderStatus;
use Yii;
use yii\base\InvalidConfigException;
use yii\data\ActiveDataProvider;
use yii\helpers\Url;

final readonly class OrdersListPresenter
{
    /**
     * @param ActiveDataProvider $dataProvider
     * @return list<array{
     *     id: int,
     *     userName: string,
     *     link: string,
     *     quantity: int,
     *     serviceId: int,
     *     serviceName: string,
     *     status: string,
     *     mode: string,
     *     createdDate: string,
     *     createdTime: string
     * }>
     * @throws InvalidConfigException
     */
    public function getOrders(ActiveDataProvider $dataProvider): array
    {
        $orderRows = [];

        foreach ($dataProvider->getModels() as $order) {
            $orderRows[] = [
                'id' => $order->id,
                'userName' => $order->user->getFullName(),
                'link' => $order->link,
                'quantity' => $order->quantity,
                'serviceId' => $order->service_id,
                'serviceName' => $order->service->name,
                'status' => Yii::t('orders', OrderStatus::from($order->status)->name),
                'mode' => Yii::t('orders', OrderMode::from($order->mode)->name),
                'createdDate' => Yii::$app->formatter->asDate($order->created_at, 'php:Y-m-d'),
                'createdTime' => Yii::$app->formatter->asTime($order->created_at, 'php:H:i:s'),
            ];
        }

        return $orderRows;
    }

    /**
     * @param string $route
     * @return string
     */
    public function getOrdersUrl(string $route): string
    {
        return Url::to([$route]);
    }
}
