<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\GlobalConfig */
/* @var $latestModel app\models\GlobalConfig */
/* @var $tip string */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => '全局配置列表', 'url' => ['index', 'project_id' => $model->project_id]];
$this->params['breadcrumbs'][] = $this->title;


?>


<div class="global-config-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <div class="row">
        <div class="col-lg-8">

            <?php
            if (!empty($tip)) {
                echo "
<div class=\"alert alert-warning alert-dismissible\" role=\"alert\">
                <button type=\"button\" class=\"close\" data-dismiss=\"alert\"><span aria-hidden=\"true\">&times;</span><span class=\"sr-only\">Close</span></button>
                <strong>失败!</strong> $tip
            </div>                
                
                ";
            }
            ?>
        </div>
    </div>

    <p>

        <?php
        //        if (Utils::configCanPreRelease($model, $latestModel)) {
        echo Html::a('与线上对比', ['compare', 'id' => $model->id], [
            'class' => 'btn btn-primary',
        ]);
        //        }
        ?>
        <?php
        if (Utils::configCanRelease($model, $latestModel)) {
            echo Html::a('发布', ['release', 'id' => $model->id], [
                'class' => 'btn btn-danger',
                'data' => [
                    'confirm' => '确定执行发布操作吗?',
                    'method' => 'post',
                ],
            ]);
        }
        ?>

        <?php
        if (Utils::configCanInvalid($model, $latestModel)) {
            echo Html::a('取消', ['invalid', 'id' => $model->id], [
                'class' => 'btn btn-info',
                'data' => [
                    'confirm' => '确定执行取消操作吗?',
                    'method' => 'post',
                ],
            ]);
        }
        ?>
    </p>

    <?php
    $pretty_json = Utils::jsonPrettyInHtml($model->content);
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
        $status_str = "<span class='{$class}'>" . Constant::$GLOBAL_CONFIG_STATUS[$model->status] . "</span>";
    } else {
        $status_str = "<span class='{$class}'>未知</span>";
    }

    echo DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            [
                "attribute" => 'content',
                'format' => 'raw',
                'value' => $pretty_json,
            ],
            [
                'attribute' => "status",
                'format' => 'raw',
                'value' => $status_str,
            ]
        ],
    ]) ?>

</div>
