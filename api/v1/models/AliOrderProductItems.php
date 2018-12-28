<?php

namespace app\api\v1\models;

use app\config\Vhelper;
use Yii;

/**
 * This is the model class for table "pur_ali_order_product_items".
 *
 * @property integer $id
 * @property string $item_amount
 * @property string $name
 * @property string $price
 * @property string $product_img_url
 * @property string $product_snapshot_url
 * @property string $quantity
 * @property string $refund
 * @property string $unit
 * @property string $weight
 * @property string $weight_unit
 * @property string $entry_discount
 * @property integer $quantity_factor
 * @property string $status_str
 * @property string $refund_status
 * @property string $close_reason
 * @property integer $logistics_status
 * @property string $gmt_create
 * @property string $gmt_modified
 * @property string $gmt_completed
 * @property string $gmt_pay_expire_time
 * @property string $refund_id_for_as
 * @property string $sku_id
 * @property string $pur_number
 * @property string $order_number
 */
class AliOrderProductItems extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'pur_ali_order_product_items';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['item_amount', 'price', 'quantity', 'refund','sku_id'], 'number'],
            [['quantity_factor', 'logistics_status','entry_discount'], 'integer'],
            [['gmt_create', 'gmt_modified', 'gmt_completed', 'gmt_pay_expire_time'], 'safe'],
            [['name', 'product_snapshot_url', 'status_str', 'close_reason', 'refund_id_for_as'], 'string', 'max' => 255],
            [['product_img_url'], 'string', 'max' => 2000],
            [['unit', 'weight', 'weight_unit', 'order_number'], 'string', 'max' => 100],
            [['refund_status', 'pur_number'], 'string', 'max' => 50],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'item_amount' => 'Item Amount',
            'name' => 'Name',
            'price' => 'Price',
            'product_img_url' => 'Product Img Url',
            'product_snapshot_url' => 'Product Snapshot Url',
            'quantity' => 'Quantity',
            'refund' => 'Refund',
            'unit' => 'Unit',
            'weight' => 'Weight',
            'weight_unit' => 'Weight Unit',
            'entry_discount' => 'Entry Discount',
            'quantity_factor' => 'Quantity Factor',
            'status_str' => 'Status Str',
            'refund_status' => 'Refund Status',
            'close_reason' => 'Close Reason',
            'logistics_status' => 'Logistics Status',
            'gmt_create' => 'Gmt Create',
            'gmt_modified' => 'Gmt Modified',
            'gmt_completed' => 'Gmt Completed',
            'gmt_pay_expire_time' => 'Gmt Pay Expire Time',
            'refund_id_for_as' => 'Refund Id For As',
            'sku_id' => 'Sku ID',
            'pur_number' => 'Pur Number',
            'order_number' => 'Order Number',
        ];
    }
    public static function saveData($pur_number,$order_number,$data){
        $exist = self::find()->where(['pur_number'=>$pur_number,'order_number'=>$order_number])->exists();
        if($exist){
            return true;
        }
       foreach ($data as $v){
           $model = new self();
           $model->item_amount = isset($v['itemAmount']) ? $v['itemAmount'] :'';
           $model->name = isset($v['name']) ? $v['name'] :'';
           $model->price = isset($v['price']) ? $v['price'] :'';
           $model->product_img_url = isset($v['productImgUrl']) ? json_encode($v['itemAmount']) :'';
           $model->product_snapshot_url = isset($v['productSnapshotUrl']) ? $v['productSnapshotUrl'] :'';
           $model->quantity = isset($v['quantity']) ? $v['quantity'] :'';
           $model->refund = isset($v['refund']) ? $v['refund'] :'';
           $model->unit = isset($v['unit']) ? $v['unit'] :'';
           $model->weight = isset($v['weight']) ? $v['weight'] :'';
           $model->weight_unit = isset($v['weightUnit']) ? $v['weightUnit'] :'';
           $model->entry_discount = isset($v['entryDiscount']) ? $v['entryDiscount'] :'';
           $model->quantity_factor = isset($v['quantityFactor']) ? $v['quantityFactor'] :'';
           $model->status_str = isset($v['statusStr']) ? $v['statusStr'] :'';
           $model->refund_status = isset($v['refundStatus']) ? $v['refundStatus'] :'';
           $model->close_reason = isset($v['closeReason']) ? $v['closeReason'] :'';
           $model->logistics_status = isset($v['logisticsStatus']) ? $v['logisticsStatus'] :'';
           $model->gmt_create = isset($v['gmtCreate']) ? Vhelper::getAliDateTime($v['gmtCreate']) :'';
           $model->gmt_modified = isset($v['gmtModified']) ? Vhelper::getAliDateTime($v['gmtModified']) :'';
           $model->gmt_completed = isset($v['gmtCompleted']) ? Vhelper::getAliDateTime($v['gmtCompleted']) :'';
           $model->gmt_pay_expire_time = isset($v['gmtPayExpireTime']) ? Vhelper::getAliDateTime($v['gmtPayExpireTime']) :'';
           $model->refund_id_for_as = isset($v['refundIdForAs']) ? $v['refundIdForAs'] :'';
           $model->sku_id = isset($v['skuID']) ? $v['skuID'] :'';
           $model->pur_number = $pur_number;
           $model->order_number = $order_number;
           if($model->save()==false){
               return false;
           }
           if(isset($v['skuInfos'])){
               AliOrderSkuInfos::saveData($pur_number,$order_number,$v['skuInfos'],$model->id);
           }

       }
       return true;
    }
}
