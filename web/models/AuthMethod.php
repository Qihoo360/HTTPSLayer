<?php
/**
 * Created by PhpStorm.
 * User: zhangshuang
 * Date: 2019/6/17
 * Time: 3:09 PM
 */

namespace app\models;


use app\library\Qihoo;
use yii\base\Action;
use yii\web\Controller;
use yii\web\IdentityInterface;
use yii\web\Request;

class AuthMethod
{
    const METHOD_OAUTH = "oauth";

    const METHOD_LDAP = "ldap";

    const METHOD_LOCAL = "local";

    private $context;

    private $method;

    /**
     * AuthMethod constructor.
     */
    public function __construct()
    {
        $this->method = safe_get_str(\Yii::$app->params, "authmethod", self::METHOD_OAUTH);
    }

    public function getMethod() {
        return $this->method;
    }

    /**
     * @param $controller Controller
     * @param $action Action
     * @return bool
     * @throws \yii\base\InvalidConfigException
     */
    public function verify($controller, $action)
    {
        switch ($this->method) {
            case self::METHOD_OAUTH:
                return $this->verifyOauth($controller, $action);
                break;
            case self::METHOD_LDAP:
                return $this->verifyLDAP($controller, $action);
                break;
            case self::METHOD_LOCAL:
                return $this->verifyLocal($controller, $action);
                break;
            default:
                return false;
                break;
        }
    }

    /**
     * @param $controller Controller
     * @param $action Action
     * @return bool
     */
    public function verifyLDAP($controller, $action)
    {
        $req = \Yii::$app->request;
        $controller_id = $action->controller->id;
        $action_id = $action->id;
        $session = \Yii::$app->session;

        if ($controller_id == 'login' && $action_id == 'logout') { // 去除logout
            return true;
        }

        if (!$session->isActive) {
            $session->setTimeout(3000);
            $session->open();
        }

        $request = \Yii::$app->request;
        $q = Qihoo::ins();
        $userInfo = $session->get("userInfo");
        if (empty($userInfo)) { // 未获取到用户信息, 即未登录情况下
            $sid = $request->get('sid');
            if (!$sid) { // 未获取到sid 去登录验证
                $q->goToLogin();
                return false;
            } else { // 登录成功
                $userInfo = $q->getUserInfo($sid);
                $session->set("userInfo", $userInfo);
                $query_string = $req->queryString;
                parse_str($query_string, $query_info);
                unset($query_info['sid']);
                $query_string = http_build_query($query_info);
                $controller->redirect("/{$controller_id}/{$action_id}?" . $query_string); // 重定向到访问的url中
                return false;
            }
        }

        if (!empty($userInfo)) {

            // 注册用户信息及接口权限判断
            if (!$this->registerUserLDAP($userInfo, $action->controller->id, $action->id)) {
                $controller->redirect(['/site/forbid', 'controller' => $action->controller->id, 'action' => $action->id]);
                return false;
            } else {
                return true;
            }
        }
        return true;
    }


    /**
     * @param $controller Controller
     * @param $action Action
     * @return bool
     * @throws \yii\base\InvalidConfigException
     */
    public function verifyOauth($controller, $action)
    {
        $controller_id = $action->controller->id;
        $action_id = $action->id;

        if ($controller_id == "login") {
            return true;
        }

        if (\Yii::$app->user->isGuest) {
            $ref = \Yii::$app->request->getUrl();
            $controller->redirect(['/login/oauth']);
            return false;
        } else {
            $user = \Yii::$app->user->identity;
            // 注册用户信息及接口权限判断
            if (!$this->registerUserOauth($user, $controller_id, $action_id)) {
                $controller->redirect(['/site/forbid', 'controller' => $controller_id, 'action' => $action_id]);
                return false;
            }
        }
        return true;
    }

    /**
     * @param $controller Controller
     * @param $action Action
     * @return bool
     * @throws \yii\base\InvalidConfigException
     */
    public function verifyLocal($controller, $action)
    {
        $controller_id = $action->controller->id;
        $action_id = $action->id;

        if ($controller_id == "login") {
            return true;
        }

        if (\Yii::$app->user->isGuest) {
            $ref = \Yii::$app->request->getUrl();
            $controller->redirect(['/login/local']);
            return false;
        } else {
            $user = \Yii::$app->user->identity;
            // 注册用户信息及接口权限判断
            if (!$this->registerUserLocal($user, $controller_id, $action_id)) {
                $controller->redirect(['/site/forbid', 'controller' => $controller_id, 'action' => $action_id]);
                return false;
            }
        }
        return true;
    }

    /**
     * @param $biz_user BizUser|IdentityInterface
     * @param $controller string
     * @param $action string
     * @return bool
     */
    private function registerUserOauth($biz_user, $controller, $action)
    {
        if (!empty($biz_user) && $biz_user->status == \Constant::VALID) {
            if (!empty($biz_user) && $biz_user->canAccess($controller, $action)) {
                \Yii::$app->user->setIdentity($biz_user); // 把当前用户注册到Yii::$app->user中
                $this->context = Context::getInstance($biz_user); // 上下文注册
                $this->context->setAuthMethod($this->method);
                return true;
            }
        }
        return false;
    }

    /**
     * @param $biz_user BizUser|IdentityInterface
     * @param $controller string
     * @param $action string
     * @return bool
     */
    private function registerUserLocal($biz_user, $controller, $action)
    {
        if (!empty($biz_user) && $biz_user->status == \Constant::VALID) {
            if (!empty($biz_user) && $biz_user->canAccess($controller, $action)) {
                \Yii::$app->user->setIdentity($biz_user); // 把当前用户注册到Yii::$app->user中
                $this->context = Context::getInstance($biz_user); // 上下文注册
                $this->context->setAuthMethod($this->method);
                return true;
            }
        }
        return false;
    }


    private function registerUserLDAP(&$userInfo, $controller, $action)
    {
        $email = !empty($userInfo['loginEmail']) ? $userInfo['loginEmail'] : "";
        if (!empty($email)) {
            $biz_user = BizUser::findOne([
                "email" => $email,
                "status" => \Constant::VALID,
            ]);
            if (!empty($biz_user) && $biz_user->canAccess($controller, $action)) {
                \Yii::$app->user->setIdentity($biz_user); // 把当前用户注册到Yii::$app->user中
                $this->context = Context::getInstance($biz_user); // 上下文注册
                $this->context->setAuthMethod($this->method);
                return true;
            }
        }
        return false;
    }

    /**
     * @param $controller Controller
     */
    public function logout($controller)
    {
        switch ($this->method) {
            case self::METHOD_OAUTH:
                $this->logoutOauth($controller);
                break;
            case self::METHOD_LDAP:
                $this->logoutLDAP($controller);
                break;
            case self::METHOD_LOCAL:
                $this->logoutLocal($controller);
                break;
            default:
                break;
        }
    }

    /**
     * @param $controller Controller
     */
    public function logoutOauth($controller)
    {
        \Yii::$app->user->logout();// 把当前用户退出Yii::$app->user

        $controller->redirect('/login/oauth');
    }

    /**
     * @param $controller Controller
     */
    public function logoutLocal($controller)
    {
        \Yii::$app->user->logout();// 把当前用户退出Yii::$app->user

        $controller->redirect('/login/local');
    }

    /**
     * @param $controller Controller
     */
    public function logoutLDAP($controller)
    {
        $session = \Yii::$app->session;
        $session->remove('userInfo');
        \Yii::$app->user->logout();// 把当前用户退出Yii::$app->user


        $controller->redirect('/login/index');
    }

    public static function getUserInfo($method)
    {
        switch ($method) {
            case self::METHOD_LDAP:
                return Context::getInstance()->bizUser();
                break;
            case self::METHOD_OAUTH:
                return \Yii::$app->user->identity;
                break;
            default:
                return null;
                break;
        }
    }

}