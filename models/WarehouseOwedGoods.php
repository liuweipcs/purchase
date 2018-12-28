<?php

namespace app\models;

use app\models\base\BaseModel;

use Yii;

/**
 * This is the model class for table "{{%warehouse_owed_goods}}".
 *
 * @property integer $id
 * @property string $sku
 * @property string $warehouse_code
 * @property integer $quantity_goods
 * @property string $name
 * @property string $order_pay_time
 * @property integer $create_id
 * @property string $create_time
 * @property integer $update_id
 * @property string $update_time
 * @property string $platform_code
 * @property integer $platform_order_id
 */
class WarehouseOwedGoods extends BaseModel
{
    /**
     * @inheritdoc
     */
    public $total_quantity_goods;
    public static function tableName()
    {
        return '{{%warehouse_owed_goods}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'sku', 'warehouse_code', 'quantity_goods', 'name', 'create_time', 'platform_code'], 'required'],
            [['id', 'quantity_goods','platform_order_id'], 'integer'],
            [['order_pay_time', 'create_time', 'update_time','note'], 'safe'],
            [['sku', 'warehouse_code'], 'string', 'max' => 50],
            [['name'], 'string', 'max' => 200],
            [['platform_code'], 'string', 'max' => 100],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'                => Yii::t('app', 'ID'),
            'sku'               => Yii::t('app', 'sku'),
            'warehouse_code'    => Yii::t('app', '仓库CODE'),
            'quantity_goods'    => Yii::t('app', '欠货数量'),
            'name'              => Yii::t('app', '产品名'),
            'order_pay_time'    => Yii::t('app', '订单付款时间'),
            'create_id'         => Yii::t('app', '创建ID'),
            'create_time'       => Yii::t('app', '创建时间'),
            'update_id'         => Yii::t('app', '更新人'),
            'update_time'       => Yii::t('app', '确认时间'),
            'platform_code'     => Yii::t('app', '平台code'),
            'platform_order_id' => Yii::t('app', '平台订单号'),
            'confirmor'         => Yii::t('app', '确认人'),
            'note'              => Yii::t('app', '备注'),
        ];
    }
}
