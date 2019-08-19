<?php

/**
 * Created by PhpStorm.
 * User: zhangshuang
 * Date: 17/10/26
 * Time: 15:23
 */
class Constant
{
    /**
     * 通用取值: 有效
     */
    const VALID = 1;

    /**
     * 通用取值: 无效
     */
    const INVALID = 0;

    const CONFIG_ON = "on";

    const CONFIG_OFF = "off";

    /**
     * @var array 页面头部数据
     */
    public static $TABBAR_FULL_LIST = [
        [
            'label' => '用户管理',
            'items' => [
                ['label' => '用户列表', 'url' => ['/bizuser/index']],
            ],
        ],
        [
            'label' => '业务管理',
            'items' => [
                ['label' => '业务列表', 'url' => ['/project/index']],
            ],
        ],
        [
            'label' => '证书管理',
            'items' => [
                ['label' => '证书列表', 'url' => ['/certificate/index']],
            ],
        ],
        [
            'label' => '配置管理',
            'items' => [
                ['label' => '配置信息', 'url' => ['/globalconfig/index']],
            ],
        ],
    ];

    public static $COMMON_STATUS = [
        0 => "无效",
        1 => "有效",
    ];

    public static $YES_OR_NO = [
        0 => "否",
        1 => "是",
    ];

    /**
     * @var array 机房信息
     * @deprecated replaceby  self::getVipLocation
     */
    public static $VIP_LOCATION = [

    ];

    /**
     * @var array
     * @deprecated replaceby self::getQfeIdc
     */
    public static $QFE_IDC = [

    ];

    public static $GLOBAL_CONFIG_STATUS = [
        0 => "未生效",
        1 => "已预发布",
        2 => "已发布",
    ];

    /**
     * @var array
     *
     * 索引 映射user对象的role_id,
     *  1 --- 表示是证书操作者
     *  2 --- 表示是业务操作者
     *
     * 取值有两种方式,
     *  <controller_id> ---- 表示赋予所有controller_id下的所有action权限
     *  <controller_id>/<action_id> ---- 表示赋予所有controller_id下的action_id权限
     */
    public static $ROLE_CONTROLLER = [
        1 => [ // 证书操作者
            'sitelogin',
            'certificate',
            'login',
            'project/view'
        ],
        2 => [ // 业务操作者
            'sitelogin',
            'certificate/view',
            'project',
            'login',
        ],
        3 => [ // 证书 & 业务 & 配置发布
            'sitelogin',
            'project',
            'login',
            'certificate',
            'globalconfig'
        ],
    ];

    public static $USER_ROLES = [
        0 => '管理员无需指定角色',
        1 => '证书管理',
        2 => '业务管理',
        3 => '证书及业务发布',
    ];

    public static function getVipLocation() {
        return safe_get_array(Yii::$app->params,"vip_location");
    }

    public static function getQfeIdc() {
        return safe_get_array(Yii::$app->params, "qfe_idc");
    }

    public static function qfeIdcRename() {
        return safe_get_str(Yii::$app->params, "idcrename");
    }

    public static function getIdcs() {
        $qfe_idc = self::getQfeIdc();
        return array_keys($qfe_idc);
    }


}
