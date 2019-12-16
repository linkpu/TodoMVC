<?php

namespace app\models;

use yii\mongodb\ActiveRecord;

class Todo extends ActiveRecord
{

    public static function collectionName()
    {
        return ['todomvc', 'todo'];
    }

    public function attributes()
    {
        return [
            '_id',
            'user_id',
            'description',
            'status',
        ];
    }
}
