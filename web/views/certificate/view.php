<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\Certificate */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => '证书列表', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="certificate-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('修改', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
    </p>

    <?php
    $pub_key = "<span class='label label-success'>" .$model->pub_key . "</span>";
    $pub_down_btn = Html::a(
        "下载证书公钥",
        [
            'download',
            'id' => $model->id,
            'type' => \app\models\Certificate::DOWNLOAD_PUB,
        ],
        [
            'class' => 'btn btn-xs btn-info',
            'title' => '下载证书公钥',
            'data-pjax' => '0',
        ]
    );

    $priv_key = "<span class='label label-success'>" .$model->priv_key . "</span>";
    $priv_down_btn = Html::a(
        "下载证书私钥",
        [
            'download',
            'id' => $model->id,
            'type' => \app\models\Certificate::DOWNLOAD_PRIV,
        ],
        [
            'class' => 'btn btn-xs btn-danger',
            'title' => '下载证书私钥',
            'data-pjax' => '0',
        ]
    );

    echo DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'name',
            [
                'attribute' => 'priv_key',
                'format' => 'raw',
                'value' => $priv_key ." ". $priv_down_btn,
            ],
            [
                'attribute' => 'pub_key',
                'format' => 'raw',
                'value' => $pub_key ." ". $pub_down_btn,
            ],
            [
                'attribute' => 'status',
                'value' => Constant::$COMMON_STATUS[$model->status],
            ],
            'serial_no',
            'subject',
            'priority',
            'algorithm',
            'issuer',
            'valid_start_time',
            'valid_end_time',
            'create_time',
            'contact_email:email',
        ],
    ]) ?>

    <div class="row">
        <div class="col-lg-6">
            <h3>该证书绑定的域名信息</h3>
            <?php
            $host_str = "";

            foreach ($model->certHosts as $certHost) {
                $host_name = $certHost->name;
                $host_str .= <<<html
<tr>
<td>
{$host_name}
</td>
</tr>        

html;
            }
            $table_host = <<<html
<table class="table table-bordered table-striped">
    <tbody>
    {$host_str}
</tbody>
</table>
html;
            echo $table_host;
            ?>
        </div>
        <div class="col-lg-6">

            <h3>使用该证书的业务</h3>
            <?php
            $project_str = "";

            foreach ($model->relProjCerts as $relProjCert) {
                $project_name = $relProjCert->project->name;
                $project_name_str = Html::a($project_name, ["/project/view", 'id' => $relProjCert->project_id]);
                $project_str .=<<<html
<tr>
<td>
{$project_name_str}
</td>
</tr>
html;


            }

            $table_project = <<<html
<table class="table table-bordered table-striped">
<tbody>
{$project_str}
</tbody>
</table>
html;
            echo $table_project;
            ?>
        </div>
    </div>






</div>
