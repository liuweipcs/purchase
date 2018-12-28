<?php

namespace app\models;

use Yii;

/**
 * @property string $id
 * @property string $pur_number
 * @property string $freight
 * @property string $discount
 */
class PurchaseOrderBreakageMain extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%purchase_order_breakage_main}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['freight', 'discount'], 'number'],
            [['pur_number'], 'string', 'max' => 100],
            [['pur_number'], 'unique'],
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
            'freight' => 'Freight',
            'discount' => 'Discount',
        ];
    }
    /**
     * 保存数据
     */
    public static function SaveOne($pur_number)
    {
        $model = self::find()->where(['pur_number'=>$pur_number])->one();
        if (empty($model)) $model = new self();
        $type_data = PurchaseOrderPayType::getDiscountPrice($pur_number);
        $model->pur_number = $pur_number;
        $model->freight = $type_data?$type_data['freight']:0; //运费
        $model->discount = $type_data?$type_data['discount']:0; //优惠额
        $res = $model->save();
        return $res;
    }
}
