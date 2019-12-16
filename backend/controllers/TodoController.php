<?php

namespace app\controllers;

use app\models\Todo;
use Yii;
use yii\rest\Controller;

class TodoController extends Controller
{
    private $userId;

    public function beforeAction($action)
    {
        $behaviors = parent::behaviors();
        if (!parent::beforeAction($action)) {
            return false;
        }
        $params = Yii::$app->request->getQueryParams();
        if (array_key_exists('accessToken', $params) && array_key_exists('userId', $params)) {
            $token = $params["accessToken"];
            $this->userId = $params["userId"];
            if ($token && $this->userId && $token == Yii::$app->cache->get('token:' . $this->userId)) {
                $behaviors['corsFilter'] = [
                    'class' => \yii\filters\Cors::className(),
                ];
                return $behaviors;
            }
        }
        throw new \yii\web\UnauthorizedHttpException('未登录');
    }

    public function actionAll()
    {
        # 获取当前用户所有的 Todo
        return Todo::find()->where(['user_id' => $this->userId])->all();
    }

    public function actionCreate()
    {
        $request = Yii::$app->request;
        $todo = new Todo();
        $description = $request->getBodyParam('description');
        $todo->user_id = $this->userId;
        $todo->description = $description;
        $todo->status = true;
        $todo->save();
        return $todo;
    }

    public function actionStarts()
    {
        # 获取正在进行的 Todo
        return Todo::find()->where(['user_id' => $this->userId, 'status' => true])->all();
    }

    public function actionEnds()
    {
        # 获取已经完成的 Todo
        return Todo::find()->where(['user_id' => $this->userId, 'status' => false])->all();
    }

    public function actionClearEnds()
    {
        # 删除已经完成的 Todo
        if (Yii::$app->request->isDelete) {
            Todo::deleteAll(['user_id' => $this->userId, 'status' => false]);
        }
    }

    public function actionChangeOneStatus($todoId)
    {
        if (Yii::$app->request->isPatch) {
            $todo = Todo::findOne(['user_id' => $this->userId, '_id' => $todoId]);
            $todo->status = $todo->status ? false : true;
            $todo->save();
            return $todo;
        }
    }

    public function actionChangeAllStatus()
    {
        $request = Yii::$app->request;
        if ($request->isPatch) {
            $status = $request->getBodyParam('status');
            if ($status == 'true' || $status === true) {
                Todo::updateAll(['status' => true], ['user_id' => $this->userId, 'status' => false]);
            } elseif ($status == 'false' || $status === false) {
                Todo::updateAll(['status' => false], ['user_id' => $this->userId, 'status' => true]);
            }
            return Todo::find()->where(['user_id' => $this->userId])->all();
        }
    }

    public function actionDescription($id)
    {
        $request = Yii::$app->request;
        if ($request->isPatch) {
            $description = $request->getBodyParam('description');
            $todo = Todo::findOne(['user_id' => $this->userId, '_id' => $id]);
            if ($todo) {
                $todo->description = $description;
                $todo->save();
                return $todo;
            }
        }
    }

    public function actionDeleteTodoById($id)
    {
        if (Yii::$app->request->isDelete) {
            $todo = Todo::findOne(['user_id' => $this->userId, '_id' => $id]);
            if ($todo) {
                $todo->delete();
            }
        }
    }
}
