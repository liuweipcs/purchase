<?php

namespace app\models;

use app\models\base\BaseModel;

use Yii;

/**
 * This is the model class for table "{{%purchase_demand_copy}}".
 *
 * @property integer $id
 * @property string $pur_number
 * @property string $demand_number
 * @property string $create_id
 * @property string $create_time
 */
class PurchaseDemandCopy extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%purchase_demand_copy}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['pur_number'], 'required'],
            [['create_time'], 'safe'],
            [['pur_number', 'demand_number'], 'string', 'max' => 100],
            [['create_id'], 'string', 'max' => 50],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'pur_number' => Yii::t('app', '采购单号'),
            'demand_number' => Yii::t('app', '需求单号'),
            'create_id' => Yii::t('app', '需求人'),
            'create_time' => Yii::t('app', '需求时间'),
        ];
    }
}
