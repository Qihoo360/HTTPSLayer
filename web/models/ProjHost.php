<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "proj_host".
 *
 * @property integer $id
 * @property string $name
 * @property integer $project_id
 */
class ProjHost extends \app\models\BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'proj_host';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'string', 'max' => 64],
            [['project_id'], 'integer'],
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
            'project_id'=> '业务',
        ];
    }
}
