<?php

namespace app\models;

use app\models\base\BaseModel;

use app\config\Vhelper;
use Yii;

/**
 * This is the model class for table "pur_supplier_log".
 *
 * @property integer $id
 * @property integer $user_id
 * @property string $user_name
 * @property string $action
 * @property string $message
 * @property string $time
 */
class SupplierLog extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'pur_supplier_log';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id'], 'integer'],
            [['time'], 'safe'],
            [['user_name', 'action'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'user_name' => 'User Name',
            'action' => 'Action',
            'message' => 'Message',
            'time' => 'Time',
        ];
    }
    //保存操作日志
    public static function saveSupplierLog($action,$message,$bool=false,$supplier_name=false,$supplier_code=false){
        $model = new self;
        $model->user_id = isset(Yii::$app->user->id)?Yii::$app->user->id:0;
        $model->user_name = isset(Yii::$app->user->identity->username)?Yii::$app->user->identity->username:'';
        $model->action  = $action;
        $model->message = $message;
        $model->time    = date('Y-m-d H:i:s',time());
        if (!empty($supplier_name)) {
            $model->supplier_name = $supplier_name;
        }
        if (!empty($supplier_code)) {
            $model->supplier_code = $supplier_code;
        }

        if ($bool) {
            return $model->attributes;
        } else {
            $model->save(false);
        }
    }
    //获取操作日志
    public static function getSupplierLogInfo($supplier_code)
    {
        $supplier_log_info = self::find()->where(['supplier_code'=>$supplier_code])->orderBy('id desc')->all();
        $res_info = [];
        foreach ($supplier_log_info as $v) {
            $res_info[] = [
                'time'=>$v->time,
                'buyer'=>$v->user_name,
                'detail'=>$v->message
            ];
        }
        return $res_info;
    }
}
