<?php

declare(strict_types=1);

use yii\data\Pagination;
use yii\helpers\Html;
use yii\widgets\LinkPager;

/** @var list<array{
 *     id: int,
 *     userName: string,
 *     link: string,
 *     quantity: int,
 *     serviceId: int,
 *     serviceName: string,
 *     status: string,
 *     mode: string,
 *     createdDate: string,
 *     createdTime: string
 * }> $orders */
/** @var Pagination|false|null $pagination */
/** @var list<array{id: ?int, name: string, count: int, url: ?string, class: string}> $serviceFilters */
/** @var string $ordersUrl */
/** @var list<array{label: string, url: string, active: bool}> $statusTabs */
/** @var list<array{label: string, url: string, active: bool}> $modeFilters */
/** @var array{
 *     action: string,
 *     value: string,
 *     types: list<array{value: int, label: string, selected: bool}>
 * } $searchForm */
/** @var string $exportUrl */
?>
<!DOCTYPE html>
<html lang="<?= Html::encode(Yii::$app->language) ?>">
<head>
    <meta charset="<?= Html::encode(Yii::$app->charset) ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= Html::encode(Yii::t('orders', 'Orders')) ?></title>
    <link href="<?= Html::encode(Yii::$app->request->baseUrl) ?>/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= Html::encode(Yii::$app->request->baseUrl) ?>/css/custom.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-fixed-top navbar-default">
    <div class="container-fluid">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-navbar-collapse" aria-controls="bs-navbar-collapse" aria-expanded="false">
                <span class="sr-only"><?= Html::encode(Yii::t('orders', 'Toggle navigation')) ?></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
        </div>
        <div class="collapse navbar-collapse" id="bs-navbar-collapse">
            <ul class="nav navbar-nav">
                <li class="active"><a href="<?= Html::encode($ordersUrl) ?>"><?= Html::encode(Yii::t('orders', 'Orders')) ?></a></li>
            </ul>
        </div>
    </div>
</nav>
<div class="container-fluid">
    <ul class="nav nav-tabs p-b">
        <?php foreach ($statusTabs as $statusTab): ?>
            <li<?= $statusTab['active'] ? ' class="active"' : '' ?>><a href="<?= Html::encode($statusTab['url']) ?>"><?= Html::encode($statusTab['label']) ?></a></li>
        <?php endforeach; ?>
        <li class="pull-right custom-search">
            <form class="form-inline" action="<?= Html::encode($searchForm['action']) ?>" method="get">
                <div class="input-group">
                    <input type="text" name="search" class="form-control" value="<?= Html::encode($searchForm['value']) ?>" placeholder="<?= Html::encode(Yii::t('orders', 'Search orders')) ?>" aria-label="<?= Html::encode(Yii::t('orders', 'Search orders')) ?>">
                    <span class="input-group-btn search-select-wrap">
                        <select class="form-control search-select" name="search-type" aria-label="<?= Html::encode(Yii::t('orders', 'Search by')) ?>">
                            <?php foreach ($searchForm['types'] as $searchType): ?>
                                <option value="<?= $searchType['value'] ?>"<?= $searchType['selected'] ? ' selected' : '' ?>><?= Html::encode($searchType['label']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" class="btn btn-default" aria-label="<?= Html::encode(Yii::t('orders', 'Search')) ?>"><span class="glyphicon glyphicon-search" aria-hidden="true"></span></button>
                    </span>
                </div>
            </form>
        </li>
        <li class="pull-right export-li">
            <a class="export" href="<?= Html::encode($exportUrl) ?>">
                <span class="glyphicon glyphicon-download-alt" aria-hidden="true"></span>
                <span class="export-title"><?= Html::encode(Yii::t('orders', 'Save result')) ?></span>
            </a>
        </li>
    </ul>
    <table class="table order-table">
        <thead>
        <tr>
            <th><?= Html::encode(Yii::t('orders', 'ID')) ?></th>
            <th><?= Html::encode(Yii::t('orders', 'User')) ?></th>
            <th><?= Html::encode(Yii::t('orders', 'Link')) ?></th>
            <th><?= Html::encode(Yii::t('orders', 'Quantity')) ?></th>
            <th class="dropdown-th">
                <div class="dropdown">
                    <button class="btn btn-th btn-default dropdown-toggle" type="button" id="service-filter" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <?= Html::encode(Yii::t('orders', 'Service')) ?>
                        <span class="caret"></span>
                    </button>
                    <ul class="dropdown-menu" aria-labelledby="service-filter">
                        <?php foreach ($serviceFilters as $serviceFilter): ?>
                            <li<?= $serviceFilter['class'] === '' ? '' : ' class="' . Html::encode($serviceFilter['class']) . '"' ?>>
                                <?php if ($serviceFilter['url'] === null): ?>
                                    <a aria-disabled="true" tabindex="-1">
                                <?php else: ?>
                                    <a href="<?= Html::encode($serviceFilter['url']) ?>">
                                <?php endif; ?>
                                    <?php if ($serviceFilter['id'] !== null): ?>
                                        <span class="label-id"><?= $serviceFilter['id'] ?></span>
                                    <?php endif; ?>
                                    <?= Html::encode($serviceFilter['name']) ?> (<?= $serviceFilter['count'] ?>)
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </th>
            <th><?= Html::encode(Yii::t('orders', 'Status')) ?></th>
            <th class="dropdown-th">
                <div class="dropdown">
                    <button class="btn btn-th btn-default dropdown-toggle" type="button" id="mode-filter" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <?= Html::encode(Yii::t('orders', 'Mode')) ?>
                        <span class="caret"></span>
                    </button>
                    <ul class="dropdown-menu" aria-labelledby="mode-filter">
                        <?php foreach ($modeFilters as $modeFilter): ?>
                            <li<?= $modeFilter['active'] ? ' class="active"' : '' ?>><a href="<?= Html::encode($modeFilter['url']) ?>"><?= Html::encode($modeFilter['label']) ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </th>
            <th><?= Html::encode(Yii::t('orders', 'Created')) ?></th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($orders as $order): ?>
            <tr>
                <td><?= $order['id'] ?></td>
                <td><?= Html::encode($order['userName']) ?></td>
                <td class="link"><?= Html::encode($order['link']) ?></td>
                <td><?= $order['quantity'] ?></td>
                <td class="service"><span class="label-id"><?= $order['serviceId'] ?></span> <?= Html::encode($order['serviceName']) ?></td>
                <td><?= Html::encode($order['status']) ?></td>
                <td><?= Html::encode($order['mode']) ?></td>
                <td><span class="nowrap"><?= Html::encode($order['createdDate']) ?></span><span class="nowrap"><?= Html::encode($order['createdTime']) ?></span></td>
            </tr>
        <?php endforeach; ?>
        <?php if ($orders === []): ?>
            <tr><td colspan="8" class="text-center"><?= Html::encode(Yii::t('orders', 'No orders found')) ?></td></tr>
        <?php endif; ?>
        </tbody>
    </table>
    <?php if ($pagination instanceof Pagination): ?>
        <div class="row">
            <div class="col-sm-8">
                <?php if ($pagination->getPageCount() > 1): ?>
                    <nav aria-label="<?= Html::encode(Yii::t('orders', 'Orders pagination')) ?>">
                        <?= LinkPager::widget(['pagination' => $pagination]) ?>
                    </nav>
                <?php endif; ?>
            </div>
            <div class="col-sm-4 pagination-counters">
                <?= Html::encode(Yii::t('orders', '{begin} to {end} of {total}', [
                    'begin' => $pagination->totalCount > 0 ? $pagination->offset + 1 : 0,
                    'end' => $pagination->offset + count($orders),
                    'total' => $pagination->totalCount,
                ])) ?>
            </div>
        </div>
    <?php endif; ?>
</div>
<script src="<?= Html::encode(Yii::$app->request->baseUrl) ?>/js/jquery.min.js"></script>
<script src="<?= Html::encode(Yii::$app->request->baseUrl) ?>/js/bootstrap.min.js"></script>
</body>
</html>
