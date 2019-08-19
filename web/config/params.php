<?php
$path = __DIR__ . "/ini/param.ini";
$ini_arr = parse_ini_file_multi($path, true);
$params_global = safe_get_array($ini_arr, 'global');

unset($ini_arr['global']);
$params = [];
foreach ($ini_arr as $cluster => $item) {
    $params[$cluster] = $item;
}
$cluster_params =  $params[APPLICATION_CLUSTER] + $params_global;

return $cluster_params;
