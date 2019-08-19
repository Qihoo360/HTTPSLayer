<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\models\FrequencyVersion */

$this->title = 'Create Frequency Version';
$this->params['breadcrumbs'][] = ['label' => 'Frequency Versions', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="frequency-version-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
