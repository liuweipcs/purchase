<?php

namespace app\models;

use app\models\base\BaseModel;

use Yii;

/**
 * This is the model class for table "{{%sku_single_tactic_main_content}}".
 *
 * @property integer $id
 * @property integer $produce_days
 * @property integer $transport_days
 * @property integer $safe_stock_days
 * @property integer $resupply_span
 * @property integer $single_tactic_main_id
 * @property integer $status
 */
class SkuSingleTacticMainContent extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%sku_single_tactic_main_content}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['supply_days', 'single_tactic_main_id','minimum_safe_stock_days', 'status','days_safe_transfer'], 'required'],
            [['supply_days', 'single_tactic_main_id','minimum_safe_stock_days','status','days_safe_transfer'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'supply_days' => Yii::t('app', '补货天数'),
            'minimum_safe_stock_days' => Yii::t('app', '最低安全库存天数'),
            'single_tactic_main_id' => Yii::t('app', 'Single Tactic Main ID'),
            'status' => Yii::t('app', '是否可用'),
            'days_safe_transfer' => Yii::t('app', '安全调拨天数'),
        ];
    }
}
