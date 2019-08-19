<?php

use kartik\select2\Select2;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\AddCertificateForm */
/* @var $certificate_dict array */

$this->title = '业务添加证书: ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => '业务列表', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = '添加证书';
?>
<div class="project-addcertificate">

    <h1><?= Html::encode($this->title) ?></h1>


    <div class="project-form">

        <?php $form = ActiveForm::begin(); ?>

        <?=
        $form->field($model, 'certificate_ids')->widget(
            Select2::className(),[
                'data' => $certificate_dict,
                'size' => Select2::SMALL,
                'options' => ['placeholder' => '选择证书 ...', 'multiple' => true],
                'pluginOptions' => [
                    'allowClear' => true
                ],
            ]
        )

        ?>

        <div class="form-group">
            <?= Html::submitButton('添加', ['class' => 'btn btn-success']) ?>
        </div>

        <?php ActiveForm::end(); ?>

    </div>

</div>
