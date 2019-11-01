<?php

/* @var $this yii\web\View */
/* @var $name string */
/* @var $message string */
/* @var $exception Exception */

use yii\helpers\Html;

if (empty($name)) {
    $name = "";
}
$this->title = $name;
?>
<div class="site-error">


    <div class="panel panel-danger">
        <div class="panel-heading">
            <h3> 服务器拒绝了该请求 </h3>
            <h5>
                <span class="label label-danger">错误信息</span>:
                <?= nl2br(Html::encode($message)) ?>
            </h5>
            <h5> 如果您有疑问请联系我们,联系方式 </h5>
        </div>
        <div class="panel-body">
            <div class="row">
                <div class="col-lg-6">
                    <table class="table table-condensed">
                        <thead>
                        <tr class="active">
                            <th>联系人</th>
                            <th>蓝信</th>
                            <th>邮箱</th>
                            <th>电话</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr class="warning">
                            <td>管理员1</td>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="panel panel-danger">
                <div class="panel-heading">
                    <h3> 此页面的基本原因分析 </h3>
                </div>
                <div class="panel-body">
                    <p> 1. 用户未申请注册开通本后台的权限</p>
                    <p> 2. 登录用户没有对应页面的访问权限</p>
                    <p> 3. 登录用户没有对应数据内容的数据访问权限</p>
                    <p> 4. 访问域名出错 </p>
                    <p> 5. 服务端异常,需联系对应人员排查问题,排查时请提供
                        <span class="label label-danger">错误信息</span>及
                        <span class="label label-danger">当前页面在浏览器地址栏中的地址</span>
                    </p></div>
            </div>

        </div>
    </div>


