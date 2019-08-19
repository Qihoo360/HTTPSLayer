<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "frequency_version".
 *
 * @property integer $id
 * @property integer $project_id
 * @property string $project_label
 * @property string $data
 * @property string $version
 * @property string $online_date
 * @property string $update_date
 * @property integer $online_user
 * @property integer $update_user
 * @property integer $status
 */
class FrequencyVersion extends \yii\db\ActiveRecord
{

    const STATUS_ROLLBACK = 2; // 上过线
    const STATUS_ONLINE = 1;  // 在线
    const STATUS_OFFLINE = 0; // 下线

    public static $statusArray = [
        self::STATUS_ROLLBACK => '已回滚',
        self::STATUS_ONLINE => '在线',
        self::STATUS_OFFLINE => '下线'
    ];

    const GLOBAL_VERSION_KEY = 'freq_version';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'frequency_version';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['project_id', 'status'], 'integer'],
            [['project_label', 'data'], 'required'],
            [['project_label', 'data', 'online_user', 'update_user'], 'string'],
            [['online_date', 'update_date'], 'safe'],
            [['version'], 'string', 'max' => 32],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'project_id' => '项目id',
            'project_label' => '项目标签',
            'data' => '数据',
            'version' => '版本号',
            'online_date' => '发布日期',
            'update_date' => '操作日期',
            'online_user' => '发布用户',
            'update_user' => '操作用户',
            'status' => '状态',
        ];
    }

    /**
     * 下线所有当前版本之前的版本
     * @return int
     */
    private function offlinePreVersion()
    {
        $id = $this->id;
        $project_id = $this->project_id;

        FrequencyVersion::updateAll(['status' => FrequencyVersion::STATUS_OFFLINE], "project_id=$project_id and id<$id");

        return true;
    }

    /**
     * 上线新版本
     * @return bool
     */
    public function onlineNewVersion()
    {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $this->save();
            $ret = $this->release();
            if ($ret) {
                $transaction->commit();
            } else {
                $transaction->rollBack();
            }
            return $ret;
        } catch (Exception $e) {
            $transaction->rollBack();
            return false;
        }
    }

    /**
     * 查找当前版本的上一个版本
     * @return FrequencyVersion
     */
    private function findPreVersion()
    {
        /**
         * @var FrequencyVersion
         */
        $model = FrequencyVersion::find()
            ->where(['project_id' => $this->project_id])
            ->andWhere(['<', 'id', $this->id])
            ->orderBy('id desc')
            ->one();

        return $model;
    }

    /**
     * 重新上线
     * @return bool
     */
    public function reonline()
    {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            // 上线当前版本
            $ret = $this->release();
            if ($ret) {
                $transaction->commit();
            } else {
                $transaction->rollBack();
            }
            return $ret;
        } catch (Exception $e) {
            $transaction->rollBack();
            return false;
        }
    }

    /**
     * 回滚
     * @return bool
     */
    public function rollback()
    {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            // 下线当前版本
            $this->status = FrequencyVersion::STATUS_ROLLBACK;
            $ret = $this->save();

            // 上线上一个版本
            $releaseModel = $this->findPreVersion();
            if (!empty($releaseModel)) {
                $ret = $releaseModel->release();
            }

            if ($ret) {
                $transaction->commit();
            } else {
                $transaction->rollBack();
            }

            return $ret;
        } catch (Exception $e) {
            $transaction->rollBack();
            return false;
        }
    }

    /**
     * 发布
     * @return bool
     */
    private function release()
    {
        // 下线小于当前版本的所有版本
        $ret = $this->offlinePreVersion();

        // 发布
        if ($ret && in_array($this->status, array_keys(self::$statusArray))) {
            $this->status = self::STATUS_ONLINE;
            $ret = $this->save();

            if ($ret != true) {
                return false;
            }

            // 更新全局version
            $key = FrequencyVersion::GLOBAL_VERSION_KEY;
            $db = RedisQfe::DB_FREQUENCYCONFIG_FILE;
            $version = $this->project_id. microtime(true);
            $ret = RedisQfe::getInstance()->set($db, $key, $version);

            return $ret;
        }

        return false;
    }

    public function findAllReleasedConfig()
    {
        $online_status = FrequencyVersion::STATUS_ONLINE;
        $all = FrequencyVersion::find()->where(["status" => $online_status])->all();
        $ret = [];
        foreach($all as $k => $row) {
            $key = $row['project_label'];
            $ret[$key] = [
                'data' => json_decode($row['data'], true),
                'version' => $row['version']
            ];
        }

        return $ret;
    }
}
