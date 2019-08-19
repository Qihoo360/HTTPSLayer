<?php

namespace app\models;

use PHPUnit\Framework\Exception;
use Yii;

/**
 * This is the model class for table "global_config".
 *
 * @property integer $id
 * @property integer $project_id
 * @property string $content
 * @property integer $status
 * @property integer $user_id
 * @property string $create_time
 * @property string $update_time
 * @property BizUser $bizUser
 */
class GlobalConfig extends \app\models\BaseActiveRecord
{
    const STATUS_INVALID = 0;
    const STATUS_PRE_RELEASE = 1;
    const STATUS_RELEASE = 2;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'global_config';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['content'], 'required'],
            [['content'], 'string'],
            [['status'], 'integer'],
            [['create_time', 'update_time'], 'safe'],
            [['user_id', 'project_id'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'content' => '配置内容',
            'project_id' => '项目',
            'status' => '状态',
            'create_time' => "创建时间",
            'update_time' => "更新时间",
            "user_id" => "创建者",
        ];
    }

    /**
     * 按照当前全局配置状态生成全局配置记录
     * @return bool
     */
    public function autoCreate()
    {
        $this->status = self::STATUS_INVALID;


        $certificates = Certificate::findAll(['status' => \Constant::VALID]);
        $cert_list = [];
        if (!empty($certificates)) {
            foreach ($certificates as $certificate) {
                $regs = [];
                $id = $certificate->id;
                $cert_redis_key = $certificate->pub_key;
                $pkey_redis_key = $certificate->priv_key;
                $pids = [];
                if (!empty($certificate->certHosts)) {
                    foreach ($certificate->certHosts as $certHost) {
                        $regs[] = $certHost->name;
                    }
                }

                if (!empty($certificate->relProjCerts)) {
                    foreach ($certificate->relProjCerts as $relPorjCert) {
                        /**
                         * @var RelPorjCert $relPorjCert
                         */
                        if (!empty($relPorjCert->project->label)) {
                            $pids[] = $relPorjCert->project->label;
                        }
                    }
                } else {
                    continue;
                }
                $cert_list[$id] = [
                    "pids" => $pids,
                    "regs" => $regs,
                    "cert_redis_key" => $cert_redis_key,
                    "pkey_redis_key" => $pkey_redis_key,
                ];
            }
        }

        /**
         * @var $projects Project[]
         */
        $projects = Project::find()->all();
        $balancer = [];
        if (!empty($projects)) {
            foreach ($projects as $project) {
                $project_label = $project->label;
                $t = 1;
                $vips_in_group = [];
                if (!empty($project->balances)) {
                    foreach ($project->balances as $balance) {
                        /**
                         * @var $balance Balance
                         */
                        $vip = [
                            'vip' => $balance->vip . "",
                            'w' => $balance->weight . "",
                        ];
                        $vips_in_group[$balance->qfe_idc][] = $vip;
                    }
                } else {
                    continue;
                }
                if (!empty($project_label)) {
                    $balancer[$project_label] = [
                        't' => $t,
                        'vips' => $vips_in_group,
                    ];
                }

            }
        }
        $content = [
            'cert' => [
                'list' => $cert_list,
            ],
            'balancer' => $balancer,
            'app' => Project::validAsLabelList(),
        ];

        $this->content = json_encode($content);
        $this->user_id = Context::getInstance()->bizUser()->id;
        $this->project_id = 0;
        return $this->save();
    }

    /**
     * 为项目创建配置
     * @param $project_id
     * @return bool
     */
    public function autoCreate4Project($project_id) {
        $project = Project::findOne($project_id);
        if (empty($project)) {
            return false;
        }
        $t = 1;
        $vips_in_group = [];
        if (!empty($project->balances)) {
            foreach ($project->balances as $balance) {
                /**
                 * @var $balance Balance
                 */
                $vip = [
                    'vip' => $balance->vip . "",
                    'w' => $balance->weight . "",
                ];
                $vips_in_group[$balance->qfe_idc][] = $vip;
            }
        } else {
            return false;
        }

        $balancer = [
            't' => $t,
            'vips' => $vips_in_group,
        ];
        $content['balancer'] = $balancer;
        $this->content = json_encode($content);
        $this->user_id = Context::getInstance()->bizUser()->id;
        $this->project_id = $project_id;
        return $this->save();
    }

    /**
     * 以一个已存在的全局配置创建新的记录
     * @param $model GlobalConfig
     * @return bool
     */
    public function rollbackCreate($model)
    {
        $this->status = self::STATUS_INVALID;
        $this->content = $model->content;
        $this->user_id = Context::getInstance()->bizUser()->id;
        $this->project_id = $model->project_id;
        return $this->save();
    }

    /**
     * 预发布操作
     * @param $tip
     * @return bool
     */
    public function preRelease(&$tip)
    {
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            $latest_model = $this->findLatestModel($this->project_id);

            if ($this->id == $latest_model->id) {
                /**
                 * @var $global_configs GlobalConfig[]
                 */
                $global_configs = self::find()->where(['project_id' => $this->project_id,'status' => self::STATUS_PRE_RELEASE])->all();
                if (!empty($global_configs)) {
                    $tip = "请先将已经预发布的配置文件取消";
                    return false;
                }
                if (in_array($this->status, [GlobalConfig::STATUS_INVALID])) {
                    $this->status = GlobalConfig::STATUS_PRE_RELEASE;
                    $this->save();
                }
            }
            $transaction->commit();
            return true;
        } catch (Exception $e) {
            $transaction->rollBack();
            $tip = "数据库错误,预发布失败";
            return false;
        }
    }

    /**
     * 发布操作
     * @return bool
     */
    public function release()
    {
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            $latest_model = $this->findLatestModel($this->project_id);

            if ($this->id == $latest_model->id) {
                /**
                 * @var $global_configs GlobalConfig[]
                 */
                $global_configs = self::find()->where(['project_id' => $this->project_id, 'status' => self::STATUS_RELEASE])->all();
                foreach ($global_configs as $global_config) {
                    $global_config->status = self::STATUS_INVALID; // 已经存在的已上线记录更新为失效
                    $global_config->save();
                }
                if (in_array($this->status, [GlobalConfig::STATUS_INVALID, GlobalConfig::STATUS_PRE_RELEASE])) {
                    $this->status = GlobalConfig::STATUS_RELEASE;
                    $this->save();
                }
            }
            $transaction->commit();
            return true;
        } catch (Exception $e) {
            $transaction->rollBack();
            return false;
        }

    }

    /**
     * 失效一个配置文件(逻辑上只处理预发布状态的)
     * @return bool
     */
    public function invalid()
    {
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            if (in_array($this->status, [GlobalConfig::STATUS_PRE_RELEASE])) {
                $this->status = GlobalConfig::STATUS_INVALID;
                $this->save();
            }
            $transaction->commit();
            return true;
        } catch (Exception $e) {
            $transaction->rollBack();
            return false;
        }

    }

    /**
     * 将全局配置文件按照idc摘出对应的配置返回配置数组。
     * @param $idc
     * @return bool|mixed
     */
    public function configIDC($idc)
    {
        $idc = strtoupper($idc);
        $idcs = \Constant::getIdcs();
        if (in_array($idc, $idcs)) {
            $content = $this->content;
            $info = json_decode($content, true);
            $info['label'] = Project::validAsLabelList();
            if (!empty($info['balancer'])) {
                foreach ($info['balancer'] as $project_id => $_b) {
                    if (!empty($_b['vips'][$idc])) {
                        $info['balancer'][$project_id]['vips'] = $info['balancer'][$project_id]['vips'][$idc];
                    } else {
                        unset($info['balancer'][$project_id]);
                    }
                }
                return $info;
            } else {
                return $info;
            }
        } else {
            return false;
        }
    }

    public function configIDC4Project($idc) {
        $idc = strtoupper($idc);
        $idcs = \Constant::getIdcs();

        if (in_array($idc, $idcs)) {
            $content = $this->content;
            $info = json_decode($content, true);
            if (!empty($info['balancer']['vips'][$idc])) {
                $info['balancer']['vips'] = $info['balancer']['vips'][$idc];
            } else {
                $info['balancer']['vips'] = [];
            }
            return $info;
        } else {
            return false;
        }
    }

    /**
     * 获取最新的全局配置文件
     * @param int $project_id
     * @return array|null|\yii\db\ActiveRecord
     */
    public static function findLatestModel($project_id = 0)
    {
        $query = self::find();
        $query->limit(1);
        $query->where([
            'project_id' => $project_id,
        ]);
        $query->orderBy(
            [
                "id" => SORT_DESC,
            ]
        );
        return $query->one();
    }

    /**
     * 获取当前线上 已发布 的全局配置文件,业务逻辑上保证只有一份, 这里如果多个会取第一个
     * @param int $project_id
     * @return array|null|\yii\db\ActiveRecord
     */
    public static function findReleaseModel($project_id = 0)
    {
        $query = self::find();
        $query->limit(1);
        $query->where([
            'project_id' => $project_id,
            'status' => self::STATUS_RELEASE]);
        $query->orderBy(
            [
                "id" => SORT_DESC,
            ]
        );
        return $query->one();

    }

    /**
     * 获取当前线上 预发布 的全局配置文件,业务逻辑上保证只有一份, 这里如果多个会取第一个
     * @param int $project_id
     * @return array|null|\yii\db\ActiveRecord
     */
    public static function findPreReleaseModel($project_id = 0)
    {
        $query = self::find();
        $query->limit(1);
        $query->where([
            'project_id' => $project_id,
            'status' => self::STATUS_PRE_RELEASE]);
        $query->orderBy(
            [
                "id" => SORT_DESC,
            ]
        );
        return $query->one();
    }


    public function getBizUser()
    {
        return $this->hasOne(BizUser::className(), ['id' => 'user_id']);
    }

}

