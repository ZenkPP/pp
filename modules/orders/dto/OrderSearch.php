<?php

declare(strict_types=1);

namespace modules\orders\dto;

use modules\orders\models\OrderSearchType;

class OrderSearch
{
    public function __construct(
        public ?string $search = null,
        public ?OrderSearchType $searchType = null,
    ) {
    }
}
