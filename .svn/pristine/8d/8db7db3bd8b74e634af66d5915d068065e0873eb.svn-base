<?php

namespace app\models;

use app\models\base\BaseModel;

use Yii;

/**
 * This is the model class for table "pur_warehouse_qty_tactics".
 *
 * @property string $id
 * @property string $warehouse_code
 * @property string $qty_from
 * @property string $qty_to
 * @property double $rate
 *
 * @property Warehouse $warehouseCode
 */
class WarehouseQtyTactics extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'pur_warehouse_qty_tactics';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['warehouse_code'], 'required'],
            [['qty_from', 'qty_to'], 'integer'],
            [['rate'], 'number'],
            [['warehouse_code'], 'string', 'max' => 30],
            [['warehouse_code', 'qty_from', 'qty_to'], 'unique', 'targetAttribute' => ['warehouse_code', 'qty_from', 'qty_to'], 'message' => 'The combination of Warehouse Code, Qty From and Qty To has already been taken.'],
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
            'qty_from' => 'Qty From',
            'qty_to' => 'Qty To',
            'rate' => 'Rate',
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
