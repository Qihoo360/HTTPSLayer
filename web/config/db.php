<?php
$path = __DIR__ . "/ini/db.ini";
$ini_arr = parse_ini_file_multi($path, true);
$db = [];
foreach ($ini_arr as $cluster => $ini) {
    $host = safe_get_str($ini, 'host');
    $port = safe_get_int($ini, 'port');
    $dbname = safe_get_str($ini, 'dbname');
    $username = safe_get_str($ini, 'username');
    $password = safe_get_str($ini, 'password');
    $charset = safe_get_str($ini, 'charset');
    $db[$cluster] = [
        'class' => 'yii\db\Connection',
        'dsn' => "mysql:host={$host};port={$port};dbname={$dbname}",
        'username' => $username,
        'password' => $password,
        'charset' => $charset,
    ];
}

//Yii::debug("XXXXXXX" . print_r($db, true));

return $db[APPLICATION_CLUSTER];
