<?php

use kartik\date\DatePicker;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\CertificateSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="certificate-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>


    <?php // echo $form->field($model, 'priv_key') ?>
    <div class ='row' >
        <div class = 'col-lg-2' >
            <?= $form->field($model, 'name') ?>

        </div>
        <div class = 'col-lg-2' >
            <?= $form->field($model, 'remain_day') ?>

        </div>

        <div class = 'col-lg-3' >
            <?= $form->field($model, 'contact_email') ?>

        </div>
        <div class = 'col-lg-2' >
            <?= $form->field($model, 'project_name') ?>

        </div>
        <div class = 'col-lg-1' >
            <?= $form->field($model, 'status')->dropDownList(Constant::$COMMON_STATUS, ['prompt' => '全部']) ?>

        </div>

    </div>

    <div class ='row'>

        <div class = 'col-lg-2' >
            <?= $form->field($model, 'create_time_start')->widget(DatePicker::className(), [
                'options' => [
                    'placeholder' => '起始日期',
                ],
                'pluginOptions' => [
                    'autoclose' => true,
                    'todayHighlight' => true,
                    'allowClear' => false,
                    'format' => 'yyyy-mm-dd',
                ]
            ]);
            ?>
        </div>
        <div class = 'col-lg-2' >
            <?= $form->field($model, 'create_time_end')->widget(DatePicker::className(), [
                'options' => [
                    'placeholder' => '结束日期',
                ],
                'pluginOptions' => [
                    'autoclose' => true,
                    'todayHighlight' => true,
                    'allowClear' => false,
                    'format' => 'yyyy-mm-dd',
                ]
            ]);
            ?>
        </div>

        <div class = 'col-lg-3' >
            <?= $form->field($model, 'host') ?>
        </div>
    </div>










    <?php // echo $form->field($model, 'serial_no') ?>

    <?php // echo $form->field($model, 'subject') ?>

    <?php // echo $form->field($model, 'priority') ?>

    <?php // echo $form->field($model, 'algorithm') ?>

    <?php // echo $form->field($model, 'issuer') ?>

    <?php // echo $form->field($model, 'valid_start_time') ?>

    <?php // echo $form->field($model, 'valid_end_time') ?>


    <div class="form-group">
        <?= Html::submitButton('搜索', ['class' => 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
