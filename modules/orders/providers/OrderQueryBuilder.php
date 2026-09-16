<?php

declare(strict_types=1);

namespace modules\orders\providers;

use modules\orders\dto\OrderFilter;
use modules\orders\models\Order;
use modules\orders\models\OrderSearchType;
use modules\users\models\User;
use yii\db\ActiveQuery;

class OrderQueryBuilder
{
    public function getQuery(OrderFilter $orderFilter): ActiveQuery
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

        if ($orderFilter->service !== null) {
            $query->andWhere(['order.service_id' => $orderFilter->service]);
        }

        if ($orderFilter->search) {
            match ($orderFilter->search->searchType) {
                OrderSearchType::OrderID => $query->andWhere(['order.id' => $orderFilter->search->search]),
                OrderSearchType::Link => $query->andWhere(['order.link' => $orderFilter->search->search]),
                OrderSearchType::User => $this->applyUserNameSearch($orderFilter, $query),
            };
        }

        return $query;
    }

    private function applyUserNameSearch(OrderFilter $orderFilter, ActiveQuery $query): void
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
