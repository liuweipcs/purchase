<?php

namespace app\models;

use app\models\base\BaseModel;

use Yii;

/**
 * This is the model class for table "{{%purchase_order_items_bak}}".
 *
 * @property integer $id
 * @property string $pur_number
 * @property string $sku
 * @property string $name
 * @property integer $qty
 * @property string $price
 * @property integer $ctq
 * @property integer $rqy
 * @property integer $cty
 * @property integer $sales_status
 * @property string $product_img
 * @property integer $order_id
 * @property integer $is_exemption
 * @property string $items_totalprice
 * @property string $product_link
 * @property integer $e_ctq
 * @property string $e_price
 *
 * @property PurchaseOrder $purNumber
 */
class PurchaseOrderItemsBak extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%purchase_order_items_bak}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['pur_number', 'sku', 'name', 'qty'], 'required'],
            [['qty', 'ctq', 'rqy', 'cty', 'sales_status', 'order_id', 'is_exemption', 'e_ctq'], 'integer'],
            [['price', 'items_totalprice', 'e_price'], 'number'],
            [['pur_number'], 'string', 'max' => 20],
            [['sku'], 'string', 'max' => 30],
            [['name'], 'string', 'max' => 300],
            [['product_img'], 'string', 'max' => 2000],
            [['product_link'], 'string', 'max' => 500],
            [['pur_number', 'sku'], 'unique', 'targetAttribute' => ['pur_number', 'sku'], 'message' => 'The combination of 采购单号 and 产品SKU has already been taken.'],
            [['pur_number'], 'exist', 'skipOnError' => true, 'targetClass' => PurchaseOrder::className(), 'targetAttribute' => ['pur_number' => 'pur_number']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'pur_number' => Yii::t('app', '采购单号'),
            'sku' => Yii::t('app', '产品SKU'),
            'name' => Yii::t('app', '产品名称'),
            'qty' => Yii::t('app', '预期数量'),
            'price' => Yii::t('app', '单价'),
            'ctq' => Yii::t('app', '确认数量'),
            'rqy' => Yii::t('app', '收货数量'),
            'cty' => Yii::t('app', '上架数量'),
            'sales_status' => Yii::t('app', '销售状态'),
            'product_img' => Yii::t('app', '产品图片'),
            'order_id' => Yii::t('app', '订单ID'),
            'is_exemption' => Yii::t('app', '是否免检'),
            'items_totalprice' => Yii::t('app', '单条sku的总金额'),
            'product_link' => Yii::t('app', 'Product Link'),
            'e_ctq' => Yii::t('app', '确认数量-对比用'),
            'e_price' => Yii::t('app', '单价-对比用'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPurNumber()
    {
        return $this->hasOne(PurchaseOrder::className(), ['pur_number' => 'pur_number']);
    }
}
