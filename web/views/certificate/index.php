<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $searchModel app\models\CertificateSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
$this->title = '证书列表';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="certificate-index">

    <h1><?php // echo Html::encode($this->title) ?></h1>
    <?php echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?= Html::a('添加证书', ['create'], ['class' => 'btn btn-success']) ?>
    </p>
    <?php Pjax::begin(); ?>    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'name',
            [
                'attribute' => 'status',
                'format' => 'raw',
                'value' =>
                    function ($model) {
                        switch ($model->status) {
                            case Constant::VALID:
                                return "<span class='label label-success'>" . Constant::$COMMON_STATUS[$model->status] . "</span>";
                                break;
                            case Constant::INVALID:
                                return "<span class='label label-danger'>" . Constant::$COMMON_STATUS[$model->status] . "</span>";

                                break;
                            default:
                                return "";
                                break;
                        }
                    },
            ],
//            'name',
            [
//                'attribute' => 'name',
                'label' => "域名",
                'format' => 'raw',
                'value' =>
                    function($model) {
//                        /**
//                         * @var $model \app\models\Certificate
//                         */
                        $host_str = "";
                        foreach ($model->certHosts as $host) {
                            $host_str .= "{$host->name}<br>";
                        }
                        return $host_str;
                    }
            ],
//            'priv_key',
//            'pub_key',
            'serial_no',
//            'subject',
            'priority',
//            'algorithm',
//            'issuer',
            'valid_start_time',
            [
                'attribute' => 'valid_end_time',
                'format' => 'raw',
                'value' => function ($model) {
                    /* @var $model app\models\CertificateSearch */
                    $current_time = date("Y-m-d H:i:s");
                    if ($model->valid_end_time < $current_time) {
                        return $model->valid_end_time . <<<html
 <span class='label label-danger'>已过期</span>
html;
                    } else if ($model->valid_start_time > $current_time) {
                        return $model->valid_end_time . <<<html
 <span class='label label-warning'>未开始</span>
html;

                    } else {
                        return $model->valid_end_time . <<<html
 <span class='label label-success'>使用中</span>

html;

                    }
                }
            ],
            'create_time',
            'contact_email:email',
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
                                'class' => 'btn btn-xs btn-success',
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
