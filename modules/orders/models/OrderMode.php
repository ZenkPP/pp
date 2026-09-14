<?php

namespace modules\orders\models;

enum OrderMode: int
{
    case Manual = 0;
    case Auto = 1;
}
