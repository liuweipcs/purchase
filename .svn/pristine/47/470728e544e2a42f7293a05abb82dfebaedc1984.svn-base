<?php

namespace app\models;

use app\models\base\BaseModel;

use Yii;

/**
 * This is the model class for table "sku_total".
 *
 * @property integer $id
 * @property string $sku
 * @property integer $demand_total
 * @property integer $purchase_total
 */
class SkuTotal extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sku_total';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id'], 'required'],
            [['id', 'demand_total', 'purchase_total'], 'integer'],
            [['sku'], 'string', 'max' => 30],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'sku' => Yii::t('app', 'Sku'),
            'demand_total' => Yii::t('app', 'Demand Total'),
            'purchase_total' => Yii::t('app', 'Purchase Total'),
        ];
    }
}
