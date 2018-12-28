<?php

namespace app\models;

use app\models\base\BaseModel;

use Yii;

/**
 * This is the model class for table "{{%overseas_warehouse_goods_tracking}}".
 *
 * @property string $id
 * @property string $owarehouse_name
 * @property string $sku
 * @property string $purchase_order_no
 * @property string $buyer
 * @property integer $state
 * @property integer $countdown_days
 * @property integer $financial_payment_time
 * @property integer $product_arrival_time
 */
class OverseasWarehouseGoodsTracking extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%overseas_warehouse_goods_tracking}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['state', 'countdown_days', 'financial_payment_time', 'product_arrival_time'], 'integer'],
            [['owarehouse_name', 'sku', 'purchase_order_no', 'buyer'], 'string', 'max' => 50],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'owarehouse_name' => Yii::t('app', '海外仓库名称'),
            'sku' => Yii::t('app', 'SKU'),
            'purchase_order_no' => Yii::t('app', '采购订单'),
            'buyer' => Yii::t('app', '采购员'),
            'state' => Yii::t('app', '状态'),
            'countdown_days' => Yii::t('app', '货物倒计时间(天数)'),
            'financial_payment_time' => Yii::t('app', '财务付款时间'),
            'product_arrival_time' => Yii::t('app', '货物到达时间'),
        ];
    }
}
