<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\FrequencyVersionSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="frequency-version-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <div class="row">

    </div>
    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'project_id') ?>

    <?= $form->field($model, 'data') ?>

    <?= $form->field($model, 'version') ?>

    <?= $form->field($model, 'online_date') ?>

    <?php // echo $form->field($model, 'rollback_date') ?>

    <?php // echo $form->field($model, 'online_user') ?>

    <?php // echo $form->field($model, 'rollback_user') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
