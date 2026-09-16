<?php

declare(strict_types=1);

namespace modules\orders\providers;

use modules\orders\dto\OrderFilter;
use modules\orders\models\Order;
use yii\data\Pagination;

readonly class OrderProvider
{
    public function __construct(
        private OrderQueryBuilder $orderQueryBuilder,
    ) {
    }

    public function countFilteredOrders(OrderFilter $orderFilter): int
    {
        return (int) $this->orderQueryBuilder->getQuery($orderFilter)->count();
    }

    /**
     * @return array<int, Order>
     */
    public function getOrders(OrderFilter $orderFilter, Pagination $pagination): array
    {
        return $this->orderQueryBuilder->getQuery($orderFilter)
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
        $orders = $this->orderQueryBuilder->getQuery($orderFilter)
            ->orderBy(['order.id' => SORT_DESC])
            ->with(['user', 'service'])
            ->each(1000);

        foreach ($orders as $order) {
            yield $order;
        }
    }
}
