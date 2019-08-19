<?php

use unclead\multipleinput\MultipleInput;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\Project */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="project-form">

    <?php $form = ActiveForm::begin(); ?>

    <div class="row">
        <div class="col-lg-3">
            <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

        </div>
        <div class="col-lg-3">
            <?php
            if ($model->isNewRecord)
                echo $form->field($model, 'label')->textInput(['maxlength' => true]);
            else
                echo $form->field($model, 'label')->textInput(['maxlength' => true,  'readonly' => true]);

            ?>

        </div>
        <div class="col-lg-6">
            <?= $form->field($model, 'contact_email')->textInput(['maxlength' => true]) ?>

        </div>
    </div>


    <div class="row">
        <div class="col-lg-6">
            <?php
            echo $form->field($model, 'host_names')->widget(MultipleInput::className(), [
                'max' => 100,
                'min' => 1, // should be at least 2 rows
                'allowEmptyList' => false,
                'enableGuessTitle' => true,
                'addButtonPosition' => MultipleInput::POS_HEADER // show add button in the header
            ])->label(false);
            ?>
        </div>
    </div>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? '添加' : '修改', ['class' => $model->isNewRecord ? 'btn btn-primary' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
