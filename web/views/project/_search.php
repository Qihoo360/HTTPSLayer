<?php

use kartik\select2\Select2;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\ProjectSearch */
/* @var $form yii\widgets\ActiveForm */
/* @var $bizuser_dict array */

?>

<div class="project-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <div class="row">
        <div class="col-lg-2">
            <?= $form->field($model, 'name') ?>

        </div>
        <div class="col-lg-2">
            <?= $form->field($model, 'user_id')->widget(
                Select2::className(),[
                    'data' => $bizuser_dict,
                    'size' => Select2::MEDIUM,
                    'options' => ['placeholder' => '选择用户 ...'],
                    'pluginOptions' => [
                        'allowClear' => true
                    ],
                ]
            ) ?>

        </div>
        <div class="col-lg-4">
            <?= $form->field($model, 'contact_email') ?>

        </div>
    </div>





    <div class="form-group">
        <?= Html::submitButton('搜索', ['class' => 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
