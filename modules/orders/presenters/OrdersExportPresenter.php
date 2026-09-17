<?php

declare(strict_types=1);

namespace modules\orders\presenters;

use modules\orders\models\OrdersSearch;
use yii\helpers\Url;

final readonly class OrdersExportPresenter
{
    private const EXPORT_ROUTE = '/orders/order/export';

    /**
     * @param OrdersSearch $ordersSearch
     * @return string
     */
    public function getExportUrl(OrdersSearch $ordersSearch): string
    {
        return Url::to(array_merge(
            [self::EXPORT_ROUTE],
            $ordersSearch->getSearchParams(),
            $ordersSearch->getFilterParams(),
        ));
    }
}
