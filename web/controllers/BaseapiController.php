<?php
/**
 * Created by PhpStorm.
 * User: zhangshuang
 * Date: 17/11/1
 * Time: 15:30
 */

namespace app\controllers;


use yii\web\Controller;
use yii\web\Response;

class BaseapiController extends Controller
{
    /**
     * @param string $data
     * @param int $code
     * @param string $content
     * @param array $extra
     * @return object
     */
    public function echoJson($data, $code = 0, $content="", $extra = [])
    {
        header("HTTP/1.1 200 OK");
        header('Content-type: application/json; charset=utf-8');
        header("Cache-Control: no-cache, must-revalidate");
//        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
//        header('Access-Control-Allow-Origin: *');
//        header("Pragma: no-cache");
        if($content) {
            return \Yii::createObject([
                'class' => 'yii\web\Response',
                'format' => Response::FORMAT_RAW,
                'data' => $content
            ]);
        }
        $response_data = [
            'code' => $code,
            'data' => $data,
        ];
        if (!empty($extra) && is_array($extra)) {
            $response_data = array_merge($extra, $response_data);
        }

        return \Yii::createObject([
            'class' => 'yii\web\Response',
            'format' => Response::FORMAT_JSON,
            'data' => $response_data,
        ]);
    }
}