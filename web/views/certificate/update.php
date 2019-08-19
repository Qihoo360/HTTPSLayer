<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Certificate */
/* @var $upload_form app\models\CertUploadForm */

$this->title = '修改证书: ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => '证书列表', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = '修改';
?>
<div class="certificate-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
        'upload_form' => $upload_form,
    ]) ?>

</div>
