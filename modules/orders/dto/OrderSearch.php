<?php

declare(strict_types=1);

namespace modules\orders\dto;

use modules\orders\models\OrderSearchType;

final readonly class OrderSearch
{
    /**
     * @param string $search
     * @param OrderSearchType $searchType
     */
    public function __construct(
        public string $search,
        public OrderSearchType $searchType,
    ) {
    }
}
