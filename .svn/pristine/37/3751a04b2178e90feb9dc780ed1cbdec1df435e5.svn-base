<?php

namespace app\models;

use app\models\base\BaseModel;

use Yii;

/**
 * This is the model class for table "{{%inventory_winit_us}}".
 *
 * @property integer $id
 * @property string $goodsSku
 * @property string $goodsName
 * @property string $goodsAlias
 * @property string $warehouseName
 * @property string $location
 * @property double $goodsAvgCost
 * @property integer $availableStockQuantity
 * @property integer $intransitStockQuantity
 * @property integer $waitingShipmentStockQuantity
 * @property integer $defectsStockQuantity
 * @property double $totalWorth
 * @property double $headlineCost
 * @property integer $safetyInventory
 * @property string $create_user
 * @property string $create_time
 */
class InventoryWinitUs extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%inventory_winit_us}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['goodsAvgCost', 'totalWorth', 'headlineCost'], 'number'],
            [['availableStockQuantity', 'intransitStockQuantity', 'waitingShipmentStockQuantity', 'defectsStockQuantity', 'safetyInventory'], 'integer'],
            [['create_time'], 'safe'],
            [['goodsSku', 'location', 'create_user'], 'string', 'max' => 20],
            [['goodsName', 'goodsAlias'], 'string', 'max' => 100],
            [['warehouseName'], 'string', 'max' => 30],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'goodsSku' => Yii::t('app', 'sku'),
            'goodsName' => Yii::t('app', '货品名'),
            'goodsAlias' => Yii::t('app', 'sku别名'),
            'warehouseName' => Yii::t('app', '仓库'),
            'location' => Yii::t('app', '货位'),
            'goodsAvgCost' => Yii::t('app', '平均成本(CNY)'),
            'availableStockQuantity' => Yii::t('app', '可用库存'),
            'intransitStockQuantity' => Yii::t('app', '在途库存'),
            'waitingShipmentStockQuantity' => Yii::t('app', '待发库存'),
            'defectsStockQuantity' => Yii::t('app', '故障品库存'),
            'totalWorth' => Yii::t('app', '仓库总价值'),
            'headlineCost' => Yii::t('app', '头程费用'),
            'safetyInventory' => Yii::t('app', '安全库存'),
            'create_user' => Yii::t('app', 'Create User'),
            'create_time' => Yii::t('app', 'Create Time'),
        ];
    }
}
