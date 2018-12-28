<?php

namespace app\api\v1\models;

use Yii;

/**
 * This is the model class for table "{{%purchase_order_items}}".
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
 *
 * @property PurchaseOrder $purNumber
 */
class PurchaseOrderItems extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%purchase_order_items}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['pur_number', 'sku', 'name', 'qty'], 'required'],
            [['qty', 'ctq', 'rqy', 'cty', 'sales_status'], 'integer'],
            [['price'], 'number'],
            [['pur_number'], 'string', 'max' => 20],
            [['sku'], 'string', 'max' => 30],
            [['name'], 'string', 'max' => 300],
            [['pur_number', 'sku'], 'unique', 'targetAttribute' => ['pur_number', 'sku'], 'message' => 'The combination of Pur Number and Sku has already been taken.'],
            [['pur_number'], 'exist', 'skipOnError' => true, 'targetClass' => PurchaseOrder::className(), 'targetAttribute' => ['pur_number' => 'pur_number']],
        ];
    }


    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPurNumber()
    {
        return $this->hasOne(PurchaseOrder::className(), ['pur_number' => 'pur_number']);
    }

    /**
     * 根据采购单号和sku获取价格
     * @param $pur_number
     * @param $sku
     * @return false|null|string
     */
    public static  function getPrice($pur_number,$sku)
    {
        $price =self::find()->select('price')->where(['pur_number'=>$pur_number,'sku'=>$sku])->scalar();
        if($price)
        {
            return $price;
        } else {
            return '0.00';
        }
    }

    /**
     * @param $pur_number
     * @param $sku
     * @return false|null|string
     */
    public static  function  getProductName($pur_number,$sku)
    {
        $name =self::find()->select('name')->where(['pur_number'=>$pur_number,'sku'=>$sku])->scalar();
        if($name)
        {
            return $name;
        } else {
            return '无';
        }
    }

}
