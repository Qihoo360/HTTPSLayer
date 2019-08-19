<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\models\Project */

$this->title = '添加业务';
$this->params['breadcrumbs'][] = ['label' => '业务列表', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="project-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
