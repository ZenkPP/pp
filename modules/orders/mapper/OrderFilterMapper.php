<?php

declare(strict_types=1);

namespace modules\orders\mapper;

use modules\orders\dto\OrderFilter;
use modules\orders\dto\OrderSearch;
use modules\orders\models\OrderMode;
use modules\orders\models\OrderSearchType;
use modules\orders\models\OrderStatus;
use yii\web\Request;

class OrderFilterMapper
{
    public function map(Request $request): OrderFilter
    {
        $statusParam = $request->getQueryParam('status');
        $statusValue = $statusParam !== null && $statusParam !== '' ? OrderStatus::fromString($statusParam) : null;

        $modeParam = $request->get('mode');
        $modeValue = $modeParam !== null && $modeParam !== '' ? OrderMode::tryFrom((int) $modeParam) : null;

        $serviceParam = $request->get('service');
        $serviceValue = $serviceParam !== null && $serviceParam !== '' ? (int) $serviceParam : null;

        $searchTypeParam = $request->get('search-type');
        $searchTypeValue = $searchTypeParam !== null && $searchTypeParam !== '' ? OrderSearchType::tryFrom((int) $searchTypeParam) : null;
        $searchParam = $request->get('search');
        $searchValue = $searchTypeValue && $searchParam !== null & $searchParam !== '' ? new OrderSearch($searchParam, $searchTypeValue) : null;

        return new OrderFilter(
            $statusValue,
            $serviceValue,
            $modeValue,
            $searchValue,
        );
    }
}
