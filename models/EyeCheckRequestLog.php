<?php

namespace app\models;

use app\models\base\BaseModel;

use Yii;

/**
 * This is the model class for table "pur_eye_check_request_log".
 *
 * @property integer $id
 * @property string $create_time
 * @property string $credit_code
 * @property string $create_user_name
 * @property string $create_user_ip
 * @property integer $status
 * @property string $response_data
 */
class EyeCheckRequestLog extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'pur_eye_check_request_log';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['create_time', 'credit_code', 'create_user_name', 'create_user_ip'], 'required'],
            [['create_time'], 'safe'],
            [['status'], 'integer'],
            [['response_data'], 'string'],
            [['credit_code', 'create_user_name', 'create_user_ip'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'create_time' => 'Create Time',
            'credit_code' => 'Credit Code',
            'create_user_name' => 'Create User Name',
            'create_user_ip' => 'Create User Ip',
            'status' => 'Status',
            'response_data' => 'Response Data',
        ];
    }
}
