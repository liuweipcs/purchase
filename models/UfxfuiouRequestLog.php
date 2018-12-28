<?php

namespace app\models;

use app\models\base\BaseModel;

use Yii;

/**
 * This is the model class for table "pur_ufxfuiou_request_log".
 *
 * @property integer $id
 * @property string $pur_tran_no
 * @property string $create_time
 * @property string $create_user_name
 * @property string $request_response
 * @property string $post_params
 * @property integer $type
 */
class UfxfuiouRequestLog extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'pur_ufxfuiou_request_log';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['pur_tran_no', 'create_time', 'create_user_name', 'request_response', 'post_params'], 'required'],
            [['create_time'], 'safe'],
            [['request_response', 'post_params'], 'string'],
            [['pur_tran_no', 'create_user_name'], 'string', 'max' => 50],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'pur_tran_no' => 'Pur Tran No',
            'create_time' => 'Create Time',
            'create_user_name' => 'Create User Name',
            'request_response' => 'Request Response',
            'post_params' => 'Post Params',
            'type' => 'Type',
        ];
    }

    public static function saveRequestLog($tranNo,$requestStr,$response){
        Yii::$app->db->createCommand()->insert(self::tableName(),[
            'pur_tran_no'=>$tranNo,
            'create_time'=>date('Y-m-d H:i:s',time()),
            'create_user_name'=>Yii::$app->user->isGuest ? 1 :Yii::$app->user->identity->username,
            'request_response'=>$response,
            'post_params'=>$requestStr
        ])->execute();
    }
}
