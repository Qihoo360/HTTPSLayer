<?php
require(__DIR__ . '/define.php'); // 宏定义文件

$params = require(__DIR__ . '/params.php');
$db = require(__DIR__ . '/db.php');
$log = require(__DIR__ . '/log.php');
$cache = require(__DIR__ . '/cache.php');
$config = [
    'id' => 'basic',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'defaultRoute' => '/login',
    'language' => 'zh-CN',
    'timeZone' => 'Asia/Shanghai',
    'components' => [
        'request' => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => '7v9m0MB9XKhrVoZ5xHzK64PdjKliY54n',
        ],
//        'session' => [
//            'class' => 'yii\web\DbSession',
//            'db' => 'db',  // 数据库连接的应用组件ID，默认为'db'.
//            'sessionTable' => 'session_access', // session 数据表名，默认为'session'.
//        ],
        'session' => [
            'class' => 'yii\web\CacheSession',
            'cookieParams' => ['lifetime' => 4 * 3600],
        ],

        'cache' => $cache,
        'user' => [
            'identityClass' => 'app\models\BizUser',
            'enableAutoLogin' => true,
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            // send all mails to a file by default. You have to set
            // 'useFileTransport' to false and configure a transport
            // for the mailer to send real emails.
            'useFileTransport' => true,
        ],
        'log' => $log,
        'db' => $db,
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
            ],
        ],
        'assetManager' => [
            'hashCallback' => function ($path) {
                return str_replace(ROOT_PATH . "/vendor/", "", $path);
            }
        ],
        'authClientCollection' => [
            'class' => 'yii\authclient\Collection',
            'clients' => [],
        ]
    ],
    'params' => $params,
];

$auth_method = safe_get_str($params, 'authmethod');

if ($auth_method == 'oauth') {
    $auth2 = safe_get_array($params, 'auth2');
    $clients = safe_get_array($auth2, 'clients');
    $auth_clients = [];
    if (!empty($clients)) {
        foreach ($clients as $client) {
            $name = safe_get_str($client, 'name');
            $class = safe_get_str($client, 'class');
            $clientId = safe_get_str($client, 'clientId');
            $clientSecret = safe_get_str($client, 'clientSecret');
            $imgUrl = safe_get_str($client, 'imgUrl');
            $auth_clients[$name] = [
                'class' => $class,
                'clientId' => $clientId,
                'clientSecret' => $clientSecret,
            ];
        }
    }
    $config['components']['authClientCollection']['clients']= $auth_clients;
}

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        //'allowedIPs' => ['127.0.0.1', '::1'],
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        'allowedIPs' => ['127.0.0.1', '::1'],
    ];
}
$config['modules']['gridview'] = [
    'class' => '\kartik\grid\Module',
    // 'downloadAction' => 'export',
];

return $config;
