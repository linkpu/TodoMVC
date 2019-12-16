<?php
$config = [
    'id' => 'basic',
    'basePath' => dirname(__DIR__),
    'components' => [
        'request' => [
            'cookieValidationKey' => 'vaDJo6drs-cdSlJSSteoRLU7iKxrp4Dq',
            'parsers' => [
                'application/json' => 'yii\web\JsonParser',
            ],
        ],
        'response' => [
            'format' => 'json'
        ],
        'cache' => [
            'class' => 'yii\redis\Cache',
            'redis' => [
                'hostname' => '127.0.0.1',
                'port' => '6379',
                'database' => 0
            ],
        ],
        'user' => [
            'identityClass' => 'app\models\User',
            'enableAutoLogin' => true,
            'enableSession' => false,
            'loginUrl' => null,
        ],
        'mongodb' => [
            'class' => 'yii\mongodb\Connection',
            'dsn' => 'mongodb://localhost:27017/homework'
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'enableStrictParsing' => true,
            'showScriptName' => false,
            'rules' => [
                'POST login' => 'site/login',
                'POST register' => 'site/register',
                'POST logout' => 'site/logout',
                'POST todos' => 'todo/create',
                'GET todos' => 'todo/all',
                'GET todos/starts' => 'todo/starts',
                'OPTIONS,PATCH todos/<todoId:[^/]*>/status' => 'todo/change-one-status',
                'OPTIONS,PATCH todos/status' => 'todo/change-all-status',
                'GET todos/ends' => 'todo/ends',
                'OPTIONS,DELETE todos/ends' => 'todo/clear-ends',
                'OPTIONS,DELETE todos/<id:[^/]*>' => 'todo/delete-todo-by-id',
                'OPTIONS,PATCH todos/<id:[^/]*>/description' => 'todo/description',
            ],
        ],
    ],
];
return $config;
