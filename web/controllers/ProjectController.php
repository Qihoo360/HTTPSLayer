<?php

namespace app\controllers;

use app\models\AddBalanceForm;
use app\models\AddCertificateForm;
use app\models\Balance;
use app\models\BizUser;
use app\models\Certificate;
use app\models\Context;
use app\models\ProjectForm;
use Yii;
use app\models\Project;
use app\models\ProjectSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * ProjectController implements the CRUD actions for Project model.
 */
class ProjectController extends BaseController
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
     * Lists all Project models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new ProjectSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'bizuser_dict' => BizUser::validAsDict(\Constant::VALID),
        ]);
    }

    /**
     * Displays a single Project model.
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
     * Creates a new Project model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new ProjectForm();
        if (Yii::$app->request->isPost) {
            $post = Yii::$app->request->post();
            if ($model->load($post)) {
                $model->host_names = $post[$model->formName()]['host_names'];
                $model->user_id = Context::getInstance()->bizUser()->id;

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
     * Updates an existing Project model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = ProjectForm::findOne($id);
        $user_id = $model->user_id;

        foreach ($model->projHosts as $_host) {
            $model->host_names[] = $_host->name;
        }

        if (Yii::$app->request->isPost) {
            $post = Yii::$app->request->post();
            if ($model->load($post)) {
                $model->user_id = $user_id;

                $model->host_names = $post[$model->formName()]['host_names'];
                if ($model->save()) {
                    return $this->redirect(['view', 'id' => $model->id]);
                }
            }
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Project model.
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

    /**
     * 添加证书
     * @param $id
     * @return string|\yii\web\Response
     */
    public function actionAddcertificate($id)
    {
        $model = AddCertificateForm::findOne($id);

        foreach ($model->relProjCerts as $relPorjCert) {
            $model->certificate_ids[] = $relPorjCert->certificate_id;
        }


        if (Yii::$app->request->isPost) {

            $post = Yii::$app->request->post();
            if ($model->load($post)) {
                $model->certificate_ids = $post[$model->formName()]['certificate_ids'];
                if ($model->save()) {
                    return $this->redirect(['view', 'id' => $model->id]);
                }
            }
        }
        return $this->render('addcertificate', [
            'model' => $model,
            'certificate_dict' => Certificate::validAsDict(\Constant::VALID),
        ]);
    }


    /**
     * 添加负载均衡
     * @param $id
     * @return string|\yii\web\Response
     * @throws \yii\base\InvalidConfigException
     */
    public function actionAddbalance($id)
    {
        $model = AddBalanceForm::findOne($id);
        foreach ($model->balances as $balance) {
            /**
             * @var $balance Balance
             */
            $current = $model->{"project_balances_".$balance->qfe_idc};
            $current[] =  $balance->toArray();
            $model->{"project_balances_".$balance->qfe_idc} = $current;
        }
        $tip = "";
        if (Yii::$app->request->isPost) {
            $post = Yii::$app->request->post();
            if ($model->load($post)) {
                $idcs = \Constant::getIdcs();
                foreach ($idcs as $idc) {
                    $model->{"project_balances_" . $idc} = $post[$model->formName()]['project_balances_' . $idc];
                }
            }

            if ($model->save()) {
                return $this->redirect(['view', 'id' => $model->id]);
            } else {
                $tip = Context::$global_message;
            }
        }

        return $this->render('addbalance', [
            'model' => $model,
            "tip" => $tip,
        ]);
    }

    /**
     * Finds the Project model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Project the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Project::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
