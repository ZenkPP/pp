<?php

declare(strict_types=1);

namespace modules\orders\mapper;

use modules\orders\dto\OrderFilter;
use modules\orders\dto\OrderSearch;
use modules\orders\models\OrderMode;
use modules\orders\models\OrderSearchType;
use modules\orders\models\OrderStatus;
use yii\web\BadRequestHttpException;
use yii\web\Request;

final class OrderFilterMapper
{
    /**
     * @throws BadRequestHttpException
     */
    public function map(Request $request): OrderFilter
    {
        return new OrderFilter(
            status: $this->getStatus($request),
            service: $this->getService($request),
            mode: $this->getMode($request),
            search: $this->getSearch($request),
        );
    }

    /**
     * @throws BadRequestHttpException
     */
    private function getStatus(Request $request): ?OrderStatus
    {
        $status = $this->getStringQueryParam($request, 'status');

        try {
            return $status === null ? null : OrderStatus::fromString($status);
        } catch (\InvalidArgumentException) {
            throw new BadRequestHttpException("Unknown order status $status");
        }
    }

    /**
     * @throws BadRequestHttpException
     */
    private function getMode(Request $request): ?OrderMode
    {
        $modeParam = $this->getIntegerQueryParam($request, 'mode');
        $mode = $modeParam === null ? null : OrderMode::tryFrom($modeParam);
        if ($modeParam !== null && $mode === null) {
            throw new BadRequestHttpException("Unknown order mode $modeParam");
        }

        return $mode;
    }

    /**
     * @throws BadRequestHttpException
     */
    private function getService(Request $request): ?int
    {
        $serviceValue = $this->getIntegerQueryParam($request, 'service');
        if ($serviceValue !== null && $serviceValue < 1) {
            throw new BadRequestHttpException('Service ID must be positive');
        }

        return $serviceValue;
    }

    /**
     * @throws BadRequestHttpException
     */
    private function getSearch(Request $request): ?OrderSearch
    {
        $searchTypeParam = $this->getIntegerQueryParam($request, 'search-type');
        $searchType = $searchTypeParam === null ? null : OrderSearchType::tryFrom($searchTypeParam);
        if ($searchTypeParam !== null && $searchType === null) {
            throw new BadRequestHttpException('Unknown search type');
        }

        $searchParam = trim($this->getStringQueryParam($request, 'search') ?? '');

        return match (true) {
            $searchParam === '' => null,
            $searchType === null => throw new BadRequestHttpException('Search type is required.'),
            $searchType === OrderSearchType::OrderID && !ctype_digit($searchParam) => throw new BadRequestHttpException('Order ID must be an integer'),
            default => new OrderSearch($searchParam, $searchType),
        };
    }

    /**
     * @throws BadRequestHttpException
     */
    private function getStringQueryParam(Request $request, string $name): ?string
    {
        $value = $request->getQueryParam($name);
        if ($value === null || $value === '') {
            return null;
        }

        if (!is_string($value)) {
            throw new BadRequestHttpException("Query parameter '$name' must be a string");
        }

        return $value;
    }

    /**
     * @throws BadRequestHttpException
     */
    private function getIntegerQueryParam(Request $request, string $name): ?int
    {
        $value = $this->getStringQueryParam($request, $name);
        if ($value === null) {
            return null;
        }

        if (!ctype_digit($value)) {
            throw new BadRequestHttpException("Query parameter '$name' must be an integer");
        }

        return (int) $value;
    }
}
