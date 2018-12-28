<?php

namespace app\models;

use app\models\base\BaseModel;

use Yii;

/**
 * This is the model class for table "pur_sku_avg_log".
 *
 * @property integer $id
 * @property string $old_last_price
 * @property string $new_last_price
 * @property integer $available_stock
 * @property string $pur_number
 * @property string $create_date
 * @property string $purchase_price
 * @property string $sku
 * @property string $ratio
 * @property string $freight
 * @property string $cty
 */
class SkuAvgLog extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'pur_sku_avg_log';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['old_last_price', 'new_last_price', 'ratio','purchase_price', 'freight'], 'number'],
            [['available_stock','cty'], 'integer'],
            [['create_date'], 'safe'],
            [['pur_number', 'sku'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'old_last_price' => 'Old Last Price',
            'new_last_price' => 'New Last Price',
            'available_stock' => 'Available Stock',
            'pur_number' => 'Pur Number',
            'create_date' => 'Create Date',
            'purchase_price' => 'Purchase Price',
            'sku' => 'Sku',
            'ratio' => 'Ratio',
            'freight' => 'Freight',
            'cty'   =>'Cty'
        ];
    }
}
