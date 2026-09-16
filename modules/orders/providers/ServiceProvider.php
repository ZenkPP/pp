<?php

declare(strict_types=1);

namespace modules\orders\providers;

use modules\orders\dto\OrderFilter;
use modules\orders\models\Order;
use modules\orders\models\Service;
use Yii;

class ServiceProvider
{
    public const CACHE_SERVICE_COUNT = 'CACHE_SERVICE_COUNT';
    private const CACHE_SERVICE_COUNT_LIMIT = 600;

    public function __construct(
        private readonly OrderQueryBuilder $orderQueryBuilder,
    ) {
    }

    /**
     * @return list<array{id: int, name: string, count: int}>
     */
    public function getServices(OrderFilter $orderFilter): array
    {
        $filtersWithoutService = new OrderFilter(
            status: $orderFilter->status,
            service: null,
            mode: $orderFilter->mode,
            search: $orderFilter->search,
        );

        $orderCounts = $this->orderQueryBuilder->getQuery($filtersWithoutService)
            ->select([
                'service_id' => '[[order.service_id]]',
                'count' => 'COUNT([[order.id]])',
            ])
            ->groupBy('[[order.service_id]]');

        return Service::find()
            ->alias('service')
            ->select([
                'id' => '[[service.id]]',
                'name' => '[[service.name]]',
                'count' => 'COALESCE([[orderCounts.count]], 0)',
            ])
            ->leftJoin(
                ['orderCounts' => $orderCounts],
                '[[orderCounts.service_id]] = [[service.id]]',
            )
            ->orderBy(['count' => SORT_DESC, 'id' => SORT_ASC])
            ->asArray()
            ->all();
    }

    public function countOrdersForServices(OrderFilter $orderFilter): int
    {
        if (
            $orderFilter->status === null
            && $orderFilter->mode === null
            && $orderFilter->search === null
        ) {
            return Yii::$app->cache->getOrSet(self::CACHE_SERVICE_COUNT, function () {
                return Order::find()->count();
            }, self::CACHE_SERVICE_COUNT_LIMIT);
        }

        $filtersWithoutService = new OrderFilter(
            status: $orderFilter->status,
            service: null,
            mode: $orderFilter->mode,
            search: $orderFilter->search,
        );

        return (int) $this->orderQueryBuilder->getQuery($filtersWithoutService)->count();
    }
}
