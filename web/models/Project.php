<?php

namespace app\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "project".
 *
 * @property integer $id
 * @property string $name
 * @property string $label
 * @property string $host
 * @property integer $user_id
 * @property BizUser $bizUser
 * @property string contact_email
 * @property RelPorjCert[] $relProjCerts
 * @property ProjHost[] $projHosts
 * @property Balance[] $balances
 */
class Project extends BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'project';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id'], 'required'],
            [['user_id'], 'integer'],
            [['create_time', 'update_time'], 'safe'],
            [['name'], 'string', 'max' => 128],
            [['label'], 'string', 'max' => 64],
            [['label'], 'unique'],
            [['name'], 'unique'],
            [['contact_email'], 'email'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => '业务名',
            'label' => '唯一标识',
            'user_id' => '创建者',
            'contact_email' => '联系人邮箱',
            'create_time' => '创建时间',
            'update_time' => '修改时间',
        ];
    }

    public function getBizUser()
    {
        return $this->hasOne(BizUser::class, ['id' => 'user_id']);
    }

    public function getRelProjCerts()
    {
        return $this->hasMany(RelPorjCert::class, ['project_id' => 'id'])->andOnCondition([
            "status"=> \Constant::VALID
        ]);
    }

    public function getProjHosts()
    {
        return $this->hasMany(ProjHost::class, ['project_id' => 'id']);
    }

    public function getBalances()
    {
        return $this->hasMany(Balance::class, ['project_id' => 'id']);
    }


    /**
     * 当前项目的id=> name 字典
     * @param null $status
     * @return array
     */
    public static function validAsDict($status = null)
    {
        $query = self::find();

        if ($status !== null) {
            $query->where(["status" => $status]);
        }

        $models = $query->all();
        $dict = ArrayHelper::map($models, 'id', 'name');
        return $dict;
    }

    /**
     * 当前项目的label列表
     * @param null $status
     * @return array
     */
    public static function validAsLabelList($status = null)
    {
        $query = self::find();

        if ($status !== null) {
            $query->where(["status" => $status]);
        }
        $models = $query->all();
        $list = ArrayHelper::getColumn($models, 'label');
        return $list;
    }

}
