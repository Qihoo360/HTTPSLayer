<?php

/* @var $this \yii\web\View */

/* @var $content string */

use kartik\nav\NavX;
use yii\helpers\Html;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\widgets\Breadcrumbs;
use app\assets\AppAsset;

AppAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php echo Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
    <style>
        /* navbar */
        .navbar-default {
            background-color: rgba(0, 77, 249, 0.8);
            border-color: rgba(0, 77, 249, 0.4);
        }

        /* Title */
        .navbar-default .navbar-brand {
            color: #ffffff;
        }

        .navbar-default .navbar-brand:hover {
            color: rgba(50, 61, 213, 0.82);
        }

        .navbar-default .navbar-brand:focus {
            color: #0066d5;
        }

        /* Link */
        .navbar-default .navbar-nav > li > a {
            color: #FFFFFF;
        }

        .navbar-default .navbar-nav > li > a:hover {
            color: #FFFFFF;

        }

        .navbar-default .navbar-nav > li > a:focus {
            color: #0066d5;
        }

        .navbar-default .navbar-nav > .active > a,
        .navbar-default .navbar-nav > .active > a:hover,
        .navbar-default .navbar-nav > .active > a:focus {
            color: #0b1af9;
            background-color: #b5bfff;
        }

        .navbar-default .navbar-nav > .open > a,
        .navbar-default .navbar-nav > .open > a:hover,
        .navbar-default .navbar-nav > .open > a:focus {
            color: #ffffff;
            background-color: rgba(0, 77, 249, 0.2);
            box-shadow: 0 0 5px rgb(2, 30, 152);

        }

        /* Caret */
        .navbar-default .navbar-nav > .dropdown > a .caret {
            border-top-color: #ffffff;
            border-bottom-color: #00e0eb;
        }

        .navbar-default .navbar-nav > .dropdown > a:hover .caret, {
            border-top-color: #000000;
            border-bottom-color: #000000;
        }

        .navbar-default .navbar-nav > .dropdown > a:focus .caret {
            border-top-color: #000000;
            border-bottom-color: #000000;
        }

        .navbar-default .navbar-nav > .open > a .caret,
        .navbar-default .navbar-nav > .open > a:hover .caret,
        .navbar-default .navbar-nav > .open > a:focus .caret {
            border-top-color: #ffffff;
            border-bottom-color: #ffffff;
        }

        /* Mobile version */
        .navbar-default .navbar-toggle {
            border-color: rgba(221, 221, 221, 0);
        }

        .navbar-default .navbar-toggle:hover,
        .navbar-default .navbar-toggle:focus {
            background-color: #DDD;
        }

        .navbar-default .navbar-toggle .icon-bar {
            background-color: #CCC;
        }

        @media (max-width: 767px) {
            .navbar-default .navbar-nav .open .dropdown-menu > li > a {
                color: #ffffff;
            }

            .navbar-default .navbar-nav .open .dropdown-menu > li > a:hover,
            .navbar-default .navbar-nav .open .dropdown-menu > li > a:focus {
                color: #fff13e;
            }
        }
    </style>
    <style>
        .navbar-default {
            box-shadow: 0 0 3px rgb(2, 30, 152);
        }

        table {
            box-shadow: 0 0 2px black;
        }

        .breadcrumb {
            box-shadow: 0 0 2px grey inset;

        }

        .container-fluid {
            /*margin-top: 70px;*/
        }

        .btn-primary {
            box-shadow: 0 0 8px #00427b;
        }

        .btn-danger {
            box-shadow: 0 0 8px #900602;
        }

        .btn-info {
            box-shadow: 0 0 8px #007496;
        }

        .btn-warning {
            box-shadow: 0 0 8px #b56a00;
        }

        .btn-success {
            box-shadow: 0 0 8px #009400;
        }

        .btn-default {
            box-shadow: 0 0 8px #8a8a8a;
        }

        .label-primary {
            box-shadow: 0 0 8px #00427b inset;
        }

        .label-danger {
            box-shadow: 0 0 8px #900602 inset;
        }

        .label-info {
            box-shadow: 0 0 8px #007496 inset;
        }

        .label-warning {
            box-shadow: 0 0 8px #b56a00 inset;
        }

        .label-success {
            box-shadow: 0 0 8px #009400 inset;
        }

        .label-default {
            box-shadow: 0 0 8px #8a8a8a inset;
        }

    </style>
</head>
<body>
<?php $this->beginBody() ?>

<div class="wrap">
    <div class="row">
        <div class="col-lg-12">
            <?php
            $userInfo = Yii::$app->user->identity;
            $auth_mothod = safe_get_str(\Yii::$app->params, "authmethod", \app\models\AuthMethod::METHOD_OAUTH);

            if ($auth_mothod == \app\models\AuthMethod::METHOD_OAUTH) {
                $login_url = "/login/oauth";
            } else if ($auth_mothod == \app\models\AuthMethod::METHOD_LOCAL) {
                $login_url = "/login/local";
            } else {
                $login_url = "#";
            }

            NavBar::begin([
                'brandLabel' => 'HTTPSLayer',
                'brandUrl' => Yii::$app->homeUrl,
                'options' => [
                    'class' => 'navbar-default',
                ],
            ]);


            if (!empty($userInfo)) {
                echo Nav::widget([
                    'options' => ['class' => 'navbar-nav navbar-left'],
                    'items' => Utils::buildBarItems($userInfo),
                ]);

            }
            $login_button = ['label' => '登录', 'url' => [$login_url], 'class' => ['btn btn-info navbar-btn']];
            $logout_button = '<li>'
                . Html::beginForm(['/login/logout'], 'post', ['class' => 'navbar-form'])
                . Html::submitButton(
                    $userInfo['name'] . '&nbsp;<span class="glyphicon glyphicon-off" aria-hidden="true"></span>',
                    ['class' => 'btn btn-info']
                )
                . Html::endForm()
                . '</li>';
            if (safe_get_str(\Yii::$app->params, "authmethod", \app\models\AuthMethod::METHOD_LOCAL) == \app\models\AuthMethod::METHOD_LOCAL) {
                $resetpw_button = '<li>' . Html::a("修改密码", "/sitelogin/resetpassword") . "</li>";
            } else {
                $resetpw_button = "";
            }


            echo Nav::widget([
                'options' => ['class' => 'navbar-nav navbar-right'],
                'items' => [
                    empty($userInfo) ? $login_button : $resetpw_button . $logout_button
                ],
            ]);

            NavBar::end();
            ?>
        </div>

    </div>


    <div class="row">
        <div class="container-fluid">
            <div class="col-lg-2">
                <?php
                //                $navx = Utils::buildNavX(\app\models\Context::getInstance()->bizUser());
                //                echo NavX::widget($navx);
                ?>
            </div>
            <div class="col-lg-12">
                <?= Breadcrumbs::widget([
                    'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
                ]) ?>
                <?= $content ?>
            </div>
        </div>
    </div>


</div>

<footer class="footer">
    <div class="container-fluid">
        <p class="pull-left">&copy; HTTPSLayer管理后台 <?= date('Y') ?></p>
    </div>
</footer>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
