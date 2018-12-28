<?php

namespace app\models;

use app\models\base\BaseModel;

use Yii;

/**
 * This is the model class for table "{{%purchase_order_items_cost_calculate}}".
 *
 * @property integer $id
 * @property integer $check_id
 * @property integer $is_check
 * @property string $check_date
 * @property integer $is_jisuan
 * @property string $jisuan_update_time
 * @property integer $is_transfer
 * @property integer $is_end
 * @property string $warehouse_code
 */
class PurchaseOrderItemsCostCalculate extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%purchase_order_items_cost_calculate}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['check_id', 'is_check'], 'required'],
            [['check_id', 'is_check', 'is_jisuan', 'is_transfer', 'is_end'], 'integer'],
            [['jisuan_update_time'], 'safe'],
            [['check_date'], 'string', 'max' => 20],
            [['warehouse_code'], 'string', 'max' => 100],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'check_id' => Yii::t('app', 'Check ID'),
            'is_check' => Yii::t('app', '是否检查'),
            'check_date' => Yii::t('app', 'Check Date'),
            'is_jisuan' => Yii::t('app', '是否计算当天的平均成本'),
            'jisuan_update_time' => Yii::t('app', 'Jisuan Update Time'),
            'is_transfer' => Yii::t('app', '是否转移到每天的成本表中'),
            'is_end' => Yii::t('app', '是否全部到货'),
            'warehouse_code' => Yii::t('app', '仓库编码'),
        ];
    }
}
