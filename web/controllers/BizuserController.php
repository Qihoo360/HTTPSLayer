<?php

namespace app\controllers;

use app\models\AuthMethod;
use Yii;
use app\models\BizUser;
use app\models\BizUserSearch;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * BizuserController implements the CRUD actions for BizUser model.
 */
class BizuserController extends BaseController
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all BizUser models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new BizUserSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single BizUser model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
            'auth_method' => $this->auth_method,
        ]);
    }

    /**
     * @param $id
     * @return mixed
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     * @throws \yii\base\Exception
     */
    public function actionAutopassword($id)
    {
        if ($this->auth_method == AuthMethod::METHOD_LOCAL) {
            $bizUser = Yii::$app->user->identity;
            if ($bizUser->is_admin) {
                $model = $this->findModel($id);
                $password = \Utils::getFixedPassword();
                $model->password = \Utils::hashPassword($password);
                $model->auth_key = \Utils::random_chars(10);
                $model->access_token = \Utils::random_chars(10);
                if ($model->save()) {
                    return $this->render('view', [
                        'model' => $this->findModel($id),
                        'auth_method' => $this->auth_method,
                        'extra'=> ['reset_password' => true]
                    ]);
//                    return $this->render(['view', 'id' => $model->id, 'extra'=> ['reset_password' => true]]);
                }
            }
        }
        throw new ForbiddenHttpException("not allow auto generate password");

    }

    /**
     * Creates a new BizUser model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     * @throws \yii\base\Exception
     */
    public function actionCreate()
    {
        $model = new BizUser();
        if (Yii::$app->request->isPost) {
            if ($model->load(Yii::$app->request->post())) {
                if ($this->auth_method == AuthMethod::METHOD_LOCAL) {
                    $password = \Utils::getFixedPassword();
                    $model->password = \Utils::hashPassword($password);
                    $model->auth_key = \Utils::random_chars(10);
                    $model->access_token = \Utils::random_chars(10);
                }

                if ($model->save()) {
                    return $this->redirect(['view', 'id' => $model->id]);
                }
            }
        }
        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing BizUser model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }


    /**
     * Finds the BizUser model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return BizUser the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = BizUser::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
