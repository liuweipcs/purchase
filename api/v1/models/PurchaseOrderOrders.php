<?php

namespace app\api\v1\models;

use Yii;

/**
 * This is the model class for table "{{%purchase_order_orders}}".
 *
 * @property integer $id
 * @property string $pur_number
 * @property string $order_number
 * @property integer $is_request
 * @property integer $create_id
 */
class PurchaseOrderOrders extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%purchase_order_orders}}';
    }

    /**
     * 关联订单记录
     * @return \yii\db\ActiveQuery
     */
    public  function  getOrders()
    {
        return $this->hasOne(PurchaseOrder::className(),['pur_number'=>'pur_number'])->where(['not in','purchas_status',[1,2,3,4,10]])->orderBy('id asc');
    }

}
