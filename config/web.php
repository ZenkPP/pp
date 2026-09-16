<?php

$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/db.php';

$config = [
    'id' => 'basic',
    'basePath' => dirname(__DIR__),
    'language' => $_ENV['APP_LANGUAGE'] ?? 'en',
    'bootstrap' => ['log'],
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
        '@modules' => '@app/modules',
    ],
    'components' => [
        'request' => [
            'cookieValidationKey' => $_ENV['COOKIE_VALIDATION_KEY'],
        ],
        'cache' => [
            'class' => \yii\caching\FileCache::class,
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => \yii\log\FileTarget::class,
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'db' => $db,
        'i18n' => [
            'translations' => [
                'orders' => [
                    'class' => yii\i18n\PhpMessageSource::class,
                    'sourceLanguage' => 'en',
                    'forceTranslation' => true,
                    'basePath' => '@modules/orders/messages',
                ],
            ],
        ],
        'view' => [
            'class' => yii\web\View::class,
            'renderers' => [
                'twig' => [
                    'class' => yii\twig\ViewRenderer::class,
                    'cachePath' => '@runtime/Twig/cache',
                    'options' => [
                        'auto_reload' => true,
                    ],
                ],
            ],
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => require __DIR__ . '/routes.php',
        ],
    ],
    'modules' => [
        'users' => [
            'class' => modules\users\UsersModule::class,
        ],
        'orders' => [
            'class' => modules\orders\OrdersModule::class,
        ],
    ],
    'params' => $params,
];

return $config;
