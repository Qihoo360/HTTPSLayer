<?php
$db = require(__DIR__ . '/db.php');
// test database! Important not to run tests on production or development databases
$db['dsn'] = 'mysql:host=127.0.0.1;port=3066;dbname=sohttps_test';
$db['username'] = "sohttps_test";
$db['password'] = "xxxxxxxxx";
unset($db['slaveConfig']);
unset($db['slaves']);
$db["charset"] = "utf8mb4";

return $db;