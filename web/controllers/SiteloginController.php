<?php
/**
 * Created by PhpStorm.
 * User: zhangshuang
 * Date: 2019/7/30
 * Time: 5:13 PM
 */

namespace app\controllers;


use app\models\AuthMethod;
use app\models\ResetPasswordForm;
use Yii;
use yii\web\ForbiddenHttpException;

class SiteloginController extends BaseController
{


    public function actionResetpassword() {
        if ($this->auth_method == AuthMethod::METHOD_LOCAL) {
            $model = new ResetPasswordForm();
            if ($model->load(Yii::$app->request->post())&& $model->validate() && $model->reset()) {
                return $this->goHome();
            }
            return $this->render('/site/resetpassword', [
                'model' => $model,
            ]);
        }
        throw new ForbiddenHttpException("not allow reset password");


    }
}