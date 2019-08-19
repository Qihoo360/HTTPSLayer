<?php

//use kartik\select2\Select2;
//use kartik\slider\Slider;
use unclead\multipleinput\MultipleInput;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\AddBalanceForm */
/* @var $certificate_dict array */
/* @var $tip string */

$this->title = '添加负载均衡: ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => '业务列表', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = '添加负载均衡';

?>
<style>
    .multi-input-block {
        margin: 10px;
        padding: 20px;
    }

    table {
        box-shadow: 0 0 5px grey;
    }
</style>

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

<div class="project-addcertificate">

    <h2><?= Html::encode($this->title) ?></h2>

    <div class="project-form">

        <?php $form = ActiveForm::begin(); ?>

        <?php
        $count = 0;
        $idcs = \Constant::getIdcs();
        foreach ($idcs as $k => $_idc) {
            $multi_input = $form->field($model, "project_balances_{$_idc}")->widget(MultipleInput::className(), [
                'max' => 20,
                'allowEmptyList' => true,

                'columns' => [
                    [
                        'name' => 'location',
                        'type' => 'dropDownList',
                        'title' => '业务集群',
                        'defaultValue' => 'SHBT',
                        'items' => Constant::getVipLocation(),
                        'options' => [
                        ]
                    ],
                    [
                        'name' => 'vip',
                        'title' => 'VIP',
                        'enableError' => true,
                        'options' => [
                        ]
                    ],
//                    [
//                        'name' => 'weight',
//                        'title' => '权重(请输入正数,且和值为100)',
//                        'enableError' => true,
//                        'options' => [
//                            'class' => 'input-weight'
//                        ]
//                    ],
//                    [
//                        'name' => 'weight',
//                        'title' => '权重',
//                        'type' => Slider::className(),
//                        'options' => [
//                            'pluginOptions' => [
//                                'min' => 0,
//                                'max' => 100,
//                                'step' => 1,
//                                'tooltip' => 'always',
//                                'tooltip_position' => 'bottom',
//                            ],
//                            'handleColor' => Slider::TYPE_INFO,
//                            'sliderColor'=>Slider::TYPE_INFO,
//
//                        ]
//                    ],
                    [
                        'name' => 'weight',
                        'title' => '权重',
                        'type' => \yii2mod\slider\IonSlider::className(),
                        'options' => [
                            'pluginOptions' => [
                                'min' => 0,
                                'max' => 100,
                                'step' => 5,
//                                'force-edges' => true,
//                                'grid' => true,
//                                'tooltip' => 'always',
//                                'tooltip_position' => 'bottom',
                            ],
//                            'width' => '100px'

//                            'handleColor' => Slider::TYPE_INFO,
//                            'sliderColor'=>Slider::TYPE_INFO,

                        ]
                    ]
                ]

            ]);

            $random_colors = [
                "bg-primary",
                "bg-info",
//                "bg-default",
//                "bg-danger",
//                "bg-success",
//                "bg-warning",
            ];

            $random_color = $random_colors[$count % count($random_colors)];
            $div = <<<html
<div class="row ">
<div class="col-lg-10 col-lg-offset-1 multi-input-block ">
{$multi_input}
</div>
</div>
html;
            echo $div;
            $count++;


        }


        ?>

        <div class="form-group">
            <?= Html::submitButton('确认', ['class' => 'btn btn-warning']) ?>
        </div>

        <?php ActiveForm::end(); ?>

    </div>

</div>

