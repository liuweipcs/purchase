<?php

namespace app\models;

use app\models\base\BaseModel;

use Yii;

/**
 * This is the model class for table "{{%overseas_warehouse_goods_tax_rebate}}".
 *
 * @property string $id
 * @property string $sku
 * @property string $country
 * @property string $tax_rate
 * @property integer $state
 * @property string $create_user
 * @property string $create_time
 */
class OverseasWarehouseGoodsTaxRebate extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%overseas_warehouse_goods_tax_rebate}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['sku','country','tax_rate'], 'required'],
            [['tax_rate'], 'number'],
            [['state'], 'integer'],
            [['create_time'], 'safe'],
            [['sku', 'country'], 'string', 'max' => 50],
            [['create_user'], 'string', 'max' => 20],
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
            'country' => Yii::t('app', '国家'),
            'tax_rate' => Yii::t('app', '税率'),
            'state' => Yii::t('app', '是否启用'),
            'create_user' => Yii::t('app', '创建人'),
            'create_time' => Yii::t('app', '创建时间'),
        ];
    }
}
