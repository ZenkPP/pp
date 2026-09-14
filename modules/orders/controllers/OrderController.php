<?php

namespace modules\orders\controllers;

use modules\orders\models\OrderMode;
use modules\orders\models\OrderStatus;
use yii\web\Controller;
use yii\web\Request;

class OrderController extends Controller
{
    public function actionIndex(Request $request, string $status = ''): string
    {
        return $this->renderPartial('@app/views/site/orders.twig', [
            'ordersRoute' => '/' . $this->getRoute(),
            'statuses' => OrderStatus::cases(),
            'modes' => OrderMode::cases(),
            'filters' => [
                'status' => $status,
                'service' => $request->get('service', ''),
                'mode' => $request->get('mode', ''),
                'search' => $request->get('search', ''),
                'search-type' => $request->get('search-type', '1'),
            ],
        ]);
    }
}
