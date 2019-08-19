<?php

use yii\grid\GridView;
use yii\helpers\Html;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $searchModel app\models\BizUserSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = '用户列表';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="biz-user-index">

    <h1><?php // echo Html::encode($this->title) ?></h1>
    <?php echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?= Html::a('添加用户', ['create'], ['class' => 'btn btn-success']) ?>
    </p>
    <?php Pjax::begin(); ?>    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            'id',
            'email:email',
            'name',
            'phone',
            [
                'attribute' => 'status',
                'value' =>
                    function ($model) {
                        return !empty(Constant::$COMMON_STATUS[$model->status]) ? Constant::$COMMON_STATUS[$model->status] : "未知";
                    },
            ],
            [
                'attribute' => 'is_admin',
                'value' =>
                    function ($model) {
                        return !empty(Constant::$YES_OR_NO[intval($model->is_admin)]) ? Constant::$YES_OR_NO[$model->is_admin] : "未知";
                    },
            ],
            [
                'attribute' => 'role_id',
                'value' =>
                    function ($model) {
                        if ($model->is_admin) {
                            return Constant::$USER_ROLES[0];
                        }
                        return !empty(Constant::$USER_ROLES[intval($model->role_id)]) ? Constant::$USER_ROLES[$model->role_id] : "未知";
                    },
            ],
            [
                'class' => 'yii\grid\ActionColumn',
                'header' => '操作',
                'template' => "{update} {view}",
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
