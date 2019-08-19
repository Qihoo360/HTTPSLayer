<?php
/**
 * Created by IntelliJ IDEA.
 * User: shenjiangxin
 * Date: 2019/5/15
 * Time: 14:06
 */

namespace app\models;

use Yii;

/**
 * Oauth 2.0
 * Class Auth
 * @package app\models
 * @property string $source
 * @property int $source_id;
 * @property int $user_id;
 * @property BizUser $bizUser;
 */
class Auth extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'auth';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id'], 'integer'],
            [['source', 'source_id'], 'string', 'max' => 64],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => '用户id',
            'source' => '提供商名称',
            'source_id' => '外部userid',
        ];
    }

    public function getBizUser()
    {
        return BizUser::findOne($this->user_id);
    }
}
