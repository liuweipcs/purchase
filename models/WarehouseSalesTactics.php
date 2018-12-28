<?php

namespace app\models;

use app\models\base\BaseModel;

use Yii;

/**
 * This is the model class for table "pur_warehouse_sales_tactics".
 *
 * @property string $id
 * @property string $warehouse_code
 * @property string $date_from
 * @property string $date_to
 * @property double $rate
 *
 * @property Warehouse $warehouseCode
 */
class WarehouseSalesTactics extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'pur_warehouse_sales_tactics';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['warehouse_code', 'date_from', 'date_to'], 'required'],
            [['date_from', 'date_to'], 'safe'],
            [['rate'], 'number'],
            [['warehouse_code'], 'string', 'max' => 30],
            [['warehouse_code', 'date_from', 'date_to'], 'unique', 'targetAttribute' => ['warehouse_code', 'date_from', 'date_to'], 'message' => 'The combination of Warehouse Code, Date From and Date To has already been taken.'],
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
            'date_from' => 'Date From',
            'date_to' => 'Date To',
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
