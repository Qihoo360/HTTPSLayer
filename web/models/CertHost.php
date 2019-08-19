<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "cert_host".
 *
 * @property integer $id
 * @property string $name
 * @property integer $certificate_id
 */
class CertHost extends \app\models\BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'cert_host';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'string', 'max' => 64],
            [['certificate_id'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => '域名',
            'certificate_id' => '证书',
        ];
    }
}
