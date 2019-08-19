<?php

use app\models\Project;
use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $searchModel app\models\ProjectSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $bizuser_dict array */

$this->title = '业务列表';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="project-index">

    <h1><?php // echo Html::encode($this->title) ?></h1>
    <?php echo $this->render('_search', [
        'model' => $searchModel,
        'bizuser_dict' =>$bizuser_dict,
    ]); ?>

    <p>
        <?= Html::a('添加业务', ['create'], ['class' => 'btn btn-success']) ?>
    </p>
    <?php Pjax::begin(); ?>    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'name',
            "label",
            [
                'attribute' => "user_id",
                "value" => function ($model) {
                    /**
                     * @var $model Project
                     */
                    return !empty($model->bizUser)? $model->bizUser->name: "";
                }
            ],

            'contact_email',

            [
                'class' => 'yii\grid\ActionColumn',
                'header' => '操作',
                'template' => "{update} {view} {addcertificate} {addbalance}",
                'buttons' => [
                    'update' => function ($url, $model) {
                        return Html::a(
                            '修改',
                            [
                                'update',
                                'id' => $model->id,
                            ],
                            [
                                'class' => 'btn btn-xs btn-warning',
                                'title' => '修改',
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
                                'class' => 'btn  btn-xs btn-primary',
                                'title' => '查看',
                                'data-pjax' => '0',
                            ]
                        );
                    },
                    'addcertificate' => function ($url, $model) {
                        return Html::a(
                            '证书配置',
                            [
                                'addcertificate',
                                'id' => $model->id,
                            ],
                            [
                                'class' => 'btn btn-xs btn-info',
                                'title' => '证书配置',
                                'data-pjax' => '0',
                            ]
                        );
                    },
                    'addbalance' => function ($url, $model) {
                        return Html::a(
                            '负载均衡配置',
                            [
                                'addbalance',
                                'id' => $model->id,
                            ],
                            [
                                'class' => 'btn btn-xs btn-default',
                                'title' => '负载均衡配置',
                                'data-pjax' => '0',
                            ]
                        );
                    },
                    'frequency' => function ($url, $model) {
                        return Html::a(
                            '访问频率控制',
                            [
                                '/frequency',
                                'project_id' => $model->id,
                            ],
                            [
                                'class' => 'btn btn-xs btn-success',
                                'title' => '频率控制',
                                'data-pjax' => '0',
                            ]
                        );
                    },
                    /* 暂时隐藏按照业务区分负载均衡配置的入口
                    'globalconfig' => function ($url, $model) {
                        return Html::a(
                            '配置信息',
                            [
                                'globalconfig/index',
                                'project_id' => $model->id,
                            ],
                            [
                                'class' => 'btn btn-xs btn-default',
                                'title' => '点击进入该业务负载均衡配置信息管理页面',
                                'data-pjax' => '0',
                            ]
                        );
                    },
                    */
                ]
            ]

        ],
    ]); ?>
    <?php Pjax::end(); ?></div>
