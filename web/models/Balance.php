<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "balance".
 *
 * @property integer $id
 * @property string $name
 * @property integer $project_id
 * @property integer $location
 * @property string $vip
 * @property integer $qfe_idc
 * @property integer $weight
 */
class Balance extends BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'balance';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['project_id', 'weight'], 'integer'],
            [['location', 'qfe_idc'], 'string', 'max' => 10],
            [['vip'], 'string', 'max' => 20],
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
            'location' => '机房',
            'vip' => 'VIP',
            'weight' => '权重',
        ];
    }
}
