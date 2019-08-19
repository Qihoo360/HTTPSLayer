<?php

namespace app\controllers;

use Yii;
use app\models\Frequency;
use app\models\FrequencySearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use app\models\Project;
use app\models\Context;
use yii\web\ServerErrorHttpException;


/**
 * FrequencyController implements the CRUD actions for Frequency model.
 */
class FrequencyController extends BaseController
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
     * Lists all Frequency models.
     * @param $project_id 项目id
     * @return mixed
     */
    public function actionIndex($project_id)
    {
        $searchModel = new FrequencySearch();
        $searchModel->project_id = $project_id;

        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'project_id' => $project_id,
        ]);
    }

    /**
     * Displays a single Frequency model.
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
     * @param $project_id 项目id
     * Creates a new Frequency model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate($project_id)
    {
        $model = new Frequency();

        $project = Project::findOne($project_id);
        if (!$project) {
            throw new NotFoundHttpException('unknow project_id');
        }

        if ($model->load(Yii::$app->request->post())) {
            $model->create_user = Context::getInstance()->bizUser()->id;
            $model->update_operation = Frequency::OP_CREATE;
            $model = Frequency::formatData($model);

            if ($model->save()) {
                return $this->redirect(['index', 'project_id' => $model->project_id]);
            }
        } else {
            return $this->render('create', [
                'model' => $model,
                'project' => $project
            ]);
        }
    }

    /**
     * Updates an existing Frequency model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        $project = Project::findOne($model->project_id);
        if (!$project) {
            throw new NotFoundHttpException('unknow project_id');
        }

        if ($model->load(Yii::$app->request->post())) {
            $model->update_user = Context::getInstance()->bizUser()->id;
            $model->update_operation = Frequency::OP_UPDATE;
            $model = Frequency::formatData($model);

            if ($model->hasErrors()) {
                return $this->render('update', [
                    'model' => $model,
                    'project' => $project
                ]);
            }

            if ($model->save()) {
                return $this->redirect(['index', 'project_id' => $model->project_id]);
            }
        } else {
            return $this->render('update', [
                'model' => $model,
                'project' => $project
            ]);
        }
    }

    /**
     * Deletes an existing Frequency model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDisable($id)
    {
        $model = $this->findModel($id);
        $model->status = Frequency::STATUS_DELETED;
        $model->update_operation = Frequency::OP_DELETE;

        $model->save();

        return $this->redirect(Yii::$app->request->referrer);
    }

    /**
     * 开启某一条path配置
     * @param $id
     * @return \yii\web\Response
     */
    public function actionOnline($id)
    {
        $model = $this->findModel($id);

        if ($model->status = Frequency::STATUS_CLOSE) {
            $model->status = Frequency::STATUS_OPEN;
            $model->update_operation = Frequency::OP_ONLINE;
        }

        $model->save();

        return $this->redirect(Yii::$app->request->referrer);
    }

    /**
     * 关闭某一条path配置
     * @param integer $id
     * @return \yii\web\Response
     */
    public function actionOffline($id)
    {
        $model = $this->findModel($id);

        if ($model->status == Frequency::STATUS_OPEN) {
            $model->status = Frequency::STATUS_CLOSE;
            $model->update_operation = Frequency::OP_OFFLINE;
        }

        $model->save();

        return $this->redirect(Yii::$app->request->referrer);
    }

    /**
     * 上线某个项目的所有开启状态的path
     * @param $project_id
     * @return \yii\web\Response
     * @throws ServerErrorHttpException
     */
    public function actionRelease($project_id)
    {
        $model = new Frequency();
        $ret = $model->releaseByProjectId($project_id);
        if ($ret) {
            return $this->redirect(['/frequency-version', 'project_id' => $project_id]);
        } else {
            throw new ServerErrorHttpException("发布失败, 请重试");
        }
    }

    /**
     * Finds the Frequency model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Frequency the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Frequency::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
