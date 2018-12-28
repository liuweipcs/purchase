<?php

namespace app\models;

use app\models\base\BaseModel;

use Yii;

/**
 * This is the model class for table "pur_warehouse_purchase_tactics".
 *
 * @property string $id
 * @property string $warehouse_code
 * @property string $type
 * @property string $days_product
 * @property string $days_logistics
 * @property string $days_safe_stock
 * @property string $days_frequency_purchase
 *
 * @property Warehouse $warehouseCode
 */
class WarehousePurchaseTactics extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'pur_warehouse_purchase_tactics';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['warehouse_code', 'type'], 'required'],
            [['type'], 'string'],
            [['days_product', 'days_logistics', 'days_safe_stock', 'days_frequency_purchase'], 'integer'],
            [['warehouse_code'], 'string', 'max' => 30],
            [['warehouse_code', 'type'], 'unique', 'targetAttribute' => ['warehouse_code', 'type'], 'message' => 'The combination of Warehouse Code and Type has already been taken.'],
            [['warehouse_code'], 'exist', 'skipOnError' => true, 'targetClass' => Warehouse::className(), 'targetAttribute' => ['warehouse_code' => 'warehouse_code']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'warehouse_code' => 'Warehouse Code',
            'type' => 'Type',
            'days_product' => 'Days Product',
            'days_logistics' => 'Days Logistics',
            'days_safe_stock' => 'Days Safe Stock',
            'days_frequency_purchase' => 'Days Frequency Purchase',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getWarehouseCode()
    {
        return $this->hasOne(Warehouse::className(), ['warehouse_code' => 'warehouse_code']);
    }
}
