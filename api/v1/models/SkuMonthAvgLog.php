<?php

namespace app\api\v1\models;

use Yii;

/**
 * This is the model class for table "pur_sku_month_avg_log".
 *
 * @property integer $id
 * @property string $sku
 * @property string $last_month_purchase_avg
 * @property string $this_month_purchase_avg
 * @property string $last_month_freight_avg
 * @property string $this_month_freight_avg
 * @property integer $stock
 * @property string $freight
 * @property string $purchase_cost
 * @property integer $purchase_num
 * @property string $calc_date
 * @property string $create_date
 */
class SkuMonthAvgLog extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'pur_sku_month_avg_log';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id'], 'required'],
            [['id', 'stock', 'purchase_num'], 'integer'],
            [['last_month_purchase_avg', 'this_month_purchase_avg', 'last_month_freight_avg', 'this_month_freight_avg', 'freight', 'purchase_cost'], 'number'],
            [['calc_date', 'create_date'], 'safe'],
            [['sku'], 'string', 'max' => 100],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'sku' => 'Sku',
            'last_month_purchase_avg' => 'Last Month Purchase Avg',
            'this_month_purchase_avg' => 'This Month Purchase Avg',
            'last_month_freight_avg' => 'Last Month Freight Avg',
            'this_month_freight_avg' => 'This Month Freight Avg',
            'stock' => 'Stock',
            'freight' => 'Freight',
            'purchase_cost' => 'Purchase Cost',
            'purchase_num' => 'Purchase Num',
            'calc_date' => 'Calc Date',
            'create_date' => 'Create Date',
        ];
    }
}
