<?php
namespace app\models;

use app\models\base\BaseModel;

/**
 * This is the model class for table "pur_purchase_tactics_daily_sales".
 */
class PurchaseTacticsDailySales extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%purchase_tactics_daily_sales}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['tactics_id'],'required'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'tactics_id' => '备货策略ID',
            'day_value' => '销量平均值天数',
            'day_sales' => '销量平均值比值',
        ];
    }
}
