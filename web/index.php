<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../config/bootstrap.php';

defined('YII_DEBUG') or define(
    'YII_DEBUG',
    filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOL),
);
defined('YII_ENV') or define('YII_ENV', $_ENV['APP_ENV'] ?? (YII_DEBUG ? 'dev' : 'prod'));

require __DIR__ . '/../vendor/yiisoft/yii2/Yii.php';

$config = require __DIR__ . '/../config/web.php';

(new yii\web\Application($config))->run();
