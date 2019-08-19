<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\models\Frequency */

$this->title = '创建新规则';
$this->params['breadcrumbs'][] = ['label' => '频率限制', 'url' => ['index', 'project_id' => $project->id]];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="frequency-create">

    <h3><?= Html::encode($this->title) ?></h3>

    <?= $this->render('_form', [
        'model' => $model,
        'project' => $project
    ]) ?>

</div>
