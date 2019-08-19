<?php

// NOTE: Make sure this file is not accessible when deployed to production
if (!in_array(@$_SERVER['REMOTE_ADDR'], ['127.0.0.1', '::1'])) {
    die('You are not allowed to access this file.');
}

defined("APPLICATION_CLUSTER") or define("APPLICATION_CLUSTER", "corp");

// comment out the following two lines when deployed to production
if (APPLICATION_CLUSTER == 'corp') {
    defined('YII_DEBUG') or define('YII_DEBUG', true);
    defined('YII_ENV') or define('YII_ENV', 'dev');
}


require(__DIR__ . '/../vendor/autoload.php');
require(__DIR__ . '/../vendor/yiisoft/yii2/Yii.php');
require(__DIR__ . '/../library/autoload.php');

$config = require(__DIR__ . '/../config/test.php');

(new yii\web\Application($config))->run();
