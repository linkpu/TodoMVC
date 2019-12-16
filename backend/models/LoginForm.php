<?php

namespace app\models;

use Yii;
use yii\base\Model;

class LoginForm extends Model
{
    public $username;
    public $password;
    public $rememberMe = true;
    private $_user = false;

    public function rules()
    {
        return [
            [['username', 'password'], 'required'],
            ['rememberMe', 'boolean'],
            ['password', 'validatePassword'],
        ];
    }

    public function validatePassword($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $user = $this->getUser();
            if (!$user || !$user->validatePassword($this->password)) {
                $this->addError($attribute, 'Incorrect password.');
            }
        }
    }

    public function login()
    {
        if ($this->validate()) {
            $access_token = User::generateAccessToken();
            $expire = 6 * 3600;
            Yii::$app->cache->set('token:' . $this->_user->_id, $access_token, $expire);
            return ['accessToken' => $access_token, 'userId' => (string) $this->_user->_id, 'expire' => $expire];
        }
        return $this->getErrors();
    }

    public function getUser()
    {
        if ($this->_user === false) {
            $this->_user = User::findOne(['username' => $this->username]);
        }
        return $this->_user;
    }
}
