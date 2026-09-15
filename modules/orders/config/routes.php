<?php

return [
    [
        'pattern' => 'orders/export',
        'route' => 'orders/order/export',
    ],
    [
        'pattern' => 'orders/<status:pending|inprogress|completed|canceled|error>',
        'route' => 'orders/order/index',
        'defaults' => ['status' => ''],
    ],
];
