<?php

use yii\helpers\Html;
use yii\grid\GridView;
use app\models\Frequency;
use app\models\Project;
use yii\web\NotFoundHttpException;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $searchModel app\models\FrequencySearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
$this->title = '频率限制';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="frequency-index">

    <h3><?= Html::encode($this->title) ?></h3>
    <?php  echo $this->render('_search', [
            'model' => $searchModel,
            'allStatus' => Frequency::$statusArray,
            'allHandleWay' => Frequency::$handleWayArray,
            'allAccordingTo' => Frequency::$accordingToArray,
            'project_id' => $project_id
    ]); ?>

    <div class="row">
        <span class="col-md-1">
            <?= Html::a('创建规则', ['create', 'project_id' => $project_id], ['class' => 'btn btn-success']) ?>
        </span>
        <span class="col-md-offset-9 col-md-2">
            <?= Html::a('发布历史', ['/frequency-version', 'project_id' => $project_id], ['class' => 'btn btn-primary']) ?>
            <?= Html::a('发布到线上', ['release', 'project_id' => $project_id], ['class' => 'btn btn-success']) ?>
        </span>
    </div>
    <?php Pjax::begin(); ?>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'summary' => '第 {begin} - {end} 条 共 {totalCount} 条 | 共 {pageCount} 页',
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            'path',
            'description',
            [
                'label' => '限频依据',
                'value' => function($data) {
                    $accordings = Frequency::$accordingToArray;
                    return isset($accordings[$data->according]) ? $accordings[$data->according] : 'unknow';
                }
            ],
            [
                'label' => '处理方式',
                'value' => function($data) {
                    $handle_ways = Frequency::$handleWayArray;
                    return isset($handle_ways[$data->handle_way]) ? $handle_ways[$data->handle_way] : 'unknow';
                }
            ],
            [
                'label' => '状态',
                'format' => 'html',
                'value' => function ($data) {
                    $online_status = "<div style='background: #5cb85c; color: white; padding: 3px 0 3px 0;' class='text-center'>已开启</div>";
                    $offline_status = "<div style='background: #f0ad4e; color: white;padding: 3px 0 3px 0;' class='text-center'>未开启</div>";
                    return $data->status == Frequency::STATUS_OPEN ? $online_status : $offline_status;
                }
            ],
            [
                'label' => '更新时间',
                'value' => function($data) {
                    return $data->update_time;
                }
            ],
            [
                'class' => 'yii\grid\ActionColumn',
                'header' => '查看',
                'template' => "{config}　{view}　{charts}",
                'buttons' => [
                    'config' => function ($url, $model) {
                        return Html::a(
                            '详细配置',
                            [
                                'update',
                                'id' => $model->id,
                            ],
                            [
                                'class' => 'btn btn-xs btn-primary',
                                'title' => '详细配置',
                                'data-pjax' => '0',
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
                                'class' => 'btn  btn-xs btn-info',
                                'title' => '查看',
                                'data-pjax' => '0',
                            ]
                        );
                    },
                    'charts' => function ($url, $model) {
                        return Html::a(
                            '报表',
                            [
                                '',
                                'id' => $model->id,
                            ],
                            [
                                'class' => 'btn btn-xs btn-default',
                                'title' => '报表',
                                'data-pjax' => '0',
                            ]
                        );
                    },
                ]
            ],
            [
                'class' => 'yii\grid\ActionColumn',
                'header' => '操作',
                'template' => "{online}　{delete}",
                'buttons' => [
                    'online' => function ($url, $model, $key) {
                        $onlineOptions = [
                            'title' => "开启",
                            'data-confirm' => "你确定要将". $model->path ."上线吗？",
                            'data-pjax' => '1',
                            'class' => 'btn btn-xs btn-success'
                        ];
                        $offlineOptions = [
                            'title' => "关闭",
                            'data-confirm' => "你确定要将". $model->path ."下线吗？",
                            'data-pjax' => '1',
                            'class' => 'btn btn-xs btn-warning'
                        ];
                        return $model->status == Frequency::STATUS_CLOSE
                            ? Html::a('开启', ['online', 'id' => $model->id,], $onlineOptions)
                            : Html::a('关闭', ['offline', 'id' => $model->id,], $offlineOptions);
                    },
                    'delete' => function ($url, $model, $key) {
                        $options = [
                            'title' => "删除",
                            'data-confirm' => "确定要将". $model->path ."删除吗？",
                            'data-pjax' => '1',
                            'class' => 'btn btn-xs btn-danger'
                        ];
                        return $model->status == Frequency::STATUS_CLOSE
                            ? Html::a('删除', ['disable', 'id' => $model->id, ], $options)
                            : '';
                    },
                ]
            ],
            //            'id',
//            'project_id',
            //            'method',
            // 'cookie_name',
            // 'time_window:ntext',
            // 'referer:ntext',
            // 'arguments:ntext',
            // 'white_ip:ntext',
            // 'black_ip:ntext',
            // 'create_time',
//             'update_time',

//             'create_user',
            // 'update_user',
            // 'update_operation',
        ],
    ]); ?>
    <?php Pjax::end(); ?></div>
</div>
