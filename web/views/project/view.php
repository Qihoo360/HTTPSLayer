<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\Project */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => '业务列表', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="project-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('修改', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('证书配置', ['addcertificate', 'id' => $model->id], ['class' => 'btn btn-info']) ?>
        <?= Html::a('负载均衡配置', ['addbalance', 'id' => $model->id], ['class' => 'btn btn-default']) ?>
        <?php // echo Html::a('频率控制配置', ['/frequency', 'project_id' => $model->id], ['class' => 'btn btn-success']) ?>
        <?php // echo Html::a('生成发布配置', ['globalconfig/index', 'project_id' => $model->id], ['class' => 'btn btn-default']) ?>
    </p>

    <h3>
        业务基础信息
    </h3>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'name',
            'label',
            [
                'attribute' => 'user_id',
                'value' => !empty($model->bizUser->name) ? $model->bizUser->name : "",
            ],
            'contact_email',
            'create_time',
            'update_time',
        ],
    ]) ?>

    <h3>关联的证书信息</h3>
    <?php

    $certificate_detail = "";
    if (!empty($model->relProjCerts)) {
        foreach ($model->relProjCerts as $relPorjCert) {
            /**
             * @var $relPorjCert \app\models\RelPorjCert
             */


            $certificate = $relPorjCert->certificate;

            $certificate_name = $certificate->name;
            $certificate_name_str = Html::a($certificate_name, ['certificate/view', 'id' => $certificate->id]);
            $certificate_host_str = "";
            if (!empty($certificate->certHosts)) {
                foreach ($certificate->certHosts as $certHost) {
                    $certificate_host_str .= $certHost->name . "<br>";

                }
            }
            $certificate_detail .= <<<html
<tr>
<th>
${certificate_name_str}
</th>
<td>
${certificate_host_str}
</td>
</tr>

html;
        }
        $table = <<<html
<table class='table table-bordered table-striped'>
    <tbody>
        <tr>
            <th>
            证书
            </th>
            <td>
            证书域名
            </td>
        </tr>
        {$certificate_detail}
    </tbody>
</table>

html;
        echo $table;
    }


    ?>

    <h3>
        业务域名
    </h3>

    <?php
    $proj_host_str = "";
    if (!empty($model->projHosts)) {
        foreach ($model->projHosts as $projHost) {
            $proj_host_name = Utils::xssStrip($projHost->name);
            $proj_host_str .= <<<html
<tr>
<td>
{$proj_host_name}
</td>
</tr>

html;

        }
    }
    $table_proj_host = <<<html
<table class="table table-bordered table-striped">
<tbody>
{$proj_host_str}
</tbody>
</table> 
html;
    echo $table_proj_host;


    ?>
    <h3>负载均衡</h3>

    <?php
    $balance_group = [];
    if (!empty($model->balances)) {
        foreach ($model->balances as $balance) {
            $balance_group[$balance->qfe_idc][] = $balance;
        }
    }
    $qfe_idcs = Constant::getQfeIdc();
    $vip_locations = Constant::getVipLocation();

    if (!empty($balance_group)) {
        foreach ($balance_group as $qfe_idc => $balances) {
            $balance_str = "";
            $qfe_idc_name = isset($qfe_idcs[$qfe_idc])? $qfe_idcs[$qfe_idc] : $qfe_idc;
            foreach ($balances as $_balance) {
                $location = $_balance->location;
                $location_name = isset($vip_locations[$location])? $vip_locations[$location]: $location;
                $vip = $_balance->vip;
                $weight = $_balance->weight;
                $balance_str .= <<<html
<tr>
<td>
{$location_name}
</td>
<td>
{$vip}
</td>
<td>
{$weight}%
</td>
</tr>
html;
        }
            $table_balance = <<<html
            <div class="row">
            
            <div class="col-lg-9">
            <h5>接入层集群: <span class="label label-success">{$qfe_idc_name}</span></h5>
<table class="table table-striped table-bordered">
<thead>
<tr>
<th>
业务集群
</th>
<th>
VIP
</th>
<th>
权重
</th>
</tr>
</thead>
<tbody>
{$balance_str}
</tbody>
</table>

    

</div>
</div>
            
html;
            echo $table_balance;
        }
    }

    ?>

</div>
