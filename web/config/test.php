<?php

require(__DIR__ . '/define.php'); // 宏定义文件
require_once __DIR__."/test_env.php";

$params = require(__DIR__ . '/params.php');
$db = require(__DIR__ . '/test_db.php');

/**
 * Application configuration shared by all test types
 */
return [
    'id' => 'basic-tests',
    'basePath' => dirname(__DIR__),
    'defaultRoute' => '/login',
    'language' => 'zh-CN',
    'timeZone' => 'Asia/Shanghai',
    'components' => [
        'db' => $db,
        'mailer' => [
            'useFileTransport' => true,
        ],
        'assetManager' => [            
            'basePath' => __DIR__ . '/../web/assets',
        ],
        'urlManager' => [
            'showScriptName' => true,
        ],
        'user' => [
            'identityClass' => 'app\models\BizUser',
            'enableAutoLogin' => true,
        ],
        'request' => [
            'cookieValidationKey' => 'test',
            'enableCsrfValidation' => false,
            // but if you absolutely need it set cookie domain to localhost
            /*
            'csrfCookie' => [
                'domain' => 'localhost',
            ],
            */
        ],        
    ],
    'params' => $params,
];
