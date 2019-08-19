<?php

use kartik\date\DatePicker;
use kartik\switchinput\SwitchInput;
use unclead\multipleinput\MultipleInput;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\Certificate */
/* @var $form yii\widgets\ActiveForm */
/* @var $upload_form app\models\CertUploadForm */

?>

<div class="certificate-form">

    <?php $form = ActiveForm::begin();
    ?>
    <div class="row">
        <div class="col-lg-4">
            <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>
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
            <?= $form->field($model, 'priority')->textInput() ?>

        </div>
        <div class="col-lg-4">
            <?= $form->field($model, 'contact_email')->textInput(['maxlength' => true]) ?>

        </div>
    </div>

    <div class="row">
    </div>

    <div class="row">
        <div class="col-lg-4">
            <?= $form->field($upload_form, 'pub_file')->fileInput()->label("公钥文件") ?>

        </div>

        <div class="col-lg-4">
            <?= $form->field($upload_form, 'priv_file')->fileInput()->label("私钥文件") ?>

        </div>
    </div>




    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? '创建' : '更新', ['class' => $model->isNewRecord ? 'btn btn-primary' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
