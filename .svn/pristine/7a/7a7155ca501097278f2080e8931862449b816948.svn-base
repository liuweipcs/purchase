<?php

namespace app\models;

use app\models\base\BaseModel;

use Yii;

/**
 * This is the model class for table "{{%purchase_order_items_stock}}".
 *
 * @property integer $id
 * @property string $pur_number
 * @property string $sku
 * @property integer $status
 * @property integer $order_total
 * @property integer $ticketed_total
 * @property integer $stock
 * @property integer $profit_loss
 * @property integer $stock_stay_days
 * @property string $update_time
 */
class PurchaseOrderItemsStock extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%purchase_order_items_stock}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['status', 'order_total', 'ticketed_total', 'stock', 'profit_loss', 'stock_stay_days'], 'integer'],
            [['profit_loss'], 'required'],
            [['update_time'], 'safe'],
            [['pur_number', 'sku'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'pur_number' => 'Pur Number',
            'sku' => 'Sku',
            'status' => 'Status',
            'order_total' => 'Order Total',
            'ticketed_total' => 'Ticketed Total',
            'stock' => 'Stock',
            'profit_loss' => 'Profit Loss',
            'stock_stay_days' => 'Stock Stay Days',
            'update_time' => 'Update Time',
        ];
    }
    /**
     * 关联订单详情表
     * @return $this
     */
    public function getPurchaseOrderItems(){
        return $this->hasOne(PurchaseOrderItems::className(), ['pur_number'=>'pur_number', 'sku'=>'sku']);
    }
    /**
     * 获取信息
     * $res = purchaseOrderItemsStock::getItemsStockInfo($model->pur_number, $model->sku);
     * $stock = !empty($res['stock']) ? $res['stock'] : 0;
     */
    public static function getItemsStockInfo($pur_number=null, $sku=null,$status=null)
    {
        if (!empty($pur_number)) {
            $where['pur_number'] = $pur_number;
        }
        if (!empty($sku)) {
            $where['sku']= $sku;
        }
        if (!empty($status)) {
            $where['status'] = $status;
        }
        $res = self::find()->where($where)->orderBy('id DESC')->asArray()->one();
        return $res;
    }
}
