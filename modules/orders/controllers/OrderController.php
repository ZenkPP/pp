<?php

declare(strict_types=1);

namespace modules\orders\controllers;

use modules\orders\mapper\OrderFilterMapper;
use modules\orders\models\OrderMode;
use modules\orders\models\OrderStatus;
use modules\orders\providers\OrderProvider;
use modules\orders\providers\ServiceProvider;
use modules\orders\services\OrderCsvExporter;
use Yii;
use yii\base\Module;
use yii\data\Pagination;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\Request;
use yii\web\Response;

class OrderController extends Controller
{
    private const DEFAULT_PAGE_SIZE = 100;

    /**
     * @param string $id
     * @param Module $module
     * @param OrderProvider $orderProvider
     * @param ServiceProvider $serviceProvider
     * @param OrderFilterMapper $orderFilterMapper
     * @param OrderCsvExporter $orderCsvExporter
     * @param array $config
     */
    public function __construct(
        $id,
        $module,
        private readonly OrderProvider $orderProvider,
        private readonly ServiceProvider $serviceProvider,
        private readonly OrderFilterMapper $orderFilterMapper,
        private readonly OrderCsvExporter $orderCsvExporter,
        array $config = [],
    ) {
        parent::__construct($id, $module, $config);
    }

    /**
     * @param Request $request
     * @return string
     * @throws BadRequestHttpException
     */
    public function actionIndex(Request $request): string
    {
        $orderFilter = $this->orderFilterMapper->map($request);

        $pagination = new Pagination([
            'totalCount' => $this->orderProvider->countFilteredOrders($orderFilter),
            'defaultPageSize' => self::DEFAULT_PAGE_SIZE,
            'pageSizeLimit' => false,
            'forcePageParam' => false,
        ]);

        return $this->renderPartial('@app/views/site/orders.twig', [
            'ordersRoute' => '/' . $this->getRoute(),
            'statuses' => OrderStatus::cases(),
            'modes' => OrderMode::cases(),
            'filters' => [
                'status' => $orderFilter->status !== null ? strtolower($orderFilter->status->name) : '',
                'service' => $orderFilter->service,
                'mode' => $orderFilter->mode?->value,
                'search' => $orderFilter->search?->search,
                'search-type' => $orderFilter->search?->searchType?->value,
            ],
            'orders' => $this->orderProvider->getOrders($orderFilter, $pagination),
            'pagination' => $pagination,
            'serviceTotalCount' => $this->serviceProvider->countOrdersForServices($orderFilter),
            'services' => $this->serviceProvider->getServices($orderFilter),
        ]);
    }

    /**
     * @param Request $request
     * @return Response
     * @throws BadRequestHttpException
     */
    public function actionExport(Request $request): Response
    {
        $orderFilter = $this->orderFilterMapper->map($request);

        $response = Yii::$app->response;
        $response->format = Response::FORMAT_RAW;
        $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="orders.csv"');
        $response->stream = fn (): \Generator => $this->orderCsvExporter->export($orderFilter);

        return $response;
    }
}
