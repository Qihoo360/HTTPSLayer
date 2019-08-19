<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

\app\assets\JsondiffpatchAsset::register($this);
/* @var $this yii\web\View */
/* @var $model app\models\GlobalConfig */
/* @var $releaseModel app\models\GlobalConfig */
/* @var $latestModel app\models\GlobalConfig */
/* @var $tip string */

$this->title = "与线上对比" . $model->id;
$this->params['breadcrumbs'][] = ['label' => '全局配置列表', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = $this->title;


?>


<div class="global-config-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?php
        if (Utils::configCanPreRelease($model, $latestModel)) {
            echo Html::a('预发布', ['prerelease', 'id' => $model->id], [
                'class' => 'btn btn-primary',
                'data' => [
                    'confirm' => '确定执行预发布操作吗?',
                    'method' => 'post',
                ],
            ]);
        }
        ?>
        <?php
        $pretty_json_current = Utils::jsonPrettyInHtml($model->content);
        if (!empty($releaseModel)) {
            $pretty_json_release = Utils::jsonPrettyInHtml($releaseModel->content);

        } else {
            $pretty_json_release = "<div class='label label-danger'>不存在线上正在生效的文件</div>";
        }

        $table = <<<html
<table class="table table-bordered">
<thead>
<tr>
<th>
当前
</th>
<th>
线上
</th>
<th>
对比
</th>
</tr>
</thead>
<td>
{$pretty_json_current}
</td>
<td>
{$pretty_json_release}
</td>
<td>
<div id="visual">
</div>
</td>

</table>
html;
        echo $table;



        ?>

</div>


<script>
    var current = <?php echo $model->content?>;
    var release = <?php
        if (!empty($releaseModel)) {
            echo $releaseModel->content;
        } else {
            echo "{}";
        }
        ?>;
    var delta = jsondiffpatch.diff(release, current);

    // beautiful html diff
    document.getElementById('visual').innerHTML = jsondiffpatch.formatters.html.format(delta, release);

    // self-explained json
//    document.getElementById('annotated').innerHTML = jsondiffpatch.formatters.annotated.format(delta, left);
</script>
