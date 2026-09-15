<?php

declare(strict_types=1);

namespace modules\orders\dto;

use modules\orders\models\OrderMode;
use modules\orders\models\OrderStatus;

class OrderFilter
{
    public function __construct(
        public ?OrderStatus $status = null,
        public ?int $service = null,
        public ?OrderMode $mode = null,
        public ?OrderSearch $search = null,
    ) {
    }
}
