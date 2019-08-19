<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Frequency */

$this->title = '更新Path: ' . $model->description;
$this->params['breadcrumbs'][] = ['label' => '频率限制', 'url' => ['index', 'project_id' => $project->id]];
$this->params['breadcrumbs'][] = ['label' => $model->description, 'url' => ['view', 'id' => $model->id, 'project_id' => $project->id]];
$this->params['breadcrumbs'][] = '更新';
?>
<div class="frequency-update">

    <h3><?= Html::encode($this->title) ?></h3>

    <?= $this->render('_form', [
        'model' => $model,
        'project' => $project
    ]) ?>

</div>
