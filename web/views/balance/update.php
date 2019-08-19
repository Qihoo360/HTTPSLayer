<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Balance */

$this->title = 'Update Balance: ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Balances', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="balance-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
