<?php

namespace app\api\v1\models;

use Yii;

/**
 * This is the model class for table "pur_inform_message".
 *
 * @property integer $id
 * @property string $pur_number
 * @property string $sku
 * @property integer $history_intock
 * @property integer $purchase_total
 * @property string $message
 * @property integer $status
 * @property string $create_time
 * @property string $confirm_time
 * @property string $inform_user
 * @property integer $intock_num
 * @property integer $excep_num
 * @property integer $normal_num
 */
class InformMessage extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'pur_inform_message';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['history_intock', 'purchase_total', 'status', 'intock_num', 'excep_num', 'normal_num'], 'integer'],
            [['create_time', 'confirm_time'], 'safe'],
            [['pur_number', 'sku'], 'string', 'max' => 50],
            [['message', 'inform_user'], 'string', 'max' => 255],
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
            'sku' => 'Sku',
            'history_intock' => 'History Intock',
            'purchase_total' => 'Purchase Total',
            'message' => 'Message',
            'status' => 'Status',
            'create_time' => 'Create Time',
            'confirm_time' => 'Confirm Time',
            'inform_user' => 'Inform User',
            'intock_num' => 'Intock Num',
            'excep_num' => 'Excep Num',
            'normal_num' => 'Normal Num',
        ];
    }

    public static function saveMessage($data){
        $insert= self::verifData($data);
        if($insert['status']==true){
            $buyer          = PurchaseOrder::find()->select('buyer')->where(['pur_number'=>$data['pur_number']])->scalar();
            $model = new self();
            $model->pur_number = $data['pur_number'];
            $model->sku        = $data['sku'];
            $model->history_intock = $insert['history'];
            $model->purchase_total = $data['purchase_quantity'];
            $model->message        = $data['sku'].'入库数量不足，请跟进';
            $model->status         = 0;
            $model->create_time    = date('Y-m-d H:i:s',time());
            $model->inform_user    = $buyer;
            $model->intock_num     = $insert['instock'];
            $model->excep_num      = $data['nogoods'];
            $model->normal_num     = $data['instock_qty_count'];
            $model->save();
        }
    }
    public static function verifData($data){
        $historyInstock = WarehouseResults::find()->where(['pur_number'=>$data['pur_number'],'sku'=>$data['sku']])->one();
        $history = !empty($historyInstock) ? $historyInstock->instock_qty_count :0;
        $intockNum = $data['instock_qty_count']-$history ;
        if($data['instock_qty_count']<$data['purchase_quantity']&&$data['instock_qty_count']>0&&$intockNum>0){
            return ['status'=>true,'instock'=>$intockNum,'history'=>$history];
        }
        return ['status'=>false];
    }
}
