<?php

namespace app\models;

use app\models\base\BaseModel;
use app\config\Vhelper;
use Yii;

/**
 * This is the model class for table "{{%admin_log}}".
 *
 * @property integer $id
 * @property string $route
 * @property string $description
 * @property integer $created_at
 * @property integer $user_id
 */
class AdminLog extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%admin_log}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['description','user_name'], 'string'],
            [['created_at'], 'required'],
            [['created_at'], 'integer'],
            [['route'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'route' => Yii::t('app', 'Route'),
            'description' => Yii::t('app', 'Description'),
            'created_at' => Yii::t('app', 'Created At'),
            'user_id' => Yii::t('app', 'User ID'),
        ];
    }
    /**
     * @param $action Action
     */
    public static function addLog($action)
    {
        $model = new self();
        $model->route = Yii::$app->request->absoluteUrl;
        $model->description = json_encode(Yii::$app->request->get());
        $model->created_at = time();
        $model->user_name = isset(Yii::$app->user->identity->username)?Yii::$app->user->identity->username:'admin';
        $model->save(false);
    }
}
