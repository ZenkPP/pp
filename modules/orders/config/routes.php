<?php

return [
    [
        'pattern' => 'orders/export',
        'route' => 'orders/order/export',
    ],
    [
        'pattern' => 'orders/<status>',
        'route' => 'orders/order/index',
        'defaults' => ['status' => ''],
    ],
];
