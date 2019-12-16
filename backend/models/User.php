<?php

namespace app\models;

use Yii;
use yii\mongodb\ActiveRecord;


class User extends ActiveRecord
{

    public static function collectionName()
    {
        return ['todomvc', 'user'];
    }

    public function attributes()
    {
        return [
            '_id',
            'username',
            'password_hash',
        ];
    }

    public static function findByUsername($username)
    {
        return static::findOne(['username' => $username]);
    }

    public static function findById($userId)
    {
        return static::findOne(['_id' => $userId]);
    }

    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    public function setPassword($password)
    {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
    }

    public static function generateAccessToken()
    {
        return Yii::$app->security->generateRandomString();
    }
}
