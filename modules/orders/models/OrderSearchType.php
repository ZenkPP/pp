<?php

declare(strict_types=1);

namespace modules\orders\models;

enum OrderSearchType: int
{
    case OrderID = 1;
    case Link = 2;
    case User = 3;
}
