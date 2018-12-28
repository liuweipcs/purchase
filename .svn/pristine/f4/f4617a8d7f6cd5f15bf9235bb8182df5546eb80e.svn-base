<?php

namespace app\models;

use app\models\base\BaseModel;

use Yii;

/**
 * This is the model class for table "pur_warehouse_min".
 *
 * @property string $id
 * @property string $warehouse_code
 * @property string $days_safe
 * @property string $days_min
 */
class WarehouseMin extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'pur_warehouse_min';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['warehouse_code'], 'required'],
            [['days_safe', 'days_min'], 'integer'],
            [['warehouse_code'], 'string', 'max' => 30],
            [['warehouse_code', 'days_safe', 'days_min'], 'unique', 'targetAttribute' => ['warehouse_code', 'days_safe', 'days_min'], 'message' => 'The combination of Warehouse Code, Days Safe and Days Min has already been taken.'],
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
            'days_safe' => 'Days Safe',
            'days_min' => 'Days Min',
        ];
    }
}
