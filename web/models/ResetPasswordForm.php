<?php
/**
 * Created by PhpStorm.
 * User: zhangshuang
 * Date: 2019/7/30
 * Time: 4:56 PM
 */

namespace app\models;


use yii\base\InvalidArgumentException;
use yii\base\Model;

class ResetPasswordForm extends Model
{
    public $oldPassword;

    public $newPassword;

    public $confirmPassword;


    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            [['newPassword', 'oldPassword', 'confirmPassword'], 'required', 'skipOnEmpty' => false, 'skipOnError' => false],
            ['oldPassword', 'validatePassword'],
            [['newPassword', 'confirmPassword', 'oldPassword'], 'validateMatch', 'skipOnEmpty' => false, 'skipOnError' => false],
        ];
    }

    public function validateMatch($attribute, $params)
    {
        if (!$this->hasErrors()) {
            if ($this->oldPassword == $this->newPassword) {
                $this->addError($attribute, 'new password must different from the old');
            }

            if ($this->newPassword != $this->confirmPassword) {
                $this->addError($attribute, 'confirm password must same as the new one');
            }
        }

    }


    /**
     * Validates the password.
     * This method serves as the inline validation for password.
     *
     * @param string $attribute the attribute currently being validated
     * @param array $params the additional name-value pairs given in the rule
     */
    public function validatePassword($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $user = Context::getInstance()->bizUser();

            if (!$user || !$user->validatePassword($this->oldPassword)) {
                $this->addError($attribute, 'Incorrect password');
            }
        }
    }

    public function reset()
    {
        $user = \Yii::$app->user->identity;
        $user->password = \Utils::hashPassword($this->newPassword);
        return $user->save();
    }


}