<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use app\models\GlobalConfig;

/* @var $this yii\web\View */
/* @var $searchModel app\models\GlobalConfigSearch */
/* @var $latestModel app\models\GlobalConfig */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = '全局配置';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="global-config-index">

    <h1><?php // echo Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?= Html::a('生成配置', ['create', 'project_id' => $searchModel->project_id], ['class' => 'btn btn-primary']) ?>
    </p>
    <?php Pjax::begin(); ?>    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            [
                'attribute' => 'user_id',
                'format' => 'raw',
                'value' => function ($model) {
                    return !empty($model->bizUser) ? $model->bizUser->name : "";
                }
            ],
            'create_time',
            'update_time',
//            'content:ntext',
//            'status',
            [
                'attribute' => 'status',
                'format' => 'raw',
                'value' => function ($model) {
                    switch ($model->status) {
                        case \app\models\GlobalConfig::STATUS_RELEASE:
                            $class = "label label-danger";
                            break;
                        case \app\models\GlobalConfig::STATUS_PRE_RELEASE:
                            $class = "label label-warning";
                            break;
                        case \app\models\GlobalConfig::STATUS_INVALID:
                            $class = "label label-success";
                            break;
                        default:
                            $class = "label label-default";
                            break;
                    }
                    if (!empty(Constant::$GLOBAL_CONFIG_STATUS[$model->status])) {
                        return "<span class='{$class}'>" . Constant::$GLOBAL_CONFIG_STATUS[$model->status] . "</span>";
                    } else {
                        return "<span class='{$class}'>未知</span>";
                    }

                }
            ],

            [
                'class' => 'yii\grid\ActionColumn',
                'header' => '操作',
                'template' => "{view} {prerelease} {release} {invalid} {rollback}",
                'buttons' => [
                    'rollback' => function ($url, $model) use ($latestModel) {
                        if (in_array($model->status, [GlobalConfig::STATUS_PRE_RELEASE, GlobalConfig::STATUS_RELEASE])) {
                            return "";
                        }
                        if ($model->id == $latestModel->id) {
                            return "";
                        }
                        return Html::a(
                            '以此配置重新生成配置',
                            [
                                'rollback',
                                'id' => $model->id,

                            ],
                            [
                                'class' => 'btn btn-xs btn-warning',
                                'title' => '以此配置重新生成配置',
                                'data-pjax' => '0',
                                'data' => [
                                    'confirm' => '确定以' . $model->id . "的配置重新生成配置吗?",
                                    'method' => 'post',
                                ],

                            ]
                        );
                    },
                    'view' => function ($url, $model) {
                        return Html::a(
                            '查看',
                            [
                                'view',
                                'id' => $model->id,
                            ],
                            [
                                'class' => 'btn btn-xs btn-primary',
                                'title' => '查看',
                                'data-pjax' => '0',
                            ]
                        );
                    }
                ]
            ]
        ],
    ]); ?>
    <?php Pjax::end(); ?></div>
