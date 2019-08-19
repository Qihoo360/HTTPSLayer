<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use app\models\FrequencyVersion;

/* @var $this yii\web\View */
/* @var $searchModel app\models\FrequencyVersionSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = '发布历史';
$this->params['breadcrumbs'][] = ['label' => '频率限制', 'url' => ['/frequency/index', 'project_id' => $project_id]];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="frequency-version-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php //echo $this->render('_search', ['model' => $searchModel]); ?>

    <!--    <p>-->
    <?php //echo Html::a('Create Frequency Version', ['create'], ['class' => 'btn btn-success']) ?>
    <!--    </p>-->
    <?php $line = 0; ?>
    <?php Pjax::begin(); ?>    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

//            'id',
//            'project_id',
            'data:ntext',
            'version',
            'online_date',
            // 'rollback_date',
            'online_user',
            // 'rollback_user',
            [
                'attribute' => 'status',
                'value' => function($model) {
                    return FrequencyVersion::$statusArray[$model->status];
                }
            ],
            [
                'class' => 'yii\grid\ActionColumn',
                'header' => '操作',
                'template' => "{view}　{rollback}",
                'buttons' => [
                    'view' => function ($url, $model, $key) {
                        return Html::a('查看', ['view', 'id' => $model->id,], ['class' => 'btn btn-xs btn-default']);
                    },
                    'rollback' => function ($url, $model, $key) use (&$line) {
                        $rollbackOption = [
                            'title' => "回滚",
                            'data-confirm' => "你确定要将 Version:" . $model->version . " 回滚吗？",
                            'data-pjax' => '1',
                            'class' => 'btn btn-xs btn-warning'
                        ];
                        $onlineAgain = [
                            'title' => "回滚",
                            'data-confirm' => "你确定要将 Version:" . $model->version . " 重新发布吗？",
                            'data-pjax' => '1',
                            'class' => 'btn btn-xs btn-success'
                        ];
                        switch ($model->status) {
                            case FrequencyVersion::STATUS_ONLINE:
                                $btn = Html::a('回滚到上个版本', ['rollback', 'id' => $model->id,], $rollbackOption);
                                break;
                            case FrequencyVersion::STATUS_OFFLINE:
                                $btn = '';
                                break;
                            case FrequencyVersion::STATUS_ROLLBACK:
                                $btn = Html::a('重新发布', ['reonline', 'id' => $model->id,], $onlineAgain);
                                break;
                            default:
                                $btn = '';
                        }
                        return $btn;
                    },
                ]
            ],
        ],
    ]); ?>
    <?php Pjax::end(); ?></div>
