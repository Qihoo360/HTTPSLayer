<?php

use kartik\switchinput\SwitchInput;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\BizUser */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="biz-user-form">

    <?php $form = ActiveForm::begin(); ?>
    <div class="row">
        <div class="col-lg-2">
            <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

        </div>
        <div class="col-lg-4">
            <?= $form->field($model, 'email')->textInput(['maxlength' => true]) ?>

        </div>
        <div class="col-lg-2">
            <?= $form->field($model, 'phone')->textInput(['maxlength' => true]) ?>

        </div>
        <div class="col-lg-2">
            <?= $form->field($model, 'status')->widget(SwitchInput::className(), [
                'type' => SwitchInput::CHECKBOX,
                'pluginOptions' => [
                    'onColor' => 'success',
                    'offColor' => 'danger',
                ]
            ]) ?>

        </div>
        <div class="col-lg-2">
            <?= $form->field($model, 'is_admin')->widget(SwitchInput::className(), [
                'type' => SwitchInput::CHECKBOX,
//                'disabled' => true,
                'pluginOptions' => [
                    'onColor' => 'success',
                    'offColor' => 'danger',
                    'onText' => 'Yes',
                    'offText' => 'No',
                ]
            ]) ?>

        </div>
    </div>

    <div class="row">
        <div class="col-lg-2">
            <?= $form->field($model, 'role_id')->dropDownList(Constant::$USER_ROLES ) ?>
        </div>
    </div>




    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? '创建' : '更新', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
