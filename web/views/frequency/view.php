<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\Frequency */

$project_id = Yii::$app->request->getQueryParam('project_id');
$this->title = $model->description;
$this->params['breadcrumbs'][] = ['label' => '频率限制', 'url' => ['index', 'project_id' => $project_id]];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="frequency-view">

    <h3><?= Html::encode($this->title) ?></h3>

    <p>
        <?= Html::a('更　新', ['update', 'id' => $model->id, 'project_id' => $project_id], ['class' => 'btn btn-primary']) ?>
    </p>
    
    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'project_id',
            'path',
            'according',
            'method',
            'cookie_name',
            'time_window:ntext',
            'referer:ntext',
            'arguments:ntext',
            'white_ip:ntext',
            'black_ip:ntext',
            'handle_way',
            'status',
            'create_time',
            'update_time',
            'create_user',
            'update_user',
            'update_operation',
        ],
    ]) ?>

</div>
