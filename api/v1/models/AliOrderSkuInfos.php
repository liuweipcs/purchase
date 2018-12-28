<?php

namespace app\api\v1\models;

use Yii;

/**
 * This is the model class for table "pur_ali_order_sku_infos".
 *
 * @property integer $id
 * @property string $ali_sku_id
 * @property string $name
 * @property string $value
 * @property string $pur_number
 * @property string $order_number
 * @property integer $product_items_id
 */
class AliOrderSkuInfos extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'pur_ali_order_sku_infos';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['product_items_id'], 'integer'],
            [['ali_sku_id', 'pur_number'], 'string', 'max' => 50],
            [['name', 'value'], 'string', 'max' => 255],
            [['order_number'], 'string', 'max' => 100],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'ali_sku_id' => 'Ali Sku ID',
            'name' => 'Name',
            'value' => 'Value',
            'pur_number' => 'Pur Number',
            'order_number' => 'Order Number',
            'product_items_id' => 'Product Items ID',
        ];
    }

    public static function saveData($pur_number,$order_number,$data,$productId){
        $insertData=[];
        foreach ($data as $k=>$v){
            $insertData[$k][] = '';
            $insertData[$k][] = isset($v['name']) ? $v['name'] : '';
            $insertData[$k][] = isset($v['value']) ? $v['value'] : '';
            $insertData[$k][] = $pur_number;
            $insertData[$k][] = $order_number;
            $insertData[$k][] = $productId;
        }
        Yii::$app->db->createCommand()->batchInsert(self::tableName(),
            ['ali_sku_id','name','value','pur_number','order_number','product_items_id'],$insertData
            )->execute();
    }
}
