<?php

declare(strict_types=1);

namespace modules\orders\controllers;

use modules\orders\models\OrdersSearch;
use modules\orders\presenters\OrdersFilterPresenter;
use modules\orders\presenters\OrdersListPresenter;
use modules\orders\presenters\OrdersExportPresenter;
use modules\orders\services\OrderCsvExporter;
use Yii;
use yii\base\InvalidConfigException;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\Request;
use yii\web\Response;

class OrderController extends Controller
{
    /**
     * @param Request $request
     * @return string
     * @throws InvalidConfigException
     */
    public function actionIndex(Request $request): string
    {
        $ordersSearch = Yii::createObject(OrdersSearch::class);
        $ordersExportPresenter = Yii::createObject(OrdersExportPresenter::class);
        $ordersFilterPresenter = Yii::createObject(OrdersFilterPresenter::class);
        $ordersListPresenter = Yii::createObject(OrdersListPresenter::class);

        $ordersSearch->loadFilters($request->getQueryParams());

        $route = '/' . $this->getRoute();
        $dataProvider = $ordersSearch->getActiveDataProvider();

        return $this->renderPartial('@app/views/site/orders', [
            'ordersUrl' => $ordersListPresenter->getOrdersUrl($route),
            'statusTabs' => $ordersFilterPresenter->getStatusTabs($ordersSearch, $route),
            'serviceFilters' => $ordersFilterPresenter->getServiceFilters($ordersSearch, $route),
            'modeFilters' => $ordersFilterPresenter->getModeFilters($ordersSearch, $route),
            'searchForm' => $ordersFilterPresenter->getSearchForm($ordersSearch, $route),
            'exportUrl' => $ordersExportPresenter->getExportUrl($ordersSearch),
            'orders' => $ordersListPresenter->getOrders($dataProvider),
            'pagination' => $dataProvider->getPagination(),
        ]);
    }

    /**
     * @param Request $request
     * @return Response
     * @throws BadRequestHttpException
     * @throws InvalidConfigException
     */
    public function actionExport(Request $request): Response
    {
        $ordersSearch = Yii::createObject(OrdersSearch::class);
        $orderCsvExporter = Yii::createObject(OrderCsvExporter::class);

        $ordersSearch->loadFilters($request->getQueryParams());

        if (!$ordersSearch->validate()) {
            throw new BadRequestHttpException(implode(' ', $ordersSearch->getFirstErrors()));
        }

        $response = Yii::$app->response;
        $response->format = Response::FORMAT_RAW;
        $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="orders.csv"');
        $response->stream = fn (): \Generator => $orderCsvExporter->export($ordersSearch->iterateOrders());

        return $response;
    }
}
