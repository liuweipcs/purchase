<?php

namespace app\api\v1\models;

use Yii;

/**
 * This is the model class for table "pur_supplier_kpi_caculte".
 *
 * @property integer $id
 * @property string $supplier_code
 * @property string $month
 * @property integer $settlement
 * @property integer $purchase_times
 * @property string $purchase_total
 * @property integer $sku_purchase_times
 * @property integer $sku_punctual_times
 * @property integer $sku_exception_times
 * @property integer $sku_overseas_exception
 * @property string $sku_up_total
 * @property string $sku_down_rate
 * @property string $sku_up_rate
 * @property string $cacul_date
 * @property string $sku_down_total
 * @property string $punctual_rate
 * @property string $excep_rate
 */
class SupplierKpiCaculte extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'pur_supplier_kpi_caculte';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['supplier_code'], 'required'],
            [['month', 'cacul_date'], 'safe'],
            [['settlement', 'purchase_times', 'sku_purchase_times', 'sku_punctual_times', 'sku_exception_times', 'sku_overseas_exception'], 'integer'],
            [['purchase_total', 'sku_up_total', 'sku_down_rate', 'sku_up_rate', 'sku_down_total','excep_rate','punctual_rate'], 'number'],
            [['supplier_code'], 'string', 'max' => 100],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'supplier_code' => 'Supplier Code',
            'month' => 'Month',
            'settlement' => 'Settlement',
            'purchase_times' => 'Purchase Times',
            'purchase_total' => 'Purchase Total',
            'sku_purchase_times' => 'Sku Purchase Times',
            'sku_punctual_times' => 'Sku Punctual Times',
            'sku_exception_times' => 'Sku Exception Times',
            'sku_overseas_exception' => 'Sku Overseas Exception',
            'sku_up_total' => 'Sku Up Total',
            'sku_down_rate' => 'Sku Down Rate',
            'sku_up_rate' => 'Sku Up Rate',
            'cacul_date' => 'Cacul Date',
            'sku_down_total' => 'Sku Down Total',
        ];
    }
}
