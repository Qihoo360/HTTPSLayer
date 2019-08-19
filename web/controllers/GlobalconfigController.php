<?php

namespace app\controllers;

use app\models\Project;
use Yii;
use app\models\GlobalConfig;
use app\models\GlobalConfigSearch;
use app\controllers\BaseController;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * GlobalconfigController implements the CRUD actions for GlobalConfig model.
 */
class GlobalconfigController extends BaseController
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
     * Lists all GlobalConfig models.
     * @param int $project_id
     * @return mixed
     */
    public function actionIndex($project_id = 0)
    {
        $searchModel = new GlobalConfigSearch();
        $searchModel->project_id = $project_id;
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'latestModel' => GlobalConfigSearch::findLatestModel($project_id),
        ]);
    }

    /**
     * Displays a single GlobalConfig model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        return $this->render('view', [
            'model' => $model,
            'latestModel' => GlobalConfigSearch::findLatestModel($model->project_id),
            'tip' => "",
        ]);
    }

    /**
     * Creates a new GlobalConfig model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @param int $project_id
     * @return mixed
     */
    public function actionCreate($project_id = 0)
    {
        $model = new GlobalConfig();
        if (!empty($project_id)) {
            $model->autoCreate4Project($project_id);
        } else {
            $model->autoCreate();
        }

        return $this->redirect(['index', 'project_id'=> $project_id]);

    }

    public function actionRollback($id)
    {
        $model = $this->findModel($id);


        if ($model->status == GlobalConfig::STATUS_INVALID) {
            $new_instance = new GlobalConfig();
            $new_instance->rollbackCreate($model);
        }

        return $this->redirect(['index', 'project_id' => $model->project_id]);
    }

    public function actionPrerelease($id)
    {
        $model = $this->findModel($id);
        $tip = "";
        $result = $model->preRelease($tip);
        if (!$result) {
            return $this->render('view', [
                'model' => $model,
                'latestModel' => GlobalConfigSearch::findLatestModel($model->project_id),
                'tip' => $tip,
            ]);
        }
        return $this->redirect(['view', 'id' => $model->id]);

    }

    public function actionRelease($id)
    {
        $model = $this->findModel($id);
        $model->release();

        return $this->redirect(['view', 'id' => $model->id]);

    }

    public function actionInvalid($id)
    {
        $model = $this->findModel($id);
        $model->invalid();

        return $this->redirect(['view', 'id' => $model->id]);

    }

    public function actionCompare($id) {
        $model = $this->findModel($id);
        $releaseModel = GlobalConfig::findReleaseModel($model->project_id);

        return $this->render('compare', [
            'model' => $model,
            'releaseModel' => $releaseModel,
            'latestModel' => GlobalConfigSearch::findLatestModel($model->project_id),
        ]);
    }


    /**
     * Finds the GlobalConfig model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return GlobalConfig the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = GlobalConfig::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }


}
