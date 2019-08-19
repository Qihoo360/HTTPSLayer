<?php
/**
 * Created by PhpStorm.
 * User: zhangshuang
 * Date: 16/10/8
 * Time: 11:07
 */

namespace app\library;

use yii\base\Exception;


class Qihoo
{
    protected static $instance;
    protected $request;
    protected $connecttimeout = 30;
    protected $timeout = 30;
    protected $ssl_verifypeer = FALSE;
    protected $host;
    protected $schema = 'http';

    public static function ins()
    {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct()
    {
        $params = \Yii::$app->params;
    }

    public function goToLogin()
    {
        exit();
    }

    public function getUserInfo($sid)
    {
        return [
            'loginEmail' => "a@a.com",
            'user' => "demo",
            'display' => 'display',
        ];
    }

    /**
     * @param $host
     * @param $data
     * @return mixed
     */
    function send($host, $data)
    {
        $options = [CURLOPT_TIMEOUT_MS => 500];
        $url = $host . '?' . http_build_query($data);
        $ret = Http::ins()->get($url, $options);
        return @json_decode($ret, true);
    }
}