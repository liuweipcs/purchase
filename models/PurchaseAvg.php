<?php

namespace app\models;

use app\models\base\BaseModel;

use Yii;

/**
 * This is the model class for table "{{%purchase_avg}}".
 *
 * @property integer $id
 * @property string $sku
 * @property string $create_time
 * @property string $update_time
 * @property string $warehouse_code
 * @property string $avg_price
 */
class PurchaseAvg extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%purchase_avg}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['create_time', 'update_time'], 'safe'],
            [['avg_price'], 'required'],
            [['avg_price'], 'number'],
            [['sku', 'warehouse_code'], 'string', 'max' => 100],
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
            'create_time' => Yii::t('app', '创建时间'),
            'update_time' => Yii::t('app', '更新时间'),
            'warehouse_code' => Yii::t('app', '仓库编码'),
            'avg_price' => Yii::t('app', '采购平均价格'),
        ];
    }
}
