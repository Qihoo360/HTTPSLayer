<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\Balance */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="balance-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'project_id')->textInput() ?>

    <?= $form->field($model, 'location')->dropDownList([ 'GZST' => 'GZST', 'ZZZC' => 'ZZZC', 'SHBT' => 'SHBT', 'SHYC' => 'SHYC', 'ZZDT' => 'ZZDT' ], ['prompt' => '']) ?>

    <?= $form->field($model, 'vip')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'weight')->textInput() ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-primary' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
