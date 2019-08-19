<?php

/**
 * Created by PhpStorm.
 * User: zhangshuang
 * Date: 17/10/26
 * Time: 15:40
 */
class Utils
{

    /**
     * @param $bizUser \app\models\BizUser
     * @param $session
     * @return array
     */
    public static function buildBarItems($bizUser)
    {

        if (!empty($bizUser->is_admin)) {
            return Constant::$TABBAR_FULL_LIST;
        }
        if (empty($bizUser)) {
            return [];
        }
        $collection = Constant::$TABBAR_FULL_LIST;

        foreach ($collection as $k1 => $level_1) {
            if (!empty($level_1['items'])) {
                foreach ($level_1['items'] as $k2 => $level_2) {
                    if (!empty($level_2['url'][0])) {
                        $url = $level_2['url'][0];
                        $route = substr($url, 1);
                        list($controller_id, $action_id) = explode("/", $route);
                        if (!$bizUser->canAccess($controller_id, $action_id)) {
                            unset($collection[$k1]['items'][$k2]);
                        }
                    }
                }

                if (empty($collection[$k1]['items'])) {
                    unset($collection[$k1]);
                }
            }
        }


        return $collection;
    }

    public static function buildNavX($bizUser)
    {
        $controller_id = Yii::$app->controller->id;
        if (!empty($bizUser)) {
            $navx = [
                'options' => ['class' => 'nav nav-pills nav-stacked'],
                'items' => [
                    ['label' => '用户管理', 'active' => $controller_id == "bizuser", 'items' => [
                        ['label' => '用户列表', 'url' => '/bizuser/index'],
                    ]],
                    ['label' => '业务管理', 'active' => $controller_id == "project", 'items' => [
                        ['label' => '业务列表', 'url' => '/project/index'],
                    ]],
                    ['label' => '证书管理', 'active' => $controller_id == "certificate", 'items' => [
                        ['label' => '证书列表', 'url' => '/certificate/index'],
                    ]],
                    ['label' => '配置管理', 'active' => $controller_id == "globalconfig", 'items' => [
                        ['label' => '配置列表', 'url' => '/globalconfig/index'],
                    ]],
                ]
            ];
            return $navx;
        } else {
            return [];
        }
    }

    /**
     * 循环创建多级目录
     * @param $dir
     * @param int $mode
     * @param bool $recursive
     * @return bool
     */
    public static function mkdirs($dir, $mode = 0777, $recursive = true)
    {
        if (is_null($dir) || $dir === "") {
            return false;
        }
        if (is_dir($dir) || $dir === "/") {
            return true;
        }
        if (self::mkdirs(dirname($dir), $mode, $recursive)) {
            return mkdir($dir, $mode);
        }
        return false;
    }


    /**
     * 上传文件本地存储文件目录
     * @return string
     */
    public static function getUploadedDir()
    {
        return ROOT_PATH . '/runtime/upload/' . date("Ymd");
    }

    public static function timePretty($date)
    {
        return date("Y-m-d H:i:s", strtotime($date));
    }

    /**
     * @param $model \app\models\GlobalConfig
     * @param $latest_model \app\models\GlobalConfig
     * @return bool
     */
    public static function configCanPreRelease($model, $latest_model)
    {
        if ($model->status == \app\models\GlobalConfig::STATUS_INVALID && $model->id == $latest_model->id) {
            return true;
        }
        return false;
    }


    /**
     * @param $model \app\models\GlobalConfig
     * @param $latest_model \app\models\GlobalConfig
     * @return bool
     */
    public static function configCanRelease($model, $latest_model)
    {
        if ($model->status == \app\models\GlobalConfig::STATUS_PRE_RELEASE) {
            return true;

        }
        return false;
    }

    /**
     * @param $model \app\models\GlobalConfig
     * @param $latest_model \app\models\GlobalConfig
     * @return bool
     */
    public static function configCanInvalid($model, $latest_model)
    {
        if ($model->status == \app\models\GlobalConfig::STATUS_PRE_RELEASE) {
            return true;

        }
        return false;
    }

    /**
     * 传输文件内容给客户端（客户端下载文件）
     * @param string $fileName 文件名
     * @param string $fileContent 文件内容
     * @param string $contentType 内容类型
     * @access public
     * @return bool
     */
    public static function sendDownloadFile($fileContent, $fileName, $contentType = 'application/octet-stream')
    {
//        header('Content-Description: File Transfer');
        header('Content-Type: ' . $contentType);
        header('Content-Disposition: attachment; filename=' . $fileName);
        header('Content-Transfer-Encoding: binary');
        header('Content-Length: ' . strlen($fileContent));
        ob_clean();
        flush();
        echo $fileContent;
        return true;
    }


    public static function jsonPrettyInHtml($json_str)
    {
        $pretty_json = json_encode(json_decode($json_str), JSON_PRETTY_PRINT);
        $pretty_json = str_replace("\n", "<br>", $pretty_json);
        $pretty_json = str_replace("\t", "&nbsp;&nbsp;&nbsp;&nbsp;", $pretty_json);
        $pretty_json = str_replace("    ", "&nbsp;&nbsp;&nbsp;&nbsp;", $pretty_json);

        return $pretty_json;

    }

    public static function hostMatch($pattern, $subject)
    {
        $patterns = explode(".", $pattern);
        $subjects = explode(".", $subject);
        $pattern_length = count($patterns);
        $subject_length = count($subjects);
        if ($pattern_length != $subject_length) {
            return false;
        }

        for ($i = $pattern_length; $i > 0; $i--) {
            if ($patterns[$i - 1] == "*") {
                continue;
            } else {
                if ($patterns[$i - 1] == $subjects[$subject_length - ($pattern_length - $i) - 1]) {
                    continue;
                } else {
                    return false;
                }
            }
        }
        return true;
    }

    public static function idcRename($idc)
    {
        $map = [];
        $config = Constant::qfeIdcRename();
        if (!empty($config)) {
            $strs = explode(",", $config);
            foreach ($strs as $item) {
                $info = explode(":", $item);
                $k = isset($info[0]) ? trim($info[0]) : "";
                $v = isset($info[1]) ? trim($info[1]) : "";
                if (!empty($k) && !empty($v)) {
                    $map[$k] = $v;
                }
            }
        }
        return isset($map[$idc]) ? $map[$idc] : $idc;
    }

    /**
     * 从hostname解析idc
     * @param $hostname
     * @return bool
     */
    public static function idcFromHostname($hostname)
    {
        $info = explode(".", $hostname);
        if (count($info) >= 3) {
            $idc = $info[count($info) - 3];
            return $idc;
        }

        return "corp";
    }

    public static function xssStrip($str)
    {
        return addslashes(strip_tags(htmlspecialchars($str)));
    }

    /**
     * 随机生成一个固定长度的字符串
     * @param int $length
     * @return string
     */
    public static function random_chars($length = 8)
    {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $random = '';
        for ($i = 0; $i < $length; $i++) {
            $random .= $chars[mt_rand(0, strlen($chars) - 1)];
        }
        return $random;
    }

    public static function getFixedPassword()
    {
        $params = Yii::$app->params;
        $fixed_passwd = safe_get_str($params, 'fixedpassword');
        return $fixed_passwd ? $fixed_passwd : "default";

    }

    public static function hashPassword($code) {
        return Yii::$app->getSecurity()->generatePasswordHash($code);
    }

    public static function hashValidate($code, $hash) {
        return Yii::$app->getSecurity()->validatePassword($code, $hash);
    }
}
