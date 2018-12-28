<?php

namespace app\api\v1\models;

use Yii;

/**
 * This is the model class for table "pur_sku_statistics_info".
 *
 * @property integer $id
 * @property string $sku
 * @property integer $purchase_num
 * @property string $create_time
 */
class SkuStatisticsInfo extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'pur_sku_statistics_info';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['sku', 'create_time'], 'required'],
            [['purchase_num'], 'integer'],
            [['create_time'], 'safe'],
            [['sku'], 'string', 'max' => 50],
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
            'purchase_num' => 'Purchase Num',
            'create_time' => 'Create Time',
        ];
    }
}
