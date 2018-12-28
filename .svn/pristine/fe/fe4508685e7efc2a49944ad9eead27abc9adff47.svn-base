<?php

namespace app\models;

use app\models\base\BaseModel;

use Yii;

/**
 * This is the model class for table "{{%lower_rate_statistics}}".
 *
 * @property string $id
 * @property string $buyer
 * @property integer $total_sku
 * @property integer $total_qty
 * @property integer $success_sku
 * @property string $create_time
 * @property integer $arrival_qty
 */
class LowerRateStatistics extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%lower_rate_statistics}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['buyer'], 'required'],
            [['total_sku', 'total_qty', 'success_sku', 'arrival_qty','success_qty','buyer_id'], 'integer'],
            [['create_time','update_time'], 'safe'],
            [['buyer'], 'string', 'max' => 50],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'buyer' => 'Buyer',
            'total_sku' => 'Total Sku',
            'total_qty' => 'Total Qty',
            'success_sku' => 'Success Sku',
            'create_time' => 'Create Time',
            'arrival_qty' => 'Arrival Qty',
            'buyer_id' => 'buyer_id',
            'update_time' => 'update_time',
        ];
    }
}
