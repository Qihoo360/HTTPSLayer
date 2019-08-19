<?php

$environ = getenv('QFE_HTTPS_IDC');
if (empty($environ)) {
    echo "Please configure `QFE_HTTPS_IDC` in environment variables \n";
    exit();
}

defined("APPLICATION_CLUSTER") or define("APPLICATION_CLUSTER", $environ);

// comment out the following two lines when deployed to production
if (APPLICATION_CLUSTER == 'corp') {
    defined('YII_DEBUG') or define('YII_DEBUG', true);
    defined('YII_ENV') or define('YII_ENV', 'dev');
} else {
    defined('YII_DEBUG') or define('YII_DEBUG', false);
    defined('YII_ENV') or define('YII_ENV', 'prod');
}


require(__DIR__ . '/../vendor/autoload.php');
require(__DIR__ . '/../vendor/yiisoft/yii2/Yii.php');
require(__DIR__ . '/../library/autoload.php');

$config = require(__DIR__ . '/../config/web.php');

(new yii\web\Application($config))->run();
