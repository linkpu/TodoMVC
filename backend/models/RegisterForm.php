<?php

namespace app\models;

use yii\base\Model;

class RegisterForm extends Model
{
    public $username;
    public $password;
    public $rememberMe = false;
    private $_user = false;

    public function rules()
    {
        return [
            [['username', 'password'], 'required'],
            ['rememberMe', 'boolean'],
            ['username', 'validateUsername'],
            ['password', 'validatePassword'],
        ];
    }

    public function validateUsername($username, $params)
    {
        if (!$this->hasErrors()) {
            if (!preg_match('/^[a-zA-Z0-9_-]{4,16}$/u', $this->username)) {
                $this->addError($username, 'Invalid');
            } elseif ($this->getUser()) {
                $this->addError($username, 'Already exists.');
            }
        }
    }

    public function validatePassword($password, $params)
    {
        if (!$this->hasErrors()) {
            if (!preg_match('/^[a-zA-Z0-9_!.@#$%^&*?]{4,16}$/u', $this->password)) {
                $this->addError($password, 'Invalid.');
            }
        }
    }

    public function register()
    {
        if ($this->validate()) {
            return $this->createUser();
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

    public function createUser()
    {
        $user = new User();
        $user->username = $this->username;
        $user->setPassword($this->password);
        $user->insert();
        return $user->toArray();
    }
}
