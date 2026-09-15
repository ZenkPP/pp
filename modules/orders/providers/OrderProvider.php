<?php

declare(strict_types=1);

namespace modules\orders\providers;

use modules\orders\dto\OrderFilter;
use modules\orders\models\Order;
use modules\orders\models\OrderSearchType;
use modules\orders\models\Service;
use modules\users\models\User;
use Yii;
use yii\data\Pagination;
use yii\db\ActiveQuery;

class OrderProvider
{
    public const CACHE_ORDERS_COUNT = 'CACHE_ORDERS_COUNT';
    private const CACHE_ORDERS_COUNT_LIMIT = 600;

    public function countFilteredOrders(OrderFilter $orderFilter): int
    {
        return (int) $this->getQuery($orderFilter)->count();
    }

    public function getOrders(OrderFilter $orderFilter, Pagination $pagination): array
    {
        return $this->getQuery($orderFilter)
            ->orderBy(['order.id' => SORT_DESC])
            ->offset($pagination->offset)
            ->limit($pagination->limit)
            ->with(['user', 'service'])
            ->all();
    }

    /**
     * @return \Generator<int, Order>
     */
    public function iterateOrders(OrderFilter $orderFilter): \Generator
    {
        foreach ($this->getQuery($orderFilter)
            ->orderBy(['order.id' => SORT_DESC])
            ->with(['user', 'service'])
            ->each(1000) as $order
        ) {
            yield $order;
        }
    }

    public function countOrdersForServices(OrderFilter $orderFilter): int
    {
        if (
            $orderFilter->status === null
            && $orderFilter->mode === null
            && $orderFilter->search === null
        ) {
            return Yii::$app->cache->getOrSet(self::CACHE_ORDERS_COUNT, function () {
                return Order::find()->count();
            }, self::CACHE_ORDERS_COUNT_LIMIT);
        }

        return (int) $this->getQuery($orderFilter, false)->count();
    }

    public function getServices(OrderFilter $orderFilter): array
    {
        $orderCounts = $this->getQuery($orderFilter, false)
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

    private function getQuery(OrderFilter $orderFilter, bool $withServiceFilter = true): ActiveQuery
    {
        $query = Order::find()
            ->alias('order')
        ;

        if ($orderFilter->status !== null) {
            $query->andWhere(['order.status' => $orderFilter->status->value]);
        }

        if ($orderFilter->mode !== null) {
            $query->andWhere(['order.mode' => $orderFilter->mode->value]);
        }

        if ($withServiceFilter && $orderFilter->service !== null) {
            $query->andWhere(['order.service_id' => $orderFilter->service]);
        }

        if ($orderFilter->search) {
            match ($orderFilter->search->searchType) {
                OrderSearchType::OrderID => $query->andWhere(['order.id' => $orderFilter->search->search]),
                OrderSearchType::Link => $query->andWhere(['order.link' => $orderFilter->search->search]),
                OrderSearchType::User => $this->getSubQueryForName($orderFilter, $query),
            };
        }

        return $query;
    }

    private function getSubQueryForName(OrderFilter $orderFilter, ActiveQuery $query): void
    {
        $words = preg_split('/\s+/', trim($orderFilter->search->search), -1, PREG_SPLIT_NO_EMPTY);

        $query->innerJoin(
            ['user' => User::tableName()],
            '[[user.id]] = [[order.user_id]]',
        );

        foreach ($words as $word) {
            $query->andWhere([
                'or',
                ['like', '[[user.first_name]]', $word],
                ['like', '[[user.last_name]]', $word],
            ]);
        }
    }
}
