<?php

namespace app\api\v1\models;

use Yii;

/**
 * This is the model class for table "pur_fba_avg_deliery_time".
 *
 * @property integer $id
 * @property string $sku
 * @property string $avg_delivery_time
 * @property string $update_time
 */
class FbaAvgDelieryTime extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'pur_fba_avg_deliery_time';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['sku', 'update_time'], 'required'],
            [['avg_delivery_time'], 'number'],
            [['update_time'], 'safe'],
            [['sku'], 'string', 'max' => 150],
            [['sku'], 'unique'],
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
            'update_time' => 'Update Time',
        ];
    }
}
