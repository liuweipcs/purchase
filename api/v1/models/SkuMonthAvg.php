<?php

namespace app\api\v1\models;

use Yii;

/**
 * This is the model class for table "pur_sku_month_avg".
 *
 * @property integer $id
 * @property string $sku
 * @property string $month
 * @property string $month_avg_purchase
 * @property string $month_avg_freight
 * @property string $cacu_date
 */
class SkuMonthAvg extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'pur_sku_month_avg';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['month', 'cacu_date'], 'safe'],
            [['month_avg_purchase', 'month_avg_freight'], 'number'],
            [['sku'], 'string', 'max' => 255],
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
            'month' => 'Month',
            'month_avg_purchase' => 'Month Avg Purchase',
            'month_avg_freight' => 'Month Avg Freight',
            'cacu_date' => 'Cacu Date',
        ];
    }
}
