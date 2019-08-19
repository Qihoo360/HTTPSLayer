<?php

use yii\Helpers\Url;
use yii\helpers\Html;
use kartik\widgets\ActiveForm;
use app\models\Frequency;
use unclead\multipleinput\MultipleInput;
use app\models\Project;
use yii\web\NotFoundHttpException;


/* @var $this yii\web\View */
/* @var $model app\models\Frequency */
/* @var $form yii\widgets\ActiveForm */

$project_id = $project->id;
$project_name = $project->name;

?>

    <div class="frequency-form">

        <?php $form = ActiveForm::begin([
            'type' => ActiveForm::TYPE_HORIZONTAL,
            'options' => ['enctype' => 'multipart/form-data', 'id' => 'frequency_form']
        ]); ?>
        <div class="row">
            <div class="col-lg-8">
                <div class="form-group">
                    <label class="control-label col-md-2" for="frequency-path">所属项目</label>
                    <div class="col-md-10">
                        <input type="text" name="<?=$model->formName()?>[project_id]" id="acfform-name" class="form-control col-md-10 hide" value="<?=$project_id?>">
                        <input type="text" id="acfform-name" class="form-control col-md-10" aria-required="true" value="<?=$project_name?>" readonly>
                    </div>
                    <div class="help-block"></div>
                </div>

                <?= $form->field($model, 'path')->textInput(['maxlength' => true, 'placeholder' => '请输入要限频的路径']) ?>
                <?= $form->field($model, 'description')->textInput(['maxlength' => true, 'placeholder' => '请输入路径描述']) ?>

                <?php
                $allMethod = [
                    'GET' => 'GET',
                    'POST' => 'POST',
                    'HEAD' => 'HEAD',
                    'PUT' => 'PUT',
                    'DELETE' => 'DELETE',
                    'CONNECT' => 'CONNECT',
                    'OPTIONS' => 'OPTIONS',
                    'TRACE' => 'TRACE',
                    'PATCH' => 'PATCH'
                ];
                echo $form->field($model, 'method')->checkboxList($allMethod, [
                    'value' => isset($model->method)
                        ? explode(';', $model->method)
                        : ['GET'], 'inline' => true]);
                ?>

                <?= $form->field($model, 'according')->radioList(Frequency::$accordingToArray, [
                    'value' => isset($model->according)
                        ? $model->according
                        : Frequency::AC_IP_COOKIE, 'inline' => true]);
                ?>
                <div id="cookie-name-div">
                    <?= $form->field($model, 'cookie_name')->textInput(['maxlength' => true, 'placeholder' => '请输入Cookie名称']) ?>
                </div>
                <?php
                    $model->time_window = json_decode($model->time_window, true);
                ?>
                <?= $form->field($model, 'time_window')->widget(MultipleInput::className(), [
                    'max' => 20,
                    'min' => 1,
                    'columns' => [
                        [
                            'name' => 'interval',
                            'title' => '时间间隔',
                            'value' => function ($data) {
                                return $data['interval'];
                            },
                        ],
                        [
                            'name' => 'unit',
                            'type' => 'dropDownList',
                            'title' => '单位',
                            'defaultValue' => 's',
                            'items' => [
                                's' => '秒',
                                'm' => '分钟',
                                'h' => '小时',
                                'd' => '天',
                            ],
                            'value' => function ($data) {
                                return $data['unit'];
                            }
                        ],
                        [
                            'name' => 'count',
                            'title' => '允许访问次数',
                            'value' => function ($data) {
                                return $data['count'];
                            }
                        ]
                    ]
                ]);
                ?>
                <div class="form-group field-frequency-referer required">
                    <label class="control-label col-md-2" for="frequency-referer"><?= $model->attributeLabels()['referer'];?></label>
                    <div class="col-md-10"><textarea id="frequency-referer" class="form-control" name="Frequency[referer]" rows="3" aria-required="true"><?= $model->referer; ?></textarea></div>
                    <div class="col-md-offset-2 col-md-10"></div>
                    <div class="col-md-offset-2 col-md-10">
                        <div class="help-block">提示：请一行填写一个允许来源地址</div>
                    </div>
                </div>

                <div class="form-group field-frequency-arguments required">
                    <label class="control-label col-md-2" for="frequency-arguments"><?= $model->attributeLabels()['arguments'];?></label>
                    <div class="col-md-10"><textarea id="frequency-arguments" class="form-control" name="Frequency[arguments]" rows="3" aria-required="true" aria-invalid="false"><?= $model->arguments; ?></textarea></div>
                    <div class="col-md-offset-2 col-md-10"></div>
                    <div class="col-md-offset-2 col-md-10"><div class="help-block">提示：请一行填写一个必含参数</div></div>
                </div>

                <div class="form-group field-frequency-white_ip required">
                    <label class="control-label col-md-2" for="frequency-white_ip"><?= $model->attributeLabels()['white_ip'];?></label>
                    <div class="col-md-10"><textarea id="frequency-white_ip" class="form-control" name="Frequency[white_ip]" rows="2" aria-required="true" aria-invalid="false"><?= $model->white_ip; ?></textarea></div>
                    <div class="col-md-offset-2 col-md-10"></div>
                    <div class="col-md-offset-2 col-md-10"><div class="help-block">提示：请一行填写一个IP</div></div>
                </div>

                <div class="form-group field-frequency-black_ip required">
                    <label class="control-label col-md-2" for="frequency-black_ip"><?= $model->attributeLabels()['black_ip'];?></label>
                    <div class="col-md-10"><textarea id="frequency-black_ip" class="form-control" name="Frequency[black_ip]" rows="2" aria-required="true"><?= $model->black_ip; ?></textarea></div>
                    <div class="col-md-offset-2 col-md-10"></div>
                    <div class="col-md-offset-2 col-md-10"><div class="help-block">提示：请一行填写一个IP</div></div>
                </div>

                <?= $form->field($model, 'handle_way')->radioList(Frequency::$handleWayArray, [
                    'value' => isset($model->handle_way)
                        ? $model->handle_way
                        : Frequency::HD_LOG_ONLY, 'inline' => true]);
                ?>
                <div class="row">
                    <div class="col-md-10 col-md-offset-2">
                        <div class="handle_way_alert alert alert-info">当该路径访问超过设置的频次后，用户显示验证码；</div>
                    </div>
                </div>
                <div style="margin-top: 20px; margin-bottom: 100px;">
                    <div class="col-md-offset-4 col-md-2">
                        <div class="form-group">
                            <?= Html::submitButton($model->isNewRecord ? '　生 成 规 则　' : '　更 新 规 则　', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary', 'id' => 'submitButton']) ?>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <?= Html::a("　返 回 列 表　", Url::to('index'), ['class' => 'btn btn-default']) ?>
                    </div>
                </div>
            </div>
        </div>

        <?php ActiveForm::end(); ?>

    </div>

<?php
$AC_IP = Frequency::AC_IP;
$displayCookieNameInputJs = <<< JS
displayCookieNameInput($model->according)
$("input[name='Frequency[according]']").on('change', function() {
    displayCookieNameInput($(this).val());
})
    
// 显示输入cookie_name的input
function displayCookieNameInput(according) {
    var cookie_name_div = $("#cookie-name-div")
    if (according == $AC_IP && !cookie_name_div.hasClass('hidden')) {
        cookie_name_div.addClass('hidden')
    } else {
        cookie_name_div.removeClass('hidden')
    }
}


// 处理方式提示
$("input[name='Frequency[handle_way]']").on('change', function() {
    var arr = new Array();
    arr[1] = '只记录访问日志，不做任何操作；';
    arr[2] = '当该路径访问超过设置的频次后，用户显示验证码；';
    arr[3] = '将在Header中添加一个字段，可以通过 `\$_SERVER[""]` 获取；';

    $(".handle_way_alert").text(arr[$(this).val()]);
})
JS;

$this->registerJs($displayCookieNameInputJs);
?>