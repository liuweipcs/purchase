<?php

namespace app\models;

use app\models\base\BaseModel;

use Yii;

/**
 * This is the model class for table "pur_hwc_avg_delivery_time".
 *
 * @property integer $id
 * @property string $sku
 * @property integer $avg_delivery_time
 * @property string $cacul_date
 * @property integer $status
 * @property integer $delivery_total
 * @property integer $purchase_time
 */
class HwcAvgDeliveryTime extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'pur_hwc_avg_delivery_time';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['avg_delivery_time', 'status', 'delivery_total', 'purchase_time'], 'integer'],
            [['cacul_date'], 'safe'],
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
            'avg_delivery_time' => 'Avg Delivery Time',
            'cacul_date' => 'Cacul Date',
            'status' => 'Status',
            'delivery_total' => 'Delivery Total',
            'purchase_time' => 'Purchase Time',
        ];
    }
}
