<?php

namespace app\models;

use Constant;
use Yii;
use yii\helpers\ArrayHelper;
use yii\web\IdentityInterface;

/**
 * This is the model class for table "user".
 * if auth method use local, these properties should be added: $password, $auth_key, $access_token
 *
 * @property integer $id
 * @property string $email
 * @property string $name
 * @property string $phone
 * @property integer $status
 * @property integer $is_admin
 * @property integer $role_id
 * @property string $password
 * @property string $auth_key
 * @property string $access_token
 */
class BizUser extends BaseActiveRecord implements IdentityInterface
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['status', 'is_admin', 'name', 'email'], 'required'],
            [['status', 'is_admin', 'role_id'], 'integer'],
            [['email'], 'string', 'max' => 64],
            [['name', 'access_token', 'auth_key'], 'string', 'max' => 40],
            [['password'], 'string', 'max' => 100],
            [['phone'], 'string', 'max' => 20],
            [['create_time', 'update_time'], 'safe'],

        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'email' => '邮箱',
            'name' => '名称',
            'phone' => '联系电话',
            'status' => '状态',
            'is_admin' => '是否是管理员',
            'create_time' => '创建时间',
            'update_time' => '更新时间',
            'role_id' => '角色',
            "password" => "密码",
        ];
    }

    /**
     * Finds an identity by the given ID.
     * @param string|int $id the ID to be looked for
     * @return IdentityInterface the identity object that matches the given ID.
     * Null should be returned if such an identity cannot be found
     * or the identity is not in an active state (disabled, deleted, etc.)
     */
    public static function findIdentity($id)
    {
        //var_dump(static::findOne($id));
        return static::findOne($id);
    }

    /**
     * Finds an identity by the given token.
     * @param mixed $token the token to be looked for
     * @param mixed $type the type of the token. The value of this parameter depends on the implementation.
     * For example, [[\yii\filters\auth\HttpBearerAuth]] will set this parameter to be `yii\filters\auth\HttpBearerAuth`.
     * @return IdentityInterface the identity object that matches the given token.
     * Null should be returned if such an identity cannot be found
     * or the identity is not in an active state (disabled, deleted, etc.)
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        return static::findOne(["access_token" => $token]);
    }

    /**
     * Finds user by username
     *
     * @param string $username
     * @return static|null
     */
    public static function findByUsername($username)
    {
        return static::findOne(["name" => $username]);

    }

    /**
     * Returns an ID that can uniquely identify a user identity.
     * @return string|int an ID that uniquely identifies a user identity.
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Returns a key that can be used to check the validity of a given identity ID.
     *
     * The key should be unique for each individual user, and should be persistent
     * so that it can be used to check the validity of the user identity.
     *
     * The space of such keys should be big enough to defeat potential identity attacks.
     *
     * This is required if [[User::enableAutoLogin]] is enabled.
     * @return string a key that is used to check the validity of a given identity ID.
     * @see validateAuthKey()
     */
    public function getAuthKey()
    {
        return $this->auth_key;
    }

    /**
     * Validates the given auth key.
     *
     * This is required if [[User::enableAutoLogin]] is enabled.
     * @param string $authKey the given auth key
     * @return bool whether the given auth key is valid.
     * @see getAuthKey()
     */
    public function validateAuthKey($authKey)
    {
        return $this->auth_key === $authKey;
    }

    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return bool if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        return \Utils::hashValidate($password, $this->password);
    }

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

    public function canAccess($controller_id, $action_id)
    {
        if ($this->status != Constant::VALID) {
            return false;
        }

        if ($this->is_admin) {
            return true;
        }

        if (!isset(Constant::$ROLE_CONTROLLER[$this->role_id])) {
            return false;
        }
        $routes = Constant::$ROLE_CONTROLLER[$this->role_id];
        if (in_array($controller_id, $routes)) {
            return true;
        }

        if (in_array("{$controller_id}/{$action_id}", $routes)) {
            return true;
        }
        return false;

    }

}
