<?php

namespace app\models;

use app\models\base\BaseModel;

use Yii;

/**
 * This is the model class for table "{{%today_list}}".
 *
 * @property integer $id
 * @property string $thumb_img
 * @property string $big_img
 * @property string $product_name
 * @property string $sku
 * @property integer $in_transit_inventory
 * @property integer $usable_inventory
 * @property integer $on_order_inventory
 * @property integer $stockout_qty
 * @property integer $developer_id
 * @property integer $buyer_id
 * @property string $create_time
 * @property string $create_user
 */
class TodayList extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%today_list}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['in_transit_inventory', 'usable_inventory', 'on_order_inventory', 'stockout_qty', 'developer_id'
                , 'buyer_id'], 'integer'],
            [['create_time'], 'safe'],
            [['thumb_img', 'big_img'], 'string', 'max' => 255],
            [['product_name'], 'string', 'max' => 100],
            [['sku', 'create_user'], 'string', 'max' => 20],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'thumb_img' => Yii::t('app', '缩略图'),
            'big_img' => Yii::t('app', '大图'),
            'product_name' => Yii::t('app', '货品名称'),
            'sku' => Yii::t('app', 'Sku'),
            'in_transit_inventory' => Yii::t('app', '在途库存'),
            'usable_inventory' => Yii::t('app', '可用库存'),
            'on_order_inventory' => Yii::t('app', '待发库存'),
            'stockout_qty' => Yii::t('app', '缺货数量'),
            'developer_id' => Yii::t('app', '开发员'),
            'buyer_id' => Yii::t('app', '采购员'),
            'create_time' => Yii::t('app', 'Create Time'),
            'create_user' => Yii::t('app', 'Create User'),
        ];
    }
}
