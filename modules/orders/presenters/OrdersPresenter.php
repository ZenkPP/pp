<?php

declare(strict_types=1);

namespace modules\orders\presenters;

use modules\orders\models\OrderMode;
use modules\orders\models\OrderSearchType;
use modules\orders\models\OrdersSearch;
use modules\orders\models\OrderStatus;
use modules\orders\providers\ServiceProvider;
use Yii;
use yii\base\InvalidConfigException;
use yii\data\ActiveDataProvider;
use yii\helpers\Url;

final readonly class OrdersPresenter
{
    private const EXPORT_ROUTE = '/orders/order/export';
    private const SEARCH_TYPE_ORDER_ID_LABEL = 'Order ID';
    private const SEARCH_TYPE_LINK_LABEL = 'Link';
    private const SEARCH_TYPE_USERNAME_LABEL = 'Username';

    public function __construct(
        private ServiceProvider $serviceProvider,
    ) {
    }

    /**
     * @param OrdersSearch $ordersSearch
     * @param string $route
     * @return list<array{label: string, url: string, active: bool}>
     */
    public function getStatusTabs(OrdersSearch $ordersSearch, string $route): array
    {
        $filters = $ordersSearch->getFilters();
        $statusParams = array_merge([$route], $ordersSearch->getSearchParams());
        $statusTabs = [[
            'label' => Yii::t('orders', 'All orders'),
            'url' => Url::to(array_merge($statusParams, ['status' => ''])),
            'active' => $filters['status'] === '',
        ]];

        foreach (OrderStatus::cases() as $status) {
            $statusName = strtolower($status->name);
            $statusTabs[] = [
                'label' => Yii::t('orders', $status->name),
                'url' => Url::to(array_merge($statusParams, ['status' => $statusName])),
                'active' => $filters['status'] === $statusName,
            ];
        }

        return $statusTabs;
    }

    /**
     * @param OrdersSearch $ordersSearch
     * @param string $route
     * @return list<array{id: ?int, name: string, count: int, url: ?string, class: string}>
     */
    public function getServiceFilters(OrdersSearch $ordersSearch, string $route): array
    {
        $filters = $ordersSearch->getFilters();
        $filterParams = array_merge(
            [$route],
            $ordersSearch->getSearchParams(),
            $ordersSearch->getFilterParams(),
        );
        $serviceFilters = [[
            'id' => null,
            'name' => Yii::t('orders', 'All'),
            'count' => $this->serviceProvider->countOrdersForServices($ordersSearch),
            'url' => Url::to(array_merge($filterParams, ['service' => null])),
            'class' => $filters['service'] === null ? 'active' : '',
        ]];

        $services = $this->serviceProvider->getServices($ordersSearch);
        foreach ($services as $service) {
            $serviceId = (int) $service['id'];
            $serviceCount = (int) $service['count'];
            $disabled = $serviceCount === 0;

            $serviceFilters[] = [
                'id' => $serviceId,
                'name' => $service['name'],
                'count' => $serviceCount,
                'url' => $disabled ? null : Url::to(array_merge($filterParams, ['service' => $serviceId])),
                'class' => trim(($filters['service'] === $serviceId ? 'active ' : '') . ($disabled ? 'disabled' : '')),
            ];
        }

        return $serviceFilters;
    }

    /**
     * @param OrdersSearch $ordersSearch
     * @param string $route
     * @return list<array{label: string, url: string, active: bool}>
     */
    public function getModeFilters(OrdersSearch $ordersSearch, string $route): array
    {
        $filters = $ordersSearch->getFilters();
        $filterParams = array_merge([$route], $ordersSearch->getSearchParams(), $ordersSearch->getFilterParams());
        $modeFilters = [[
            'label' => Yii::t('orders', 'All'),
            'url' => Url::to(array_merge($filterParams, ['mode' => null])),
            'active' => $filters['mode'] === null,
        ]];

        foreach (OrderMode::cases() as $mode) {
            $modeFilters[] = [
                'label' => Yii::t('orders', $mode->name),
                'url' => Url::to(array_merge($filterParams, ['mode' => $mode->value])),
                'active' => $filters['mode'] === $mode->value,
            ];
        }

        return $modeFilters;
    }

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

    /**
     * @param string $route
     * @return string
     */
    public function getOrdersUrl(string $route): string
    {
        return Url::to([$route]);
    }

    /**
     * @param OrdersSearch $ordersSearch
     * @param string $route
     * @return array{
     *     action: string,
     *     value: string,
     *     types: list<array{value: int, label: string, selected: bool}>
     * }
     */
    public function getSearchForm(OrdersSearch $ordersSearch, string $route): array
    {
        $filters = $ordersSearch->getFilters();
        $selectedSearchType = $filters['search-type'] ?? OrderSearchType::OrderID->value;
        $searchTypes = [];

        foreach (OrderSearchType::cases() as $searchType) {
            $label = match ($searchType) {
                OrderSearchType::OrderID => self::SEARCH_TYPE_ORDER_ID_LABEL,
                OrderSearchType::Link => self::SEARCH_TYPE_LINK_LABEL,
                OrderSearchType::User => self::SEARCH_TYPE_USERNAME_LABEL,
            };
            $searchTypes[] = [
                'value' => $searchType->value,
                'label' => Yii::t('orders', $label),
                'selected' => $selectedSearchType === $searchType->value,
            ];
        }

        return [
            'action' => Url::to([$route, 'status' => $filters['status']]),
            'value' => (string) $filters['search'],
            'types' => $searchTypes,
        ];
    }

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
}
