<div class="col-md-offset-3 col-md-6">
    <div class="panel panel-info">
        <div class="panel-heading">
            <h3 class="panel-title">Select</h3>
        </div>
        <div class="panel-body">
            <div class="row">
                <?php
                $params = Yii::$app->params;
                $auth_method = safe_get_str($params, 'authmethod');
                if ($auth_method == 'oauth') {
                    try {
                        $auth2 = safe_get_array($params, 'auth2');
                        $clients = safe_get_array($auth2, 'clients');
                        $auth_clients = [];
                        if (!empty($clients)) {
                            foreach ($clients as $client) {
                                $name = safe_get_str($client, 'name');
                                $imgUrl = safe_get_str($client, 'imgUrl');
                                echo \app\oauth2\widgets\CommonWidget::widget([
                                    'baseAuthUrl' => ['login/auth'],
                                    'popupMode' => false,
                                    'clientKey' => $name,
                                    'imgUrl' => $imgUrl,
                                ]);
                            }
                        }
                    } catch (Exception $e) {
                        echo $e->getMessage();
                    }
                }
                ?>
            </div>
        </div>
    </div>
</div>


