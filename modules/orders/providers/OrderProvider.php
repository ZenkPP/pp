<?php

declare(strict_types=1);

namespace modules\orders\providers;

use modules\orders\dto\OrderFilter;
use modules\orders\models\Order;
use yii\data\Pagination;

readonly class OrderProvider
{
    private const EXPORT_BATCH_SIZE = 1000;

    /**
     * @param OrderQueryBuilder $orderQueryBuilder
     */
    public function __construct(
        private OrderQueryBuilder $orderQueryBuilder,
    ) {
    }

    /**
     * @param OrderFilter $orderFilter
     * @return int
     */
    public function countFilteredOrders(OrderFilter $orderFilter): int
    {
        return (int) $this->orderQueryBuilder->getQuery($orderFilter)->count();
    }

    /**
     * @param OrderFilter $orderFilter
     * @param Pagination $pagination
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
     * @param OrderFilter $orderFilter
     * @return \Generator<int, Order>
     */
    public function iterateOrders(OrderFilter $orderFilter): \Generator
    {
        $orders = $this->orderQueryBuilder->getQuery($orderFilter)
            ->orderBy(['order.id' => SORT_DESC])
            ->with(['user', 'service'])
            ->each(self::EXPORT_BATCH_SIZE);

        foreach ($orders as $order) {
            yield $order;
        }
    }
}
