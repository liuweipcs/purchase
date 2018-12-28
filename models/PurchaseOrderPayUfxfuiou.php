<?php

namespace app\models;

use app\models\base\BaseModel;

use Yii;

/**
 * This is the model class for table "pur_purchase_order_pay_ufxfuiou".
 *
 * @property integer $id
 * @property string $requisition_number
 * @property string $pur_tran_num
 * @property string $create_time
 * @property string $create_user_name
 * @property integer $status
 * @property string $update_time
 * @property string $update_user_name
 */
class PurchaseOrderPayUfxfuiou extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'pur_purchase_order_pay_ufxfuiou';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['requisition_number', 'pur_tran_num', 'create_time', 'create_user_name'], 'required'],
            [['create_time', 'update_time'], 'safe'],
            [['status'], 'integer'],
            [['requisition_number', 'pur_tran_num', 'create_user_name', 'update_user_name'], 'string', 'max' => 50],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'requisition_number' => 'Requisition Number',
            'pur_tran_num' => 'Pur Tran Num',
            'create_time' => 'Create Time',
            'create_user_name' => 'Create User Name',
            'status' => 'Status',
            'update_time' => 'Update Time',
            'update_user_name' => 'Update User Name',
        ];
    }

    //保存交易流水号和付款申请单号的绑定关系
    public static function saveBindInfo($payModel,$tranNo){
        $model =new self();
        $model->requisition_number = $payModel->requisition_number;
        $model->pur_tran_num       = $tranNo;
        $model->create_time        = date('Y-m-d H:i:s',time());
        $model->create_user_name   = Yii::$app->user->identity->username;
        $model->status             = 1;
        return $model->save();
    }
}
