<?php

namespace app\controllers;

use Yii;
use yii\rest\Controller;
use app\models\LoginForm;
use app\models\RegisterForm;

class SiteController extends Controller
{
    public $modelClass = 'app\models\User';

    protected function verbs()
    {
        $verbs = parent::verbs();
        return $verbs;
    }

    public function actionLogin()
    {
        $loginForm = new LoginForm();
        $loginForm->load([$loginForm->formName() => yii::$app->request->post()]);
        return $loginForm->login();
    }

    public function actionRegister()
    {
        $registerForm = new RegisterForm();
        $registerForm->load([$registerForm->formName() => yii::$app->request->post()]);
        return $registerForm->register();
    }

    public function actionLogout()
    {
        $params = Yii::$app->request->getQueryParams();
        if (array_key_exists('accessToken', $params) && array_key_exists('userId', $params)){
            $token = $params["accessToken"];
            $userId = $params["userId"];
            if ($token && $userId && $token == Yii::$app->cache->get('token:' . $userId)) {
                Yii::$app->cache->set('token:' . $userId, '');
                return ['accessToken' => "", 'userId' => "", 'expire' => -1];
            }
        }
        throw new \yii\web\UnauthorizedHttpException('未登录');
    }
}
