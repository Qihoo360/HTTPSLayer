<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\BizUser */
/* @var $auth_method string */
/* @var $extra array */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => '用户列表', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="biz-user-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('修改', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?php
        if ($auth_method == \app\models\AuthMethod::METHOD_LOCAL) {
            $bizUser = Yii::$app->user->identity;
            if ($bizUser->is_admin) {
                echo Html::a('重置密码', ['autopassword', 'id' => $model->id], [
                    'class' => 'btn btn-danger',
                    'data' => [
                        'confirm' => '确定重置密码?',
                        'method' => 'post',
                    ],
                ]);
            }
        }

        ?>
    </p>

    <?php
    if (!empty($extra["reset_password"])) {
        $default_password = Utils::getFixedPassword();
        echo <<<HTML
        
        <div class="row">
        <div class="col-lg-3">
        <div class="panel panel-danger">密码初始化成功：默认密码{$default_password}</div>

</div>
</div>
HTML;

    }

    ?>

    <?php
    $attributes = [
        'id',
        'email:email',
        'name',
        [
            'attribute' => 'status',
            'value' => Constant::$COMMON_STATUS[$model->status],
        ],
        [
            'attribute' => 'is_admin',
            'value' => Constant::$YES_OR_NO[$model->is_admin],
        ],
        [
            'attribute' => 'role_id',
            'value' => $model->is_admin ? Constant::$USER_ROLES[0] : Constant::$USER_ROLES[$model->role_id],
        ],
    ];
    echo DetailView::widget([
        'model' => $model,
        'attributes' => $attributes,
    ]) ?>

</div>
