<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\models\Certificate */
/* @var $upload_form app\models\CertUploadForm */


$this->title = '添加证书';
$this->params['breadcrumbs'][] = ['label' => '证书列表', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="certificate-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
        'upload_form' => $upload_form,
    ]) ?>

</div>
