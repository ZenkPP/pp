<?php

declare(strict_types=1);

namespace modules\orders\controllers;

use modules\orders\models\OrdersSearch;
use modules\orders\presenters\OrdersPresenter;
use modules\orders\services\OrderCsvExporter;
use Yii;
use yii\base\InvalidConfigException;
use yii\base\Module;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\Request;
use yii\web\Response;

class OrderController extends Controller
{
    /**
     * @param string $id
     * @param Module $module
     * @param OrdersSearch $ordersSearch
     * @param OrderCsvExporter $orderCsvExporter
     * @param OrdersPresenter $ordersPresenter
     * @param array $config
     */
    public function __construct(
        $id,
        $module,
        private readonly OrdersSearch $ordersSearch,
        private readonly OrderCsvExporter $orderCsvExporter,
        private readonly OrdersPresenter $ordersPresenter,
        array $config = [],
    ) {
        parent::__construct($id, $module, $config);
    }

    /**
     * @param Request $request
     * @return string
     * @throws BadRequestHttpException
     * @throws InvalidConfigException
     */
    public function actionIndex(Request $request): string
    {
        $this->ordersSearch->loadFilters($request->getQueryParams());

        if (!$this->ordersSearch->validate()) {
            throw new BadRequestHttpException(implode(' ', $this->ordersSearch->getFirstErrors()));
        }

        $route = '/' . $this->getRoute();
        $dataProvider = $this->ordersSearch->getActiveDataProvider();

        return $this->renderPartial('@app/views/site/orders', [
            'ordersUrl' => $this->ordersPresenter->getOrdersUrl($route),
            'statusTabs' => $this->ordersPresenter->getStatusTabs($this->ordersSearch, $route),
            'serviceFilters' => $this->ordersPresenter->getServiceFilters($this->ordersSearch, $route),
            'modeFilters' => $this->ordersPresenter->getModeFilters($this->ordersSearch, $route),
            'searchForm' => $this->ordersPresenter->getSearchForm($this->ordersSearch, $route),
            'exportUrl' => $this->ordersPresenter->getExportUrl($this->ordersSearch),
            'orders' => $this->ordersPresenter->getOrders($dataProvider),
            'pagination' => $dataProvider->getPagination(),
        ]);
    }

    /**
     * @param Request $request
     * @return Response
     * @throws BadRequestHttpException
     */
    public function actionExport(Request $request): Response
    {
        $this->ordersSearch->loadFilters($request->getQueryParams());

        if (!$this->ordersSearch->validate()) {
            throw new BadRequestHttpException(implode(' ', $this->ordersSearch->getFirstErrors()));
        }

        $response = Yii::$app->response;
        $response->format = Response::FORMAT_RAW;
        $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="orders.csv"');
        $response->stream = fn (): \Generator => $this->orderCsvExporter->export($this->ordersSearch->iterateOrders());

        return $response;
    }
}
