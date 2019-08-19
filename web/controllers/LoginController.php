<?php
/**
 * Created by PhpStorm.
 * User: zhangshuang
 * Date: 17/10/26
 * Time: 14:15
 */

namespace app\controllers;

use app\models\Auth;
use app\models\AuthMethod;
use app\models\BizUser;
use app\models\LoginForm;
use Yii;
use yii\authclient\OAuth2;
use yii\base\InvalidArgumentException;
use yii\db\Exception;
use yii\helpers\Url;
use yii\web\ForbiddenHttpException;
use yii\web\ServerErrorHttpException;

class LoginController extends BaseController
{

    public function actionIndex()
    {
        return $this->render('index');
    }

    /**
     * 登出
     */
    public function actionLogout()
    {
        $auth_method = new AuthMethod();
        $auth_method->logout($this);
    }

    public function actionOauth()
    {
        if (!\Yii::$app->user->isGuest) {
            return $this->redirect('/');
        }
        return $this->render('oauth');
    }

    public function actionLocal()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        }

        $model->password = '';
        return $this->render('local', [
            'model' => $model,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
            'auth' => [
                'class' => 'yii\authclient\AuthAction',
                'successCallback' => [$this, 'onAuthSuccess'],
            ],
        ];
    }

    /**
     * @param $client OAuth2
     * @return \yii\web\Response
     * @throws \Exception
     */
    public function onAuthSuccess($client)
    {
        $url = ['login/index'];
        $attributes = $client->getUserAttributes();
        $identity = \Yii::$app->user;
        $login = isset($attributes["login"]) ? $attributes["login"] : "";
        $email = isset($attributes["email"]) ? $attributes["email"] : "";
        $source_id = isset($attributes["id"]) ? $attributes["id"] : "";

        if (empty($email)) {
            throw new InvalidArgumentException("oauth email is empty");
        }

        /* @var $auth Auth */
        $auth = Auth::findOne([
            'source' => $client->getId(),
            'source_id' => $source_id,
        ]);

        if ($identity->isGuest) { // 平台未登录
            if ($auth) {
                $user = $auth->bizUser;
                $identity->login($user);
            } else {
                $user = BizUser::findOne(['email' => $email]);
                if ($user) {
                    $auth = new Auth([
                        'user_id' => $user->id,
                        'source' => $client->getId(),
                        'source_id' => (string)$source_id,
                    ]);
                    $auth->save();
                    $identity->login($user);
                } else {
                    $auth2 = safe_get_array(\Yii::$app->params, "auth2");
                    $auto_create = safe_get_int($auth2, "auto_create");
                    if ($auto_create) {
                        $user = new BizUser([
                            'name' => $login,
                            'email' => $email,
                            'status' => 1,
                            'is_admin' => 0,
                        ]);
                        $transaction = $user->getDb()->beginTransaction();
                        try {

                            $user_saved = $user->save();
                            if ($user_saved) {
                                $auth = new Auth([
                                    'user_id' => $user->id,
                                    'source' => $client->getId(),
                                    'source_id' => (string)$attributes['id'],
                                ]);
                                $auth_saved = $auth->save();
                                if ($auth_saved) {
                                    $transaction->commit();
                                    \Yii::$app->user->login($user);
                                } else {
                                    $transaction->rollBack();
                                    throw new ServerErrorHttpException("save error: user->" . intval($user_saved) . ", auth-> " . intval($auth_saved));
                                }
                            } else {
                                $transaction->rollBack();
                                throw new ServerErrorHttpException("save error: user->" . intval($user_saved));
                            }
                        } catch (Exception $e) {
                            $transaction->rollBack();
                            throw $e;
                        }

                    } else {
                        throw new ForbiddenHttpException("you are not allowd to login in, [email]" . $email);
                    }
                }
            }
        } else { // 用户已经登陆
            if (!$auth) { // 添加验证提供商（向验证表中添加记录）
                $auth = new Auth([
                    'user_id' => $identity->id,
                    'source' => $client->getId(),
                    'source_id' => (string)$attributes['id'],
                ]);
                $auth->save();
            }
        }
        return $this->redirect($url);
    }
}