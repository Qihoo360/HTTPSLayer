<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\models\GlobalConfig */

$this->title = 'Create Global Config';
$this->params['breadcrumbs'][] = ['label' => 'Global Configs', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="global-config-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
