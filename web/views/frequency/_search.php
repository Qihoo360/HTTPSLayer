<?php

use yii\helpers\Html;
use kartik\widgets\ActiveForm;
use kartik\select2\Select2;
use app\models\Frequency;

/* @var $this yii\web\View */
/* @var $model app\models\FrequencySearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="frequency-search">

    <?php $form = ActiveForm::begin([
        'type' => ActiveForm::TYPE_HORIZONTAL,
        'action' => ['index', 'project_id' => $project_id],
        'method' => 'get',
    ]); ?>

    <div class="row">
        <div class="col-md-5">
            <?= $form->field($model, 'path')->textInput(['placeholder' => '请输入要查询的路径']) ?>
            <?= $form->field($model, 'description')->textInput(['placeholder' => '请输入要查询的描述']) ?>
        </div>
        <div class="col-md-5">
            <?= $form->field($model, 'handle_way')->widget(Select2::className(),
                [
                    'data' => $allHandleWay,
                    'size' => Select2::MEDIUM,
                    'options' => ['placeholder' => '选择处理方式 ...'],
                    'pluginOptions' => [
                        'allowClear' => true
                    ],
                ]
            ) ?>
            <?php unset($allStatus[Frequency::STATUS_DELETED]); // 删除状态不展示 ?>
            <?= $form->field($model, 'status')->widget(Select2::className(),
                [
                    'data' => $allStatus,
                    'size' => Select2::MEDIUM,
                    'options' => ['placeholder' => '选择状态 ...'],
                    'pluginOptions' => [
                        'allowClear' => true
                    ],
                ]
            ) ?>
        </div>
        <div class="col-md-1">
            <?= Html::submitButton('查　询', ['class' => 'btn btn-primary']) ?>
        </div>
    </div>
    <?php ActiveForm::end(); ?>

</div>
