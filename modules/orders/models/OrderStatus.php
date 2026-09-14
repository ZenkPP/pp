<?php

namespace modules\orders\models;

enum OrderStatus: int
{
    case Pending = 0;
    case InProgress = 1;
    case Completed = 2;
    case Canceled = 3;
    case Error = 4;
}
