<?php
/**
 * Created by PhpStorm.
 * User: zhangshuang
 * Date: 17/11/2
 * Time: 15:48
 */

use yii\web\Request;

$path = __DIR__ . "/ini/log.ini";
$ini_arr = parse_ini_file_multi($path, true);
$global = safe_get_array($ini_arr, 'global');

$db_target = [
    'class' => 'yii\log\DbTarget',
    'levels' => ['error', 'warning', 'info'],
    'categories' => [
        'yii\db\Command::execute'
    ],
    'logVars' => [
        '_GET',
        '_POST',
        '_FILES',
        '_SERVER.REMOTE_ADDR',
        '_SERVER.REQUEST_URI',
        '_SERVER.REQUEST_METHOD',
        '_SERVER.HTTP_REFERER',
        '_SERVER.REQUEST_TIME'
    ],
    // 数据库操作日志记录
    'prefix' => function ($message) {
        if (Yii::$app === null) {
            return '';
        }

        $request = Yii::$app->getRequest();
        $ip = $request instanceof Request ? $request->getUserIP() : '-';

        /* @var $user \yii\web\User */
        $user = Yii::$app->has('user', true) ? Yii::$app->get('user') : null;
        if ($user && ($identity = $user->getIdentity(false))) {
            $userID = $identity->getId();
        } else {
            $userID = '-';
        }
        return "[$userID][$ip]";
    }
];

$file_target = [];
$class = safe_get_str($global, 'class');
if (!empty($class)) {
    $file_target = [
        'class' => $class,
        'levels' => explode(",", safe_get_str($global, 'levels')),
        'logFile' => safe_get_str($global, 'logFile'),
        'maxFileSize' => safe_get_int($global, 'maxFileSize'),
        'maxLogFiles' => safe_get_int($global, 'maxLogFiles'),
    ];
}
$targets = [];

if (!empty($db_target)) {
    $targets[] = $db_target;
}

if (!empty($file_target)) {
    $targets[] = $file_target;
}

return [
    'traceLevel' => YII_DEBUG ? 3 : 0,
    'targets' => $targets,
];
