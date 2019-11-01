<?php
/**
 * Created by PhpStorm.
 * User: zhangshuang
 * Date: 17/10/26
 * Time: 15:27
 */

namespace app\models;


class Context
{
    public static $_instance;

    public static $global_message;


    private $biz_user;

    private $auth_method;

    /**
     * Context constructor.
     * @param $biz_user
     */
    public function __construct($biz_user)
    {
        if (!empty($biz_user) && $biz_user instanceof BizUser) {
            $this->biz_user = $biz_user;
        }
    }

    public static function getInstance($biz_user = null) {
        if (empty(self::$_instance)) {
            self::$_instance = new self($biz_user);
        }
        return self::$_instance;
    }

    public function __clone()
    {
        trigger_error("clone is not allow!", E_USER_ERROR);
    }

    /**
     * @return BizUser 获取当前登录的用户
     */
    public function bizUser()
    {
        return $this->biz_user;
    }

    public function setAuthMethod($method) {
        $this->auth_method = $method;
    }

    public function getAuthMethod() {
        return $this->auth_method;
    }
}