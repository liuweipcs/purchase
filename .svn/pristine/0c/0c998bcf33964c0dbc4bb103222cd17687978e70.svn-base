<?php

namespace app\api\v1\controllers;
use app\models\PurchaseOrder;
use app\models\PurchaseOrderBreakage;
use app\models\PurchaseOrderItems;
use app\models\PurchaseOrderPay;
use app\models\PurchaseOrderReceipt;
use app\models\PurchaseOrderRefundQuantity;
use yii;
use linslin\yii2\curl;
use yii\helpers\Json;

/**
 *申请退款、报损数据推送给仓库 取消在途数量
 * Created by PhpStorm.
 * User: ztt
 * Date: 2018/6/16 0023
 * Time: 18:41
 */

class RefundQtyController extends BaseController
{
    /***
     * 申请退款数据推送给仓库
     */
    public function actionRefund()
    {
        set_time_limit(0);
        //组装参数 查询申请退款数量 未推送、已收款的数据
        $arrRefundOrder = PurchaseOrderRefundQuantity::find()
            ->alias('refund')
            ->select(['refund.*', 'pur_purchase_order_receipt.pay_status', 'pur_purchase_order.warehouse_code','pur_purchase_order.transit_warehouse',
                'pur_purchase_order.purchas_status', 'pur_purchase_order.purchase_type' , 'pur_purchase_order_items.id as item_id', 'refund.is_cancel'])
            ->leftJoin(PurchaseOrderReceipt::tableName(), 'pur_purchase_order_receipt.requisition_number=refund.requisition_number')
            ->leftJoin(PurchaseOrder::tableName(), 'pur_purchase_order.pur_number=refund.pur_number')
            ->leftJoin(PurchaseOrderItems::tableName(), 'pur_purchase_order_items.pur_number=refund.pur_number')
            ->where(['refund.refund_status' => 0,'pur_purchase_order_receipt.pay_status' => 2])
            ->orWhere(['refund.refund_status' => 0,'refund.is_cancel' => 1])
            ->asArray()
            ->all();

        if ($arrRefundOrder) {
            try {
                $curl = new curl\Curl();
                $url = Yii::$app->params['server_ip'] . '/index.php/purchases/cancelPurchaseByOrderSku';
                foreach ($arrRefundOrder as $value) {
                    //开启事物(因为不确定订单是否更改状态成功(要把改变后的订单状态一起推送过去)，所以单个请求接口，如果推送返回成功则提交事物)
                    $transaction = Yii::$app->db->beginTransaction();
                    //默认状态1  如果更改状态或请求接口失败则为0，事物回滚
                    $status = 1;

                    //获取未到货数
                    $order_model = new PurchaseOrder();
                    $pay_model = new PurchaseOrderPay();
                    $payInfo = $pay_model->getPayDetail($value['pur_number']);
                    $orderInfo = $order_model->getOrderDetail($value['pur_number'], $payInfo['skuNum']);
                    $weidaohuo_num = 0;//未到货数
                    foreach ($orderInfo['purchaseOrderItems'] as $val) {
                        $weidaohuo_num += $val['ctq'] - $val['ruku_num'];
                    }
                    //本次退款数量+历史退款数=未到货数；历史订单(所有退款数) 订单状态：部分到货不等待剩余
                    //历史成功推送仓库退款数
                    $history_ctq = PurchaseOrderRefundQuantity::find()
                        ->select('sum(refund_qty) as total')
                        ->where(['pur_number' => $value['pur_number'], 'refund_status' => 1])
                        ->scalar();
                    $history_ctq = !empty($history_ctq) ? $history_ctq : 0;
                    $change_status = '';
                    if ($value['refund_qty'] + $history_ctq == $weidaohuo_num && $value['is_cancel'] !=1 && $value['purchas_status'] !=10 && $value['purchas_status'] !=9) {//本次退款数量+历史退款数=未到货数  部分到货不等待剩余
                        $change_status = 9;
                        $res = self::UpdatePurchaseStatus($orderInfo['id'], 9);
                        if (!$res) {
                            $status = 0;
                        }
                    }

                    ////更改状态结束
                    if ($status) {
                        ////推送退款数量开始
                        $data = $this->getPurchase($value,$change_status,1);

                        if (!empty($data)) {
                            $result = $curl->setPostParams([
                                'purchase' => Json::encode($data),
                            ])->post($url);

                            $res_data = Json::decode($result);
                            if (isset($res_data['success_list'])) {
                                $self = PurchaseOrderRefundQuantity::find()
                                    ->where(['id' => $value['id']])
                                    ->one();

                                if ($self && $self->refund_status == 0) {
                                    $self->refund_status = 1;
                                    $result = $self->save();

                                    if (!$result) {
                                        $status = 0;
                                    }
                                }
                            } else {
                                $status = 0;
                            }
                        }
                        ////推送退款数量结束
                    }

                    //成功提交更改订单状态、改变推送状态      失败仅改变推送状态
                    if ($status) {
                        $transaction->commit();
                    } else {
                        $transaction->rollBack();

                        if (isset($res_data['failure_list'])) {
                            $self = PurchaseOrderRefundQuantity::find()
                                ->where(['id' => $value['id']])
                                ->one();
                            if ($self && $self->refund_status == 0) {
                                $self->refund_status = 2;
                                $self->save();
                            }
                        }
                    }
                }
            }catch (Exception $e) {

                exit('发生了错误');
            }
        }else{
            exit('没有在途数据推送');
        }
    }

    /***
     * 报损数据推送给仓库
     */
    public function actionRefundBreakage()
    {
        set_time_limit(0);
        //组装参数 查询已通过的报损数量 未推送的数据
        $arrRefundOrder = PurchaseOrderBreakage::find()
            ->alias('a')
            ->select(['a.*', 'pur_purchase_order.warehouse_code', 'pur_purchase_order.purchas_status', 'pur_purchase_order.purchase_type',
                'pur_purchase_order_items.id as item_id','pur_purchase_order.transit_warehouse'])
            ->leftJoin(PurchaseOrder::tableName(), 'a.pur_number=pur_purchase_order.pur_number')
            ->leftJoin(PurchaseOrderItems::tableName(), 'a.pur_number=pur_purchase_order_items.pur_number')
            ->where(['a.status' => 3 , 'a.refund_status' => 0])
            ->asArray()
            ->all();

        if ($arrRefundOrder) {
            try {
                $curl = new curl\Curl();
                $url = Yii::$app->params['server_ip'] . '/index.php/purchases/cancelPurchaseByOrderSku';
                foreach ($arrRefundOrder as $value) {
                    //开启事物(因为不确定订单是否更改状态成功(要把改变后的订单状态一起推送过去)，所以单个请求接口，如果推送返回成功则提交事物)
                    $transaction = Yii::$app->db->beginTransaction();
                    //默认状态1  如果更改状态或请求接口失败则为0，事物回滚
                    $status = 1;

                    //采购数
                    $all_ctq = PurchaseOrderItems::find()->where(['pur_number'=>$value['pur_number']])->sum('ctq');

                    //获取未到货数
                    $order_model   = new PurchaseOrder();
                    $pay_model     = new PurchaseOrderPay();
                    $payInfo       = $pay_model->getPayDetail($value['pur_number']);
                    $orderInfo     = $order_model->getOrderDetail($value['pur_number'], $payInfo['skuNum']);
                    $weidaohuo_num = 0;//未到货数
                    foreach ($orderInfo['purchaseOrderItems'] as $val){
                        $weidaohuo_num += $val['ctq']-$val['ruku_num'];
                    }

                    //本次报损数+历史成功推送仓库报损数=未到货数；历史订单(所有退款数) 订单状态：部分到货不等待剩余
                    //本次报损数+历史成功推送仓库报损数=采购数；历史订单(所有退款数) 订单状态：订单作废
                    //历史成功报损数量
                    $history_ctq = PurchaseOrderBreakage::find()
                        ->select('sum(breakage_num) as total')
                        ->where(['pur_number' => $value['pur_number'],'status'=>3,'refund_status'=>1])
                        ->scalar();
                    $history_ctq = !empty($history_ctq)?$history_ctq:0;
                    $change_status = '';
                    if($history_ctq + $value['breakage_num'] == $all_ctq && $value['purchas_status'] != 10){//本次报损数+历史成功推送仓库报损数=采购数  订单作废
                        $change_status = 10;
                        $res = self::UpdatePurchaseStatus($orderInfo['id'],10);
                        if(!$res){
                            $status = 0;
                        }
                    }elseif($history_ctq + $value['breakage_num'] == $weidaohuo_num && $value['purchas_status'] != 10 && $value['purchas_status'] != 9){//本次报损数+历史成功推送仓库报损数=未到货数  部分到货不等待剩余
                        $change_status = 9;
                        $res = self::UpdatePurchaseStatus($orderInfo['id'],9);
                        if(!$res){
                            $status = 0;
                        }
                    }
                    ////更改状态结束

                    if($status) {
                        ////推送退款数量开始
                        $data = $this->getPurchase($value,$change_status,2);
                        if (!empty($data)) {
                            $result = $curl->setPostParams([
                                'purchase' => Json::encode($data),
                            ])->post($url);
                            $res_data = Json::decode($result);
                            if (isset($res_data['success_list'])) {
                                $self = PurchaseOrderBreakage::find()
                                    ->where(['id' => $value['id']])
                                    ->one();

                                if ($self && $self->refund_status == 0) {
                                    $self->refund_status = 1;
                                    $result = $self->save();

                                    if (!$result) {
                                        $status = 0;
                                    }
                                }
                            } else {
                                $status = 0;
                            }
                        }
                        ////推送退款数量结束
                    }

                    //成功提交更改订单状态、改变推送状态      失败仅改变推送状态
                    if ($status) {
                        $transaction->commit();
                    } else {
                        $transaction->rollBack();

                        if (isset($res_data['failure_list'])) {
                            $self = PurchaseOrderBreakage::find()
                                ->where(['id' => $value['id']])
                                ->one();
                            if ($self && $self->refund_status == 0) {
                                $self->refund_status = 2;
                                $self->save();
                            }
                        }
                    }
                }
            } catch (Exception $e) {

                exit('发生了错误');
            }
        }else{
            exit('没有在途数据推送');
        }
    }




    //更改订单状态
    public  static  function  UpdatePurchaseStatus($ids,$type)
    {
        if (!$ids) return;
        $map['id']=strpos($ids,',') ? explode(',',$ids):$ids;
        $orders=PurchaseOrder::find()->select('id,purchas_status,is_push')->where($map)->all();
        foreach ($orders as $v)
        {
            $v->purchas_status=$type;
            $result =$v->save(false);
        }

        return $result;
    }

    //组装参数
    protected function  getPurchase($value, $change_status='' , $type=1)
    {
        if(empty($value)){
            return [];
        }

        $data = [];
        $list = [];
        $list['pur_number'] = $value['pur_number'];//采购单号
        $list['warehouse_code'] = $value['warehouse_code'];//仓库编码
        $list['sku'] = $value['sku'];//sku
        if($type == 1){
            $list['ctq'] = $value['refund_qty'];//取消数量
            $list['check_operator'] = '';
            $list['cancel_operator'] = $value['creator'];//取消操作人
        }elseif($type == 2){
            $list['ctq'] = $value['breakage_num'];//报损数量
            $list['check_operator'] = $value['approval_person'];//审批人
            $list['cancel_operator'] = $value['apply_person'];//取消操作人
        }
        $list['type'] = $type;//1退款 2报损
        $list['status'] = !empty($change_status) ? $change_status : $value['purchas_status'];//当前订单状态
        $list['purchase_type'] = $value['purchase_type'];//采购类型
        $list['id'] = $value['item_id'];//采购单详情的id
        $list['transit_warehouse'] = $value['transit_warehouse'];//中转仓
        $data[] = $list;

        return $data;
    }
}