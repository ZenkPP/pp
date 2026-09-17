<?php

declare(strict_types=1);

namespace modules\orders\models;

use modules\users\models\User;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;

final class OrdersSearch extends Model
{
    private const PAGE_SIZE = 100;
    private const EXPORT_BATCH_SIZE = 1000;
    private const INTEGER_PATTERN = '/^[0-9]+$/D';

    public mixed $status = null;
    public mixed $service = null;
    public mixed $mode = null;
    public mixed $search = null;
    public mixed $searchType = null;

    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            [
                ['status', 'service', 'mode', 'search', 'searchType'],
                'string',
                'skipOnEmpty' => false,
                'when' => static fn (self $model, string $attribute): bool => $model->$attribute !== null,
            ],
            ['search', 'trim'],
            [
                'status',
                'filter',
                'filter' => 'strtolower',
                'skipOnEmpty' => true
            ],
            [
                ['status', 'service', 'mode', 'search', 'searchType'],
                'default',
                'value' => null
            ],
            [
                'service',
                'integer',
                'integerPattern' => self::INTEGER_PATTERN,
                'min' => 1,
                'max' => PHP_INT_MAX,
            ],
            [
                'status',
                'in',
                'range' => array_map('strtolower', array_column(OrderStatus::cases(), 'name')),
                'strict' => true,
            ],
            [
                'mode',
                'in',
                'range' => array_map('strval', array_column(OrderMode::cases(), 'value')),
                'strict' => true,
            ],
            [
                'searchType',
                'in',
                'range' => array_map('strval', array_column(OrderSearchType::cases(), 'value')),
                'strict' => true,
            ],
            [
                'searchType',
                'required',
                'when' => static fn (self $model): bool => !$model->hasErrors('search') && $model->search !== null,
            ],
            [
                'search',
                'match',
                'pattern' => self::INTEGER_PATTERN,
                'when' => static fn (self $model): bool => $model->searchType === (string) OrderSearchType::OrderID->value,
            ],
        ];
    }

    /**
     * @param array<string, mixed> $params
     * @return void
     */
    public function loadFilters(array $params): void
    {
        $this->load([
            'status' => $params['status'] ?? null,
            'service' => $params['service'] ?? null,
            'mode' => $params['mode'] ?? null,
            'search' => $params['search'] ?? null,
            'searchType' => $params['search-type'] ?? null,
        ], '');
    }

    /**
     * @return ActiveDataProvider
     * @throws \InvalidArgumentException
     */
    public function getActiveDataProvider(): ActiveDataProvider
    {
        return new ActiveDataProvider([
            'query' => $this->getListingQuery(),
            'sort' => false,
            'pagination' => [
                'pageSize' => self::PAGE_SIZE,
                'defaultPageSize' => self::PAGE_SIZE,
                'pageSizeLimit' => false,
                'forcePageParam' => false,
            ],
        ]);
    }

    /**
     * @return \Generator<int, Order>
     * @throws \InvalidArgumentException
     */
    public function iterateOrders(): \Generator
    {
        /** @var Order $order */
        foreach ($this->getListingQuery()->each(self::EXPORT_BATCH_SIZE) as $order) {
            yield $order;
        }
    }

    /**
     * @return ActiveQuery
     * @throws \InvalidArgumentException
     */
    public function getQuery(): ActiveQuery
    {
        $query = Order::find()->alias('order');

        if (!$this->validate()) {
            return $query->where('0 = 1');
        }

        $status = $this->status === null ? null : OrderStatus::fromString($this->status);
        $query->andFilterWhere([
            'order.status' => $status?->value,
            'order.service_id' => $this->service,
            'order.mode' => $this->mode,
        ]);

        if ($this->search !== null) {
            match (OrderSearchType::from((int) $this->searchType)) {
                OrderSearchType::OrderID => $query->andWhere(['order.id' => $this->search]),
                OrderSearchType::Link => $query->andWhere(['order.link' => $this->search]),
                OrderSearchType::User => $this->applyUserNameSearch($query),
            };
        }

        return $query;
    }

    /**
     * @param ActiveQuery $query
     * @return void
     */
    private function applyUserNameSearch(ActiveQuery $query): void
    {
        $words = preg_split('/\s+/', $this->search, -1, PREG_SPLIT_NO_EMPTY);
        $query->innerJoin(['user' => User::tableName()], 'user.id = order.user_id');

        foreach ($words as $word) {
            $query->andWhere([
                'or',
                ['like', 'user.first_name', $word],
                ['like', 'user.last_name', $word],
            ]);
        }
    }

    /**
     * @return ActiveQuery
     * @throws \InvalidArgumentException
     */
    private function getListingQuery(): ActiveQuery
    {
        return $this->getQuery()
            ->orderBy(['order.id' => SORT_DESC])
            ->with(['user', 'service']);
    }

    /**
     * @return array{status: string, service: ?int, mode: ?int, search: ?string, search-type: ?int}
     */
    public function getFilters(): array
    {
        return [
            'status' => $this->status ?? '',
            'service' => $this->service === null ? null : (int) $this->service,
            'mode' => $this->mode === null ? null : (int) $this->mode,
            'search' => $this->search,
            'search-type' => $this->search === null ? null : (int) $this->searchType,
        ];
    }

    /**
     * @return array{search?: string, search-type?: int}
     */
    public function getSearchParams(): array
    {
        $filters = $this->getFilters();

        return $filters['search'] === null ? [] : [
            'search' => $filters['search'],
            'search-type' => $filters['search-type'],
        ];
    }

    /**
     * @return array{status: string, service: ?int, mode: ?int}
     */
    public function getFilterParams(): array
    {
        $filters = $this->getFilters();

        return [
            'status' => $filters['status'],
            'service' => $filters['service'],
            'mode' => $filters['mode'],
        ];
    }
}
