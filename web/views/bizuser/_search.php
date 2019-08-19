<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\BizUserSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="biz-user-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <div class="row">
        <div class="col-lg-4">
            <?= $form->field($model, 'email') ?>

        </div>
        <div class="col-lg-2">
            <?= $form->field($model, 'name') ?>

        </div>
        <div class="col-lg-2">
            <?= $form->field($model, 'phone') ?>

        </div>
        <div class="col-lg-2">
            <?= $form->field($model, 'status')->dropDownList(Constant::$COMMON_STATUS, ["prompt" => "全部"]) ?>

        </div>




    </div>





    <?php // echo $form->field($model, 'is_admin') ?>

    <div class="form-group">
        <?= Html::submitButton('搜索', ['class' => 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
