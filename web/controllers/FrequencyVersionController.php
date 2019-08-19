<?php

namespace app\controllers;

use Yii;
use app\models\FrequencyVersion;
use app\models\FrequencyVersionSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * FrequencyVersionController implements the CRUD actions for FrequencyVersion model.
 */
class FrequencyVersionController extends BaseController
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
     * Lists all FrequencyVersion models.
     * @param integer $project_id
     * @return mixed
     */
    public function actionIndex($project_id)
    {
        $searchModel = new FrequencyVersionSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'project_id' => $project_id
        ]);
    }

    /**
     * Displays a single FrequencyVersion model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new FrequencyVersion model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new FrequencyVersion();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing FrequencyVersion model.
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
     * Deletes an existing FrequencyVersion model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
//    public function actionDelete($id)
//    {
//        $this->findModel($id)->delete();
//
//        return $this->redirect(['index']);
//    }

    public function actionRollback($id)
    {
        $model = $this->findModel($id);
        $ret = $model->rollback();

        if ($ret) {
            return $this->redirect(Yii::$app->request->referrer);
        } else {
            throw new ServerErrorHttpException("回滚到上一个版本失败，请重试");
        }
    }

    public function actionReonline($id)
    {
        $model = $this->findModel($id);
        $ret = $model->reonline();
        if ($ret) {
            return $this->redirect(Yii::$app->request->referrer);
        } else {
            throw new ServerErrorHttpException("重新发布失败，请重试");
        }

    }

    /**
     * Finds the FrequencyVersion model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return FrequencyVersion the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = FrequencyVersion::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
