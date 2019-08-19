<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "rel_proj_cert".
 *
 * @property integer $id
 * @property integer $project_id
 * @property integer $certificate_id
 * @property integer $status
 * @property Project $project
 * @property Certificate $certificate
 */
class RelPorjCert extends BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'rel_proj_cert';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['project_id', 'certificate_id'], 'required'],
            [['project_id', 'certificate_id'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'project_id' => '项目',
            'certificate_id' => '证书',
        ];
    }

    public function getProject()
    {
        return $this->hasOne(Project::className(), ['id' => 'project_id']);
    }

    public function getCertificate()
    {
        return $this->hasOne(Certificate::className(), ['id' => 'certificate_id']);
    }
}
