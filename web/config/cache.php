<?php
$path = __DIR__ . "/ini/cache.ini";

$ini_arr = parse_ini_file_multi($path, true);

$cache = [];
foreach ($ini_arr as $cluster => $ini) {
    $cache[$cluster] = [
        'class' => 'yii\redis\Cache',
        'redis' => [
            'hostname' => safe_get_str($ini, 'hostname'),
            'port' => safe_get_int($ini, 'port'),
            'database' => safe_get_int($ini, 'database'),
            'password' => safe_get_str($ini, 'password'),
        ]
    ];
}
return $cache[APPLICATION_CLUSTER];
