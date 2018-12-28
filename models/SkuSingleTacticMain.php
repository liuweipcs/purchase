<?php

namespace app\models;

use app\models\base\BaseModel;

use Yii;

/**
 * This is the model class for table "{{%sku_single_tactic_main}}".
 *
 * @property integer $id
 * @property string $sku
 * @property string $warehouse
 * @property string $date_start
 * @property string $date_end
 * @property string $user
 * @property string $create_date
 * @property integer $status
 */
class SkuSingleTacticMain extends BaseModel
{
    public $file_execl;
    public $supply_days;
    public $minimum_safe_stock_days;
    public $days_safe_transfer;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%sku_single_tactic_main}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['sku', 'warehouse', 'date_start', 'date_end', 'user', 'create_date', 'supply_days','minimum_safe_stock_days','days_safe_transfer'], 'required'],
            [['date_start', 'date_end', 'create_date'], 'safe'],
            [['status','supply_days','minimum_safe_stock_days','days_safe_transfer'], 'integer'],
            [['sku', 'warehouse'], 'string', 'max' => 30],
            [['user'], 'string', 'max' => 15],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'sku' => Yii::t('app', 'SKU'),
            'warehouse' => Yii::t('app', '仓库'),
            'date_start' => Yii::t('app', '生效时间'),
            'date_end' => Yii::t('app', '结束时间'),
            'user' => Yii::t('app', '创建者'),
            'create_date' => Yii::t('app', '创建时间'),
            'status' => Yii::t('app', '是否可用'),
            'supply_days' => Yii::t('app', '补货天数'),
            'minimum_safe_stock_days' => Yii::t('app', '最低安全库存天数'),
            'days_safe_transfer' => Yii::t('app', '安全调拨天数'),
        ];
    }

    /**
     *关联从表
     * @return $this
     */
    public function getScontent()
    {
        return $this->hasOne(SkuSingleTacticMainContent::className(), ['single_tactic_main_id' => 'id']);
    }
}
