<?php
/**
 * Created by PhpStorm.
 * User: zhangshuang
 * Date: 17/11/1
 * Time: 15:35
 */

namespace app\controllers;


use app\models\FrequencyVersion;
use app\models\GlobalConfig;
use app\models\Project;
use app\models\RedisQfe;

class ApiController extends BaseapiController
{
    /**
     *
     * webAPI 获取对应机房的配置
     * @param $host
     * @param int $ispre
     * @param string $v
     * @param string $t
     * @param string $token
     * @return object
     */
    public function actionConfig($host = "", $idc = "", $ispre = 0, $v = "", $t = "", $token = "")
    {
//        $idcrename = \Constant::qfeIdcRename();
//        \Yii::info("XXXXXX" . json_encode($idcrename));
        if (!$idc) {
            $idc = \Utils::idcFromHostname($host);
        }
        $idc = \Utils::idcRename($idc);
        $project_id = 0;
        /**
         * @var $model GlobalConfig
         */
        if ($ispre) {
            $model = GlobalConfig::findPreReleaseModel($project_id);
            if (empty($model)) {
                $model = GlobalConfig::findReleaseModel($project_id);
            }
        } else {
            $model = GlobalConfig::findReleaseModel($project_id);
        }


        if (!empty($model)) {
            $result = $model->configIDC($idc);
        } else {
            $result = false;
        }

        if ($result) {
            return $this->echoJson($result, 0, "", ['version' => $model->id]);
        } else {
            return $this->echoJson([], 1);
        }

    }

    /**
     *
     * webAPI 获取对应机房的配置
     * @param $host
     * @param int $ispre
     * @param string $v
     * @param string $t
     * @param string $token
     * @return object
     */
    public function actionProjectconfig($host, $label, $ispre = 0, $v = "", $t = "", $token = "")
    {
        $idc = $host;
        if (empty($label)) {
            return $this->echoJson([], 1);
        }
        $project = Project::findOne(['label' => $label]);
        if (empty($project)) {
            return $this->echoJson([], 1);
        }
        $project_id = $project->id;
        /**
         * @var $model GlobalConfig
         */
        if ($ispre) {
            $model = GlobalConfig::findPreReleaseModel($project_id);
            if (empty($model)) {
                $model = GlobalConfig::findReleaseModel($project_id);
            }
        } else {
            $model = GlobalConfig::findReleaseModel($project_id);
        }


        if (!empty($model)) {
            $result = $model->configIDC4Project($idc);
        } else {
            $result = false;
        }

        if ($result) {
            return $this->echoJson($result, 0, "", ['version' => $model->id]);
        } else {
            return $this->echoJson([], 1);
        }
    }


    public function actionFrequencyConfig()
    {
        $model = new FrequencyVersion();
        $db = RedisQfe::DB_FREQUENCYCONFIG_FILE;
        $key = FrequencyVersion::GLOBAL_VERSION_KEY;
        $list = [
            'config' => $model->findAllReleasedConfig(),
            'global_version' => RedisQfe::getInstance()->get($db, $key),
        ];

        return $this->echoJson($list);
    }
}