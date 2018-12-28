<?php

namespace app\api\v1\models;

use Yii;

/**
 * This is the model class for table "pur_ali_order_log".
 *
 * @property integer $id
 * @property string $pur_number
 * @property string $order_number
 * @property string $error_code
 * @property string $error_message
 * @property string $create_date
 * @property string $status
 */
class AliOrderLog extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'pur_ali_order_log';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['create_date'], 'safe'],
            [['pur_number'], 'string', 'max' => 50],
            [['order_number', 'error_code'], 'string', 'max' => 100],
            [['status'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'pur_number' => 'Pur Number',
            'order_number' => 'Order Number',
            'error_code' => 'Error Code',
            'error_message' => 'Error Message',
            'create_date' => 'Create Date',
            'status' => 'Status',
        ];
    }

    public static function saveSuccessLog($logDatas){
        Yii::$app->db->createCommand()->insert(self::tableName(),
            [
                'pur_number'=>isset($logDatas['pur_number']) ? $logDatas['pur_number'] : '',
                'order_number'=>isset($logDatas['order_number']) ? $logDatas['order_number'] : '',
                'error_code'=>isset($logDatas['error_code']) ? $logDatas['error_code'] : '',
                'error_message'=>isset($logDatas['message']) ? $logDatas['message'] :'',
                'create_date'=>date('Y-m-d H:i:s',time()),
                'status'=>'success',
            ])->execute();
    }
}
