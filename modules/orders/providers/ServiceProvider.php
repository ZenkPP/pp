<?php

declare(strict_types=1);

namespace modules\orders\providers;

use modules\orders\models\Order;
use modules\orders\models\OrdersSearch;
use modules\orders\models\Service;
use Yii;

class ServiceProvider
{
    public const CACHE_SERVICE_COUNT = 'CACHE_SERVICE_COUNT';
    private const CACHE_SERVICE_COUNT_TTL = 600;

    /**
     * @param OrdersSearch $searchModel
     * @return list<array{id: int, name: string, count: int}>
     * @throws \InvalidArgumentException
     */
    public function getServices(OrdersSearch $searchModel): array
    {
        $filtersWithoutService = clone $searchModel;
        $filtersWithoutService->service = null;

        $orderCounts = $filtersWithoutService->getQuery()
            ->select([
                'service_id' => 'order.service_id',
                'count' => 'COUNT(order.id)',
            ])
            ->groupBy('order.service_id');

        return Service::find()
            ->alias('service')
            ->select([
                'id' => 'service.id',
                'name' => 'service.name',
                'count' => 'COALESCE(orderCounts.count, 0)',
            ])
            ->leftJoin(
                ['orderCounts' => $orderCounts],
                'orderCounts.service_id = service.id',
            )
            ->orderBy(['count' => SORT_DESC, 'id' => SORT_ASC])
            ->asArray()
            ->all();
    }

    /**
     * @param OrdersSearch $searchModel
     * @return int
     * @throws \InvalidArgumentException
     */
    public function countOrdersForServices(OrdersSearch $searchModel): int
    {
        if (
            $searchModel->status === null
            && $searchModel->mode === null
            && $searchModel->search === null
        ) {
            return (int) Yii::$app->cache->getOrSet(self::CACHE_SERVICE_COUNT, static function (): int {
                return (int) Order::find()->count();
            }, self::CACHE_SERVICE_COUNT_TTL);
        }

        $filtersWithoutService = clone $searchModel;
        $filtersWithoutService->service = null;

        return (int) $filtersWithoutService->getQuery()->count();
    }
}
