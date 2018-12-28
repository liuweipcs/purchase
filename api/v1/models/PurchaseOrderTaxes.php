<?php

namespace app\api\v1\models;

use Yii;

/**
 * This is the model class for table "pur_purchase_order_taxes".
 *
 * @property integer $id
 * @property string $pur_number
 * @property string $sku
 * @property integer $is_taxes
 * @property string $taxes
 * @property integer $create_id
 * @property string $create_time
 */
class PurchaseOrderTaxes extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'pur_purchase_order_taxes';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['is_taxes', 'create_id'], 'integer'],
            [['create_time'], 'safe'],
            [['pur_number'], 'string', 'max' => 100],
            [['sku'], 'string', 'max' => 50],
            [['taxes'], 'string', 'max' => 30],
            [['pur_number', 'sku'], 'unique', 'targetAttribute' => ['pur_number', 'sku'], 'message' => 'The combination of Pur Number and Sku has already been taken.'],
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
            'is_taxes' => 'Is Taxes',
            'taxes' => 'Taxes',
            'create_id' => 'Create ID',
            'create_time' => 'Create Time',
        ];
    }
}
