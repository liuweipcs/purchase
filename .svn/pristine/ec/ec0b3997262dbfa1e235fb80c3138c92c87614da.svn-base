<?php

namespace app\models;

use app\models\base\BaseModel;

use app\config\Vhelper;
use Yii;

/**
 * This is the model class for table "{{%purchase_order_cancel}}".
 *
 * @property string $id
 * @property string $pur_number
 * @property string $sku
 * @property string $cancel_ctq
 * @property string $items_totalprice
 * @property string $create_time
 * @property integer $audit_status
 * @property string $audit_time
 *
 * @property PurchaseOrder $purNumber
 */
class PurchaseOrderCancelSub extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%purchase_order_cancel_sub}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['pur_number', 'sku', 'cancel_ctq'], 'required'],
            [['cancel_ctq'], 'integer'],
            [['items_totalprice'], 'number'],
            [['create_time', 'audit_time'], 'safe'],
            [['pur_number'], 'string', 'max' => 100],
            [['sku'], 'string', 'max' => 30],
            [['pur_number', 'sku'], 'unique', 'targetAttribute' => ['pur_number', 'sku'], 'message' => 'The combination of Pur Number and Sku has already been taken.'],
            [['pur_number'], 'exist', 'skipOnError' => true, 'targetClass' => PurchaseOrder::className(), 'targetAttribute' => ['pur_number' => 'pur_number']],
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
            'cancel_ctq' => 'Cancel Ctq',
            'items_totalprice' => 'Items Totalprice',
            'create_time' => 'Create Time',
            'audit_status' => 'Audit Status',
            'audit_time' => 'Audit Time',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPurNumber()
    {
        return $this->hasOne(PurchaseOrder::className(), ['pur_number' => 'pur_number']);
    }
    /**
     * 关联取消未到货主表
     */
    public function getPurchaseOrderCancel()
    {
        return $this->hasOne(PurchaseOrderCancel::className(), ['id' => 'cancel_id']);
    }
    /**
     * 获取已经取消货物数量 -- 审核过的（单个sku）
     */
    public static function getCancelCtq($pur_number=null,$sku=null,$demand_number=null)
    {
        if (is_array($pur_number)) {
            $cancel_ctq = [];
            $cancel_model = PurchaseOrderCancel::find()->select('id')->where(['in', 'pur_number', $pur_number])->andWhere(['audit_status'=>2])->asArray()->all();
            if (empty($cancel_model)) return false;

            $ids = array_column($cancel_model, 'id');
            $cancelSubInfo =  self::find()->select('pur_number,sku,SUM(cancel_ctq) as cancel_ctq')->where(['in', 'cancel_id', $ids])->groupBy('pur_number,sku')->asArray()->all();

            if (!empty($cancelSubInfo)) {
                foreach ($cancelSubInfo as $k => $v) {
                    $cancel_ctq[$v['pur_number']][$v['sku']] = $v['cancel_ctq'];
                }
            }
        } else {
            $cancel_model = PurchaseOrderCancel::find()->select('id')->where(['pur_number'=>$pur_number])->andWhere(['audit_status'=>2])->asArray()->all();

            $cancel_ctq = 0;
            $where = [];
            if (!empty($cancel_model)) {
                foreach ($cancel_model as $k=>$v) {
                    $where = ['pur_number'=>$pur_number,'sku'=>$sku,'cancel_id'=>$v['id']];
                    if (!empty($demand_number)) $where['demand_number'] = $demand_number;
                    $res =  self::find()->where($where)->sum('cancel_ctq');
                    $cancel_ctq += $res;
                }
            }
        }

        return $cancel_ctq;
    }
    /**
     * 获取已经取消货物【数量】 -- 审核过的（整个单的）
     */
    public static function getCancelCtqOrder($pur_number)
    {
        $cancel_model = PurchaseOrderCancel::find()->select('id')->where(['pur_number'=>$pur_number])->andWhere(['audit_status'=>2])->asArray()->all();

        $cancel_ctq = 0;
        if (!empty($cancel_model)) {
            foreach ($cancel_model as $k=>$v) {
                $res =  self::find()->where(['pur_number'=>$pur_number,'cancel_id'=>$v['id']])->sum('cancel_ctq');
                $cancel_ctq += $res;
            }
        }
        return $cancel_ctq;
    }
    /**
     * 获取已经取消货物【金额】 -- 审核过的（整个单的）
     */
    public static function getCancelPriceOrder($pur_number)
    {
        $cancel_model = PurchaseOrderCancel::find()->where(['pur_number'=>$pur_number])->andWhere(['audit_status'=>2])->asArray()->all();
        $cancel_price = $price = 0;
        if (!empty($cancel_model)) {
            $itemsPriceInfo = PurchaseOrderItems::getItemsPrice($pur_number);

            foreach ($cancel_model as $k=>$v) {
                $res =  self::find()->where(['pur_number'=>$pur_number,'cancel_id'=>$v['id']])->asArray()->all();

                foreach ($res as $re) {
                    foreach ($itemsPriceInfo as $iv) {
                        if ($re['pur_number']==$iv['pur_number'] && $re['sku']==$iv['sku'] ) {
                           $price = $iv['price'];
                        }
                    }
                    $cancel_price += $price*$re['cancel_ctq'];
                }
                $cancel_price += $v['freight'] -$v['discount'];
            }
        }
        return $cancel_price;
    }
    /**
     * 保存取消未到货数量
     */
    public static function saveCancelSub($data,$is_all_cancel)
    {
        if ($is_all_cancel==2) {
            //全部取消
            foreach ($data as $k => $v) {
                $cancel_sub_model = new  self();
                $cancel_sub_model->cancel_id = $v['cancel_id'];
                $cancel_sub_model->pur_number = $v['pur_number'];
                $cancel_sub_model->sku = $v['sku'];
                $cancel_sub_model->cancel_ctq = $v['ctq'];
                $cancel_sub_model->items_totalprice = $v['ctq'] * $v['price'];
                $cancel_sub_model->create_time = date('Y-m-d H:i:s', time());
                if (!empty($v['old_demand_status'])) {
                    $cancel_sub_model->old_demand_status = $v['old_demand_status'];
                }
                if (!empty($v['demand_number'])) {
                    $cancel_sub_model->demand_number = $v['demand_number'];
                }
                $status = $cancel_sub_model->save(false);
            }
        } else {
            //部分取消
            foreach ($data as $k => $v) {
                $cancel_sub_model = new  self();
                $cancel_sub_model->cancel_id = $v['cancel_id'];
                $cancel_sub_model->pur_number = $v['pur_number'];
                $cancel_sub_model->sku = $v['sku'];
                $cancel_sub_model->cancel_ctq = $v['cancel_ctq'];
                $cancel_sub_model->items_totalprice = $v['cancel_ctq'] * $v['price'];
                $cancel_sub_model->create_time = date('Y-m-d H:i:s', time());
                if (!empty($v['old_demand_status'])) {
                    $cancel_sub_model->old_demand_status = $v['old_demand_status'];
                }
                if (!empty($v['demand_number'])) {
                    $cancel_sub_model->demand_number = $v['demand_number'];
                }
                $status = $cancel_sub_model->save(false);
            }
        }

    }
    /**
     * 获取取消明细（此次取消的数量和金额）
     */
    public static function getCancelDetail($cancel_id)
    {
        $tax_price = $price = 0;
        $cancel_price_total = 0;

        $info = self::find()->where(['cancel_id'=>$cancel_id])->asArray()->all();
        $cancel_ctq_total = self::find()->where(['cancel_id'=>$cancel_id])->sum('cancel_ctq');

        $pur_number = $info[0]['pur_number'];
        $itemsPriceInfo = PurchaseOrderItems::getItemsPrice($pur_number);

        foreach ($info as $k=>$v) {
            foreach ($itemsPriceInfo as $id => $iv) {
                if ($v['pur_number']==$iv['pur_number'] && $v['sku']==$iv['sku'] ) {
                   $price = $iv['price'];
                }
            }
            $cancel_price_total += ($v['cancel_ctq'] * $price);
        }
        //运费
        $freight = PurchaseOrderCancel::getFreight($cancel_id);
        //优惠额
        $discount = PurchaseOrderCancel::getDiscount($cancel_id);


        //取消金额
        $cancel_price_total = $cancel_price_total + $freight - $discount;

        return ['cancel_ctq_total'=>$cancel_ctq_total,'cancel_price_total'=>$cancel_price_total];
    }
    /**
     * 获取取消数量（单个sku）
     */
    public static function getCancelCtqSku($cancel_id,$pur_number,$sku=null)
    {
        return self::find()->select('cancel_ctq')->where(['cancel_id'=>$cancel_id,'pur_number'=>$pur_number,'sku'=>$sku])->scalar();
    }
    /**
     *  比较 取消数量  和  采购数量-入库数量
     *  $audit 是否包含待审核的：false审核，true审核+待审核
     *  
     */
    public static function cancelCtqBccomp($pur_number,$audit=false,$cancel_id=false)
    {
        $is_cancel_insrock = false; //是否取消数量大于未入库数量：true是，false否
        //采购数量
        $ctq_total = PurchaseOrderItems::getCtqTotal($pur_number);
        //入库数量
        $order_model   = new PurchaseOrder();
        $pay_model     = new PurchaseOrderPay();
        $payInfo       = $pay_model->getPayDetail($pur_number);
        $orderInfo     = $order_model->getOrderDetail($pur_number, $payInfo['skuNum']);
        $ruku_num = 0;
        if (!empty($orderInfo['purchaseOrderItems'])) {
            foreach($orderInfo['purchaseOrderItems'] as $k => $v) {
                $type = Vhelper::getNumber($v['pur_number']); //1国内，2海外，3fba
                if ($type == 2) {
                    $v['ruku_num'] = WarehouseResults::getInstockInfo($v['pur_number'],$v['sku'])['instock_qty_count'];
                }
                $ruku_num +=$v['ruku_num'];
            }
        }

        //取消的货物数量
        if ($audit) {
            //当前审核和审核通过的（审核时计算的）
            $cancel_ctq = PurchaseOrderCancelSub::getCancelDetail($cancel_id)['cancel_ctq_total']; //当前取消的数量
            $cancel_ctq_order = PurchaseOrderCancelSub::getCancelCtqOrder($pur_number); //审核通过：总的取消数量
            $cancel_ctq_res = $cancel_ctq + $cancel_ctq_order; //最终取消的数量：当前取消数量 - 审核通过的数量（以前的取消的数量）
        } else {
            //只算审核通过的（财务付款时比较）
            $cancel_ctq_res = self::getCancelCtqOrder($pur_number);
        }

        $ctq = $ctq_total-$ruku_num;
        $cancel_purchase_warehouses = bccomp($cancel_ctq_res,$ctq);//如果：取消数量 = 采购数量-入库数量，状态变为，部分到货不等待剩余
        //0 取消等于采购，-1取消小于采购，1取消大于采购
        $cancel_purchase = bccomp($cancel_ctq_res,$ctq_total); //取消数量和采购数量作对比


        /**
         * 单个sku或需求的取消数量和未到货数量作对比
         */
        $cancelInfo = PurchaseOrderCancel::find()->alias('poc')->joinWith('purchaseOrderCancelSub')->where(['poc.pur_number'=>$pur_number, 'poc.audit_status'=>[1, 2]])->asArray()->all();
        if (!empty($cancelInfo)) {
            $cancelDetail = [];
            $cancelSubInfo = array_column($cancelInfo, 'purchaseOrderCancelSub');
            foreach ($cancelSubInfo as $sk => $sv) {
                foreach ($sv as $ssk => $ssv) {
                    if ($ssv['cancel_ctq']==0) continue;
                    $dk = $ssv['sku'] . $ssv['demand_number'];
                    if (isset($cancelDetail[$dk])) {
                        $cancelDetail[$dk]['cancel_ctq']+=$ssv['cancel_ctq'];
                    } else {
                        $cancelDetail[$dk]['cancel_ctq'] = $ssv['cancel_ctq'];
                    }
                    $cancelDetail[$dk]['sku'] = $ssv['sku'];
                    $cancelDetail[$dk]['demand_number'] = $ssv['demand_number'];
                    $cancelDetail[$dk]['pur_number'] = $ssv['pur_number'];
                }
            }
            foreach ($cancelDetail as $value) {
                $instockInfo = WarehouseResults::getInstockInfo($value['pur_number'],$value['sku'], $value['demand_number']);
                $ruku_num = $instockInfo['instock_qty_count'];
                $ctq = $instockInfo['ctq'];
                if ($value['cancel_ctq']>($ctq-$ruku_num)) {
                    # 如果取消数量，大于未到货数量，则退出，
                    $is_cancel_insrock = true;
                    break;
                }
            }
        }
        return ['cancel_purchase_warehouses'=>$cancel_purchase_warehouses, 'cancel_purchase'=>$cancel_purchase, 'is_cancel_insrock'=>$is_cancel_insrock];
    }
    /**
     * 获取旧的需求状态
     */
    public static function getOldDemandStatus($demand_number)
    {
        return self::find()->select('old_demand_status')->where(['demand_number'=>$demand_number])->scalar();
    }
    /**
     * 获取已经取消货物【需求单号】 -- 审核过的（整个单的）
     */
    public static function getCancelDemandNumber($pur_number)
    {
        $cancel_model = PurchaseOrderCancel::find()->select('id')->where(['pur_number'=>$pur_number])->andWhere(['audit_status'=>2])->asArray()->all();
        if (empty($cancel_model)) return false;
        $cancel_ids = array_filter( array_column($cancel_model,'id') );
        $demand_numbers =  self::find()->select('demand_number')->where(['in','cancel_id', $cancel_ids])->asArray()->all();
        if (empty($demand_numbers)) return false;
        $demand_numbers = array_filter( array_column($demand_numbers,'demand_number') );
        return $demand_numbers;
    }
    /**
     * 获取指定sku或需求的取消数量和金额--审核通过和未审核的
     */
    public static function getSkuDemandCtq($pur_number,$sku=false,$demand_number=false)
    {
        $cancel_price = $cancel_ctq = $items_ctq = $demand_ctq = 0;
        $where = [];
        if ($pur_number) $where['pur_purchase_order_cancel.pur_number'] = $pur_number;
        if ($sku) $where['sku'] = $sku;
        if ($demand_number) {
            $where['demand_number'] = $demand_number;
            $summaryInfo = PlatformSummary::find()->select('purchase_quantity')->where(['demand_number'=>$demand_number])->asArray()->one();
            $demand_ctq = $summaryInfo['purchase_quantity'];
        }
        $cancelInfo = PurchaseOrderCancel::find()
            ->joinWith(['purchaseOrderCancelSub'])
            ->where($where)
            ->andWhere(['in', 'audit_status', [1, 2]])
            ->asArray()->all();

        if (empty($cancelInfo)) {
            return false;
        } else {
            $purchaseOrderCancelSub = array_column($cancelInfo, 'purchaseOrderCancelSub');
            foreach ($purchaseOrderCancelSub as $k => $v) {
                foreach ($v as $key => $value) {
                    //海外仓新版
                    if (!empty($demand_number) && !empty($value['demand_number']) && $value['demand_number']==$demand_number ) {
                        $itemsInfo = PurchaseOrderItems::find()->select('price,ctq')->where(['pur_number'=>$pur_number,'sku'=>$value['sku']])->asArray()->one();
                        $cancel_price += $value['cancel_ctq']*$itemsInfo['price'];
                        $cancel_ctq += $value['cancel_ctq'];
                        $items_ctq += $itemsInfo['ctq'];
                    }
                    //海外仓旧版和FBA
                    if (empty($demand_number)) {
                        $itemsInfo = PurchaseOrderItems::find()->select('price,ctq')->where(['pur_number'=>$pur_number,'sku'=>$value['sku']])->asArray()->one();
                        $cancel_price += $value['cancel_ctq']*$itemsInfo['price'];
                        $cancel_ctq += $value['cancel_ctq'];
                        $items_ctq += $itemsInfo['ctq'];
                    }
                    
                }
            }
        }
        return ['cancel_price'=>$cancel_price, 'cancel_ctq'=>$cancel_ctq, 'items_ctq'=>$items_ctq, 'demand_ctq'=>$demand_ctq];
    }
}
