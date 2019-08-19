<?php
/**
 * Created by PhpStorm.
 * User: zhangshuang
 * Date: 17/10/26
 * Time: 14:09
 */

namespace app\controllers;


use app\models\AuthMethod;
use app\models\Context;
use yii\web\Request;

class BaseController extends BaseapiController
{


    /**
     * @var string 当前访问的controller
     */
    public $controller;
    /**
     * @var Request
     */
    public $req;


    /**
     * @var Context;
     */
    public $context;

    public $auth_method;


    public function beforeAction($action)
    {
        $this->layout = '@app/views/layouts/main.php';

        if (!parent::beforeAction($action)) {
            return false;
        }
        $auth_method = new AuthMethod();
        $this->auth_method = $auth_method->getMethod();
        return $auth_method->verify($this, $action);
    }
}