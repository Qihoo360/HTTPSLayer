<?php

namespace app\controllers;

use app\models\CertificateForm;
use app\models\CertUploadForm;
use Yii;
use app\models\Certificate;
use app\models\CertificateSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\UploadedFile;

/**
 * CertificateController implements the CRUD actions for Certificate model.
 */
class CertificateController extends BaseController
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
     * Lists all Certificate models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new CertificateSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Certificate model.
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
     * Creates a new Certificate model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new CertificateForm();
        $upload_form = new CertUploadForm();

        if (Yii::$app->request->isPost) {
            $post = Yii::$app->request->post();
            if ($model->load($post)) {
                $this->upload($upload_form, $model);

                if ($model->save()) {
                    return $this->redirect(['view', 'id' => $model->id]);
                }
            }
        }
        return $this->render('create', [
            'model' => $model,
            'upload_form' => $upload_form,
        ]);
    }

    /**
     * Updates an existing Certificate model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = CertificateForm::findOne($id);
        foreach ($model->certHosts as $_host) {
            $model->host_names[] = $_host->name;
        }
        // 传空默认用旧值的属性暂存
        $existed_pub_content = $model->pub_content;
        $existed_priv_content = $model->priv_content;
        $existed_pub_key = $model->pub_key;
        $existed_priv_key = $model->priv_key;

        $upload_form = new CertUploadForm();
        if (Yii::$app->request->isPost) {

            $post = Yii::$app->request->post();
            if ($model->load($post)) {

                // 用旧值
                if (empty($model->pub_content)) {
                    $model->pub_content = $existed_pub_content;
                    $model->pub_key = $existed_pub_key;
                }
                if (empty($model->priv_content)) {
                    $model->priv_content = $existed_priv_content;
                    $model->priv_key = $existed_priv_key;
                }
                $this->upload($upload_form, $model);
                if ($model->save()) {
                    return $this->redirect(['view', 'id' => $model->id]);
                }
            }
        }
        return $this->render('update', [
            'model' => $model,
            'upload_form' => $upload_form,
        ]);
    }


    /**
     * 下载证书
     * @param $id
     * @param $type
     * @return \yii\web\Response
     */
    public function actionDownload($id, $type)
    {
        $model = $this->findModel($id);
        if ($model) {
            $content = "";
            $file_name = "";
            $mime_type = "";
            switch ($type) {
                case Certificate::DOWNLOAD_PRIV:
                    $content = $model->priv_content;
                    $file_name = $model->priv_key . ".key";
                    $mime_type = 'text/plain';
                    break;
                case Certificate::DOWNLOAD_PUB:
                    $content = $model->pub_content;
                    $file_name = $model->pub_key . ".crt";
                    $mime_type = 'application/x-x509-ca-cert';
                    break;
                default:
                    break;
            };

            if (!empty($content) && !empty($file_name)) {
                Yii::$app->response->sendContentAsFile($content, $file_name, ['mimeType' => $mime_type]);
                return Yii::$app->response;
            }
        }

        return $this->redirect(['view','id' => $id]);

    }

    /**
     * Finds the Certificate model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Certificate the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Certificate::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

    /**
     * 上传文件,同时生成model相关字段
     *
     * @param $upload_form CertUploadForm
     * @param $model Certificate
     * @return bool
     */
    protected function upload($upload_form, &$model)
    {
        $upload_form->pub_file = UploadedFile::getInstance($upload_form, 'pub_file');
        $upload_form->priv_file = UploadedFile::getInstance($upload_form, 'priv_file');

        if (!empty($upload_form->pub_file) && !empty($upload_form->priv_file)) {
            return $upload_form->upload($model);
        } else {
            return true;
        }


    }
}
