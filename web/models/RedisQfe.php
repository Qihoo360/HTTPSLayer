<?php
/**
 * Created by PhpStorm.
 * User: zhangshuang
 * Date: 17/11/1
 * Time: 17:10
 */

namespace app\models;


use Exception;
use Redis;

class RedisQfe
{

    const DB_CERTIFICATE_FILE = 0; // 聚合页字拼音按照音调分组
    const DB_FREQUENCYCONFIG_FILE = 1; //验证码服务频率限制

    private static $_instance;

    private $wclient;

    private $rclient;

    private function __construct()
    {
        $wredis_host = \Yii::$app->params['persistence_redis_whost'];
        $wredis_port = \Yii::$app->params['persistence_redis_wport'];
        $wredis_passwd = \Yii::$app->params['persistence_redis_wpasswd'];

        $rredis_host = \Yii::$app->params['persistence_redis_rhost'];
        $rredis_port = \Yii::$app->params['persistence_redis_rport'];
        $rredis_passwd = \Yii::$app->params['persistence_redis_rpasswd'];

        try {

            $this->wclient = new Redis();
            $this->wclient->connect($wredis_host, $wredis_port, 2);
            if (!empty($wredis_passwd)) {
                $this->wclient->auth($wredis_passwd);
            }

            $this->rclient = new Redis();
            $this->rclient->connect($rredis_host, $rredis_port, 2);
            if (!empty($wredis_passwd)) {
                $this->rclient->auth($rredis_passwd);
            }
        } catch (Exception $e) {

        }
    }

    /**
     * forbid to clone the object
     */
    public function __clone()
    {
        trigger_error("clone is not allow!", E_USER_ERROR);
    }

    public static function getInstance()
    {
        if (empty(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function get($db, $key)
    {
        if (empty($this->rclient)) {
            return false;
        }
        try {
            $this->rclient->select($db);
            return $this->rclient->get($key);
        } catch (Exception $e) {
            return false;
        }
    }

    public function set($db, $key, $value)
    {
        if (empty($this->wclient)) {
            return false;
        }
        try {
            $this->wclient->select($db);
            return $this->wclient->set($key, $value);
        } catch (Exception $e) {
            return false;
        }

    }

}