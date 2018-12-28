<?php

namespace app\models;

use app\models\base\BaseModel;

use Yii;
use app\config\Vhelper;
use app\services\PurchaseOrderServices;
use app\services\BaseServices;

/**
 * This is the model class for table "{{%warehouse_results}}".
 *
 * @property integer $id
 * @property string $pur_number
 * @property string $sku
 * @property string $express_no
 * @property integer $purchase_quantity
 * @property integer $arrival_quantity
 * @property integer $nogoods
 * @property integer $have_sent_quantity
 * @property integer $receipt_number
 */
class WarehouseResults extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%warehouse_results}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['purchase_quantity', 'arrival_quantity', 'nogoods', 'have_sent_quantity'], 'integer'],
            [['pur_number', 'sku', 'express_no','receipt_number'], 'string', 'max' => 30],
            [['express_no'], 'string', 'max' => 255],
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
            'sku' => Yii::t('app', '产品sku'),
            'express_no' => Yii::t('app', '快递单号'),
            'purchase_quantity' => Yii::t('app', '采购数量'),
            'arrival_quantity' => Yii::t('app', '到货数量'),
            'nogoods' => Yii::t('app', '不良品数量'),
            'have_sent_quantity' => Yii::t('app', '赠送数量'),
            'receipt_number' => Yii::t('app', '入库单号'),
        ];
    }

    /**
     * @param $pur_number
     * @param $sku
     * @param string $filed
     * @return array|null|\yii\db\ActiveRecord
     */
    public static  function  getResults($pur_number,$sku,$filed='*')
    {
        return self::find()->select($filed)->where(['pur_number'=>$pur_number,'sku'=>$sku])->one();
    }
    /**
     * 获取入库信息：采购单和sku
     * 入库单号(receipt_number)、良品上架数量(instock_qty_count)、入库时间(instock_date)
     */
    public static function getInstockInfo($pur_number,$sku,$demand_number=null)
    {
        $data = [];

        //判断采购单类型
        $type = Vhelper::getNumber($pur_number); //1国内，2海外，3fba
        $results = self::getResults($pur_number,$sku); //入库详情信息
        $items = PurchaseOrderItems::find()->where(['pur_number'=>$pur_number, 'sku'=>$sku])->one(); //订单详情信息
        $order = PurchaseOrder::find()->where(['pur_number'=>$pur_number])->one(); //订单主表信息

        $instock_qty_count = !empty($results->instock_qty_count) ? $results->instock_qty_count: 0;
        $arrival_quantity = !empty($results->arrival_quantity) ? $results->arrival_quantity : 0;

        $data['ctq'] = $items['ctq']?:0;
        if ($type == 1) {
            # 国内
            $data['instock_qty_count'] = $instock_qty_count; //良品上架数量
        } elseif ($type ==2) {
            # 海外
            $data['wcq'] = !empty($items->wcq)?$items->wcq:0; //仓库取消数量
            $data['instock_qty_count'] = $arrival_quantity;//良品上架数量
        } else {
            # FBA
            $data['arrival_quantity'] = $items->rqy?:$arrival_quantity; //收货数量
            $data['instock_qty_count'] = $items->cty?:$instock_qty_count;//良品上架数量
        }

        if ($demand_number) {
            $summaryModel = PlatformSummary::find()->where(['demand_number'=>$demand_number])->andWhere(['>','agree_time','2018-08-29 10:00:00'])->one();
            if ($summaryModel) {
                $data['instock_qty_count'] = $summaryModel['cty']?:0;
                $data['ctq'] = $summaryModel['purchase_quantity']?:0;
            }
        }

        $data['nogoods'] = !empty($results->nogoods)?$results->nogoods:0;; //不良品数量
        $data['instock_date'] = !empty($results->instock_date)?$results->instock_date:''; //入库时间
        $data['instock_user'] = !empty($results->instock_user)?$results->instock_user:''; //收货人
        $data['receipt_number'] = !empty($results->receipt_number)?$results->receipt_number:''; //入库单号


        $data['purchas_status'] = PurchaseOrderServices::getPurchaseStatusText($order->purchas_status); //状态
        $data['warehouse_code'] = $order->warehouse_code; //仓库编码
        $data['warehouse_name'] = !empty($order->warehouse_code)?BaseServices::getWarehouseCode($order->warehouse_code):''; //仓库名称
        return $data;
    }
    /**
     * 获取入库信息：针对采购单
     */
    public static function getOrderInstockInfo($pur_number)
    {
        //判断采购单类型
        $type = Vhelper::getNumber($pur_number); //1国内，2海外，3fba

        $results = self::find()
            ->select('sum(arrival_quantity) as arrival_quantity, sum(instock_qty_count) as instock_qty_count, sum(nogoods) as nogoods')
            ->where(['pur_number'=>$pur_number])
            ->asArray()->all()[0]; //入库详情信息

        $items = PurchaseOrderItems::find()
            ->select('sum(ctq) as ctq, sum(wcq) as wcq, sum(rqy) as rqy,sum(cty) as cty')
            ->where(['pur_number'=>$pur_number])
            ->asArray()->all()[0]; //订单详情信息

        //如果没有数据，默认为：0
        if (empty($results['instock_qty_count'])) {
            $results['arrival_quantity'] = $results['instock_qty_count'] = $results['nogoods'] = 0;
            $items['wcq'] = $items['rqy'] = $items['cty'] = 0;
        }

        if ($type == 2) {
            # 海外
//            $data['wcq'] = !empty($items->wcq)?$items->wcq:0; //仓库取消数量
            $results['instock_qty_count'] = $results['arrival_quantity'];//良品上架数量
        } else {
            # 国内
//            $data['instock_qty_count'] = !empty($results->instock_qty_count) ? $results->instock_qty_count: 0; //良品上架数量
            # FBA
//            $data['arrival_quantity'] = $items->rqy; //收货数量
//            $data['instock_qty_count'] = $items->cty;//良品上架数量??
        }

        return array_merge($results,$items);
    }
}
