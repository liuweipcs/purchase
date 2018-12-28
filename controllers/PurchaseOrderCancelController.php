<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/6/8
 * Time: 9:55
 */

namespace app\controllers;


use app\config\Vhelper;
use app\models\AmazonOutofstockOrder;
use app\models\PurchaseDemand;
use app\models\PurchaseLog;
use app\models\PurchaseOrder;
use app\models\PurchaseOrderCancel;
use app\models\PurchaseOrderCancelSearch;
use app\models\PurchaseOrderCancelSub;
use app\models\PurchaseOrderItems;
use app\models\PurchaseOrderOrders;
use app\models\PurchaseOrderPay;
use app\models\PurchaseOrderPayDetail;
use app\models\PurchaseOrderPayType;
use app\models\PurchaseOrderReceipt;
use app\models\PurchaseUser;
use app\models\PlatformSummary;
use app\models\PurchaseCompactItems;
use app\models\PurchaseCompact;
use app\models\OrderPayDemandMap;
use yii\data\Pagination;
use yii\filters\VerbFilter;
use Yii;

class PurchaseOrderCancelController extends BaseController
{
    public $is_pay = [5, 6]; //已付款状态
    // '-1' => '未提交',
    // '0'  => '作废',
    // '1'  => '未申请付款',
    // '2'  => '待财务审批',
    // '3'  => '财务驳回',
    // '4'  => '待财务付款',
    // '5'  => '已付款',
    // '6'  => '已部分付款',
    // '10' => '待采购经理审核',
    // '11' => '经理驳回',
    // '12' => '出纳驳回',
    // '13'=> '富友付款待审核',

    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }
    /**
     * fba审核
     * @return [type] [description]
     */
    public function actionIndex()
    {
        $params = \Yii::$app->request->queryParams;
        $searchModel = new PurchaseOrderCancelSearch();
        $dataProvider = $searchModel->search($params);
        return $this->render('index',[
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }
    /**
     * 海外仓-网采单审核
     */
    public function actionOverseasIndex()
    {
        $params = \Yii::$app->request->queryParams;
        $searchModel = new PurchaseOrderCancelSearch();
        $dataProvider = $searchModel->overseasSearch($params);

        return $this->render('overseas-index',[
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }
    //审核
    public function actionAudit()
    {
        $request = Yii::$app->request;
        if($request->isPost) {
            $data = $request->post();
            $status = $this->saveAudit($data);
            return $this->redirect(Yii::$app->request->referrer);
        } else {
            $get_info  = $request->get();
            $pur_number = $get_info[1]['pur_number'];
            $cancel_id = $get_info[1]['cancel_id'];

            $order_model = new PurchaseOrder();
            $pay_model   = new PurchaseOrderPay();
            $model       = $order_model->findOne(['pur_number' => $pur_number]);
            if(is_null($model)) {
                return '订单信息不存在';
            }

            if(in_array($model->shipfees_audit_status, [0, 2])) {
                return '你有修改过订单信息，还没有审核通过，暂时不能取消未到货';
            }
            $payInfo     = $pay_model->getPayDetail($pur_number);
            $orderInfo   = $order_model->getOrderDetail($pur_number, $payInfo['skuNum']);
            $type        = $model->purchaseOrderPayType;
            $rpt         = $type ? $type->request_type : 0;  // 订单的请款方式

            if($payInfo['countPayMoney'] == 0) {
                $rpt = 0;
            }
            if ( ($model->purchase_type ==2) || ($orderInfo['purchase_type'] ==2) ) {
                return $this->renderAjax('overseas-audit', [
                    'model'       => $model,
                    'orderInfo'   => $orderInfo,
                    'payInfo'     => $payInfo,
                    'rpt'         => $rpt,
                    'cancel_id'   => $cancel_id,
                ]);

            } else {
                return $this->renderAjax('audit', [
                    'model'       => $model,
                    'orderInfo'   => $orderInfo,
                    'payInfo'     => $payInfo,
                    'rpt'         => $rpt,
                    'cancel_id'   => $cancel_id,
                ]);
            }
        }
    }
    /**
     * 保存审核
     */
    public function saveAudit($data)
    {
        $cancelInfo = PurchaseOrderCancel::find()->where(['id'=>$data['cancel_id']])->asArray()->one();
        if ($cancelInfo['audit_status'] == 1) {
            # code...
        } else {
            exit('已审核，不能够重新审核');
        }

        $compactItemsInfo = PurchaseCompactItems::getCancelJudgeCompact($cancelInfo['pur_number'], $data);
        //判断是合同还是网采
        if ( $data['audit_status']==2 && !empty($compactItemsInfo['orderPayInfo'][0]['id']) ) {
            # 合同：审核通过的，合同单，部分付款的，
            return self::_heTongAudit($compactItemsInfo);
        } else {
            # 网采：网采，合同未付款
            return self::_wangCaiAudit($data);
        }
    }
    /**
     * 合同单
     */
    public static function _heTongAudit($data)
    {
        $orderInfo = PurchaseOrder::find()->select('purchase_type, is_new')->where(['pur_number'=>$data['pur_number']])->asArray()->one();
        if ( ($orderInfo['purchase_type']==3) || ($orderInfo['purchase_type']==2 && $orderInfo['is_new']==2) ) {
            /**
             * FBA和海外仓旧版
             * @var [type]
             */
            return self::_fbaHeTongAudit($data);
        } elseif ( $orderInfo['purchase_type']==2 && $orderInfo['is_new']==1 ) {
            /**
             * 海外仓新版
             */
            return self::_newHwHetongAudit($data);
        }
        
        // vd($data);
        
    }
    /**
     * 新海外仓合同部分付款审核
     */
    public static function _newHwHetongAudit($data)
    {
        $return_res = 1;
        $receipt_price = 0;
        $cancel_price = 0; //作废审核通过的金额
        $pur_number = $data['pur_number'];
        $viewCancelInfo = $data['data'];
        $cancel_detail = PurchaseOrderCancelSub::getCancelDetail($data['cancel_id']); //当前取消数量和金额
        $res_ctq = PurchaseOrderCancelSub::cancelCtqBccomp($data['pur_number'],true,$data['cancel_id']); //审核通过和当前的数量 == 采购总数
        $ctq_bccomp = $res_ctq['cancel_purchase_warehouses'];

        PurchaseOrderItems::updateIsCancel($pur_number); //修改采购单子表中的sku是否取消
        if ($ctq_bccomp == 0) {
            # 整个采购单取消：则该采购单生成退款申请单，退款金额为采购单支付定金金额
            $cancelInfo = PurchaseOrderCancel::find()
                ->alias('poc')
                ->joinWith(['purchaseOrderCancelSub'])
                ->where(['poc.pur_number'=>$pur_number])
                ->andWhere(['in', 'audit_status', [1, 2]])
                ->asArray()->all();
            $demand_numbers = []; //需求号-这个采购单所包含的需求号
            $subInfo = array_column($cancelInfo, 'purchaseOrderCancelSub'); //取消详情
            foreach ($subInfo as $k => $v) {
                foreach ($v as $sk => $sv) {
                    $demand_numbers[] = $sv['demand_number'];
                }
            }

            //请款金额
            $mapInfo = OrderPayDemandMap::find()
                ->joinWith(['purchaseOrderPay'])
                ->where(['in', 'demand_number', $demand_numbers])
                ->andWhere(['in', 'pay_status', [5, 6]])
                ->andWhere(['in', 'pay_category', [12, 13, 21]])
                ->asArray()
                ->all();
                
            if (!empty($mapInfo)) {
                $receipt_price = array_sum(array_column($mapInfo, 'pay_amount')); //已付订金金额
            }
        } else {
            #部分取消
            $cancel_detail = PurchaseOrderCancelSub::getCancelDetail($data['cancel_id']); //当前取消数量和金额
            $compactItemsInfo = PurchaseCompactItems::find()->select('pur_number')->where(['compact_number'=>$data['compact_number'], 'bind'=>1])->asArray()->all();
            $pur_numbers = array_column($compactItemsInfo, 'pur_number');
            foreach ($pur_numbers as $v) $cancel_price += PurchaseOrderCancelSub::getCancelPriceOrder($v);
            $cancel_price_total = $cancel_detail['cancel_price_total'] + $cancel_price; //当前取消金额加上之前取消的

            //尾款金额
            //方法一：
            $weiInfo = PurchaseCompactItems::getCancelJudgeCompact($pur_number);
            $pay_categorys = $weiInfo['pay_categorys']; //请款方式
            $pay_ratios = $weiInfo['pay_ratios']; //已付款比例
            $order_total_price = $weiInfo['order_total_price']; //订单总额--出去作废的sku
            $pay_total_price = $weiInfo['pay_total_price']; //已付款总额
            $wei_price = $order_total_price-$pay_total_price; //尾款总额
            //方法二：
            // $select_ratio = PurchaseCompact::getCompactPayPrice($data['compact_number']);
            // $wei_price = !empty(end($select_ratio)['money'])?end($select_ratio)['money']:0;

            if ($cancel_price_total>$wei_price) {
                # 取消金额大于尾款金额：生成退款单，退款金额=取消金额-尾款金额
                $receipt_price = $cancel_price_total-$wei_price; //退款金额
            } else {
                // self::noPay($data['cancel_id'], $data['pur_number']);
                // PurchaseOrderCancel::saveCancel($data,3,1,1);
            }
        }

        //有退款的
        if (!empty($receipt_price)) {
            $post = [
                'is_compact_cancel' => true,
                'cancel_id' => $data['cancel_id'],
                'pur_number' => $data['pur_number'],
                'refund_status' => 3, //3部分退款，4全额退款，10作废
                'money' => $receipt_price, //退款金额
                'freight' => 0, //运费
                'order_freight' => !empty($freight) ? $freight : 0, //运费
                'discount' => 0, //优惠额
                'order_discount' => !empty($discount) ? $discount : 0, //优惠额
                'confirm_note' => '取消未到货退款，取消数量：' . $cancel_detail['cancel_ctq_total'], //退款备注
            ];
            PurchaseOrderCancel::updateAll(['audit_status'=>2], ['id'=>$data['cancel_id']]);
            // $return_res = self::fullFullCancel($post);
        }
        if (!empty($post)) {
            return $post;
        } else {
            return -1;
        }
    }
    public static function _fbaHeTongAudit($data)
    {
        $receipt_price = 0; //退款金额
        $cancel_price = 0; //作废审核通过的金额

        $cancel_detail = PurchaseOrderCancelSub::getCancelDetail($data['cancel_id']); //当前取消数量和金额
        $compactItemsInfo = PurchaseCompactItems::find()->select('pur_number')->where(['compact_number'=>$data['compact_number'], 'bind'=>1])->asArray()->all();
        $pur_numbers = array_column($compactItemsInfo, 'pur_number');
        foreach ($pur_numbers as $v) $cancel_price += PurchaseOrderCancelSub::getCancelPriceOrder($v);
        $res_ctq = PurchaseOrderCancelSub::cancelCtqBccomp($data['pur_number'],true,$data['cancel_id']); //审核通过和当前的数量 == 采购总数
        $res = $res_ctq['cancel_purchase_warehouses'];

        $items_total_price = PurchaseOrderItems::getCountPrice($data['pur_number']); //采购的总金额
        $price_info = PurchaseOrderPayType::getDiscountPrice($data['pur_number']);
        $freight = !empty($price_info['freight']) ?$price_info['freight'] : 0;//运费
        $discount = !empty($price_info['discount']) ?$price_info['discount'] : 0;//优惠
        $cancel_price_total = $cancel_detail['cancel_price_total'] + $cancel_price; //当前取消金额加上之前取消的

        $transaction = Yii::$app->db->beginTransaction();
        try {
            PurchaseOrderItems::updateIsCancel($data['pur_number']); //修改采购单子表中的sku是否取消
            //方法一：
            $compactItemsInfo = PurchaseCompactItems::getCancelJudgeCompact($data['pur_number'], $data);
            $pay_categorys = $compactItemsInfo['pay_categorys']; //请款方式
            $pay_ratios = $compactItemsInfo['pay_ratios']; //已付款比例
            $order_total_price = $compactItemsInfo['order_total_price']; //订单总额--出去作废的sku
            $pay_total_price = $compactItemsInfo['pay_total_price']; //已付款总额
            $wei_price = $order_total_price-$pay_total_price; //尾款总额

            //方法二：获取未作废的sku的总金额
            //$settlement_ratios = explode('+',$price_info['settlement_ratio']);
            // $settlement_ratio = (int)end($settlement_ratios); //尾款比例
            // $noCancelPrice = PurchaseOrderItems::getNoCancelTotalPrice($data['compact_number']);
            // $wei_price = sprintf("%.3f", $noCancelPrice*$settlement_ratio/100); // 尾款总额
            
            //方法三：
            // $orderPayInfo = $data['orderPayInfo'];
            // $pay_radios = array_column($orderPayInfo,'pay_ratio');
            // foreach ($pay_radios as $k => $v) $pay_radios[$k] = (int)$v;
            // $pay_radio = array_sum($pay_radios); //总请款比例
            // $pay_price = array_sum(array_column($orderPayInfo, 'pay_price')); //请款总额
            // $compact_price = sprintf("%.3f", ($pay_price/$pay_radio)*100); //合同总额
            // $wei_price = sprintf("%.3f", $compact_price*$settlement_ratio/100); // 尾款总额
            // $select_ratio = PurchaseCompact::getCompactPayPrice($data['compact_number']);
            // $receipt_price = !empty(end($select_ratio)['money'])?end($select_ratio)['money']:0;
            $already_receipt_price = PurchaseOrderReceipt::find()->where(['pur_number'=>$data['pur_number'], 'pay_status'=>2])->sum('pay_price'); //已收款金额


            if ($res == 0 && !in_array(21,$pay_categorys) ) {
                # 全部取消且不是手动请款的：采购单生成退款申请单，退款金额为采购单支付定金金额
                $pay_radio = array_sum($pay_ratios);  // 已付款比例
                $receipt_price = sprintf("%.3f", $items_total_price*$pay_radio/100); //退款金额
                $receipt_price = $receipt_price-$already_receipt_price;
            } else {
                # 部分取消
                if (!empty($already_receipt_price) && $already_receipt_price>0){
                    #如果有收款记录：则退款金额为取消金额
                    $receipt_price = $cancel_detail['cancel_price_total'];
                }elseif($cancel_price_total>$wei_price) {
                    # 取消金额大于尾款金额：生成退款单，退款金额=取消金额-尾款金额
                    $receipt_price = $cancel_price_total-$wei_price; //退款金额
                } else {
                    self::noPay($data['cancel_id'], $data['pur_number']);
                    PurchaseOrderCancel::saveCancel($data,3,1,1);
                }
            }

            //有退款的
            if (!empty($receipt_price)) {
                $post = [
                    'is_compact_cancel' => true,
                    'cancel_id' => $data['cancel_id'],
                    'pur_number' => $data['pur_number'],
                    'refund_status' => 3, //3部分退款，4全额退款，10作废
                    'money' => $receipt_price, //退款金额
                    'freight' => 0, //运费
                    'order_freight' => !empty($freight) ? $freight : 0, //运费
                    'discount' => 0, //优惠额
                    'order_discount' => !empty($discount) ? $discount : 0, //优惠额
                    'confirm_note' => '取消未到货退款，取消数量：' . $cancel_detail['cancel_ctq_total'], //退款备注
                ];
                PurchaseOrderCancel::updateAll(['audit_status'=>2], ['id'=>$data['cancel_id']]);
                $return_res = self::fullFullCancel($post);
            }
            $transaction->commit();
        } catch (Exception $e) {
            $transaction->rollBack();
            Yii::$app->getSession()->setFlash('error','对不起，操作失败了');
            return -1;
        }
    }
    /**
     * 网采单
     */
    public static function _wangCaiAudit($data)
    {
        if ($data['audit_status'] == 2) {
            //审核通过
            $cancel_info = PurchaseOrderCancel::find()->where(['id'=>$data['cancel_id']])->one();
            $purchase_status = PurchaseOrderPay::getOrderPayStatus($cancel_info['pur_number']); // 获取订单所有支付单的支付状态
            $cancel_detail = PurchaseOrderCancelSub::getCancelDetail($data['cancel_id']); //当前取消数量和金额
            $items_ctq_total = PurchaseOrderItems::getCtqTotal($cancel_info['pur_number']); //总的采购数量
            $totalPay = PurchaseOrderPay::getOrderPaidMoney($cancel_info['pur_number']); //财务付款总金额（6部分付款 5已付款）
            $items_total_price = PurchaseOrderItems::getCountPrice($cancel_info['pur_number']); //采购的总金额
            $cancel_freight = PurchaseOrderCancel::getFreight($data['cancel_id']); //取消的运费
            $cancel_discount = PurchaseOrderCancel::getDiscount($data['cancel_id']); //取消的优惠额

            //优惠和运费
            $price_info = PurchaseOrderPayType::getDiscountPrice($cancel_info['pur_number']);

            $freight = !empty($price_info['freight']) ?$price_info['freight'] : 0;
            $discount = !empty($price_info['discount']) ?$price_info['discount'] : 0;
            $total_pay_res = $totalPay + $freight - $discount;

            $order_total_price = $items_total_price - $discount + $freight; //订单总金额
            $pay_price = bccomp($total_pay_res,$order_total_price,3);

            $res_ctq = PurchaseOrderCancelSub::cancelCtqBccomp($cancel_info['pur_number'],true,$data['cancel_id']); //审核通过和当前的数量 == 采购总数
            $res = $res_ctq['cancel_purchase'];

            if ($totalPay <= 0) {
                # 未付款订单：全部取消：作废(减在途)
                self::noPay($data['cancel_id'],$cancel_info['pur_number']);
            } elseif ($pay_price == 0 || $pay_price==1 || $pay_price == -1) {
                # 全额付款
                if ($res == 0) {// 全部取消  fullFullCancel -》订单状态（代收款）-》退款（应收）-》财务收款后-》订单状态：作废
                    //当前取消的数量 = 订单采购数量  //全部退款
                    if ($cancel_detail['cancel_ctq_total'] == $items_ctq_total) {// 一次性全部取消：全额退款 退款金额=支付金额（减在途：ok）
                        $refund_status = 4;//3部分退款，4全额退款，10作废
                    } else {// 多次后全部取消：全额退款 退款金额=取消金额（？？减在途：1.减去当前，2.系统当初是减去所有未到货的）
                        $refund_status = 3;//3部分退款，4全额退款，10作废
                    }
                } else {// 部分取消  fullPartCancel  -》订单状态（代收款）-》退款（应收）-》财务收款后-》订单状态根据剩下的状态来变（？？）
                    $refund_status = 3;//3部分退款，4全额退款，10作废（？？减在途：1.减去当前，2.系统当初是减去所有未到货的）
                }
            } elseif ($pay_price == -1) {
                #部分付款
            } 
            if (!empty($refund_status)) {
                $post = [
                    'cancel_id' => $data['cancel_id'],
                    'pur_number' => $cancel_info['pur_number'],
                    'refund_status' => $refund_status, //3部分退款，4全额退款，10作废
                    'money' => $cancel_detail['cancel_price_total'], //退款金额
                    'freight' => !empty($cancel_freight) ? $cancel_freight : 0, //运费
                    'order_freight' => !empty($freight) ? $freight : 0, //运费
                    'discount' => !empty($cancel_discount) ? $cancel_discount : 0, //优惠额
                    'order_discount' => !empty($discount) ? $discount : 0, //优惠额
                    'confirm_note' => '取消未到货退款，取消数量：' . $cancel_detail['cancel_ctq_total'], //退款备注
                ];
                $return_res = self::fullFullCancel($post);
            }
        }
        if (!empty($return_res) && $return_res == -1) {
        } else {
            return PurchaseOrderCancel::saveCancel($data,3,1,1);
        }
    }
    /**
     * 未付款订单处理
     */
    public static function noPay($cancel_id, $pur_number)
    {

        $cancelCtqBccomp = PurchaseOrderCancelSub::cancelCtqBccomp($pur_number,true,$cancel_id);
        $res_ctq = $cancelCtqBccomp['cancel_purchase_warehouses'];
        $transaction = Yii::$app->db->beginTransaction();
        try {
            //比较 取消数量  和  采购数量-入库数量
            if ($res_ctq == 0) {
                //全部取消：作废？？这里应该要减在途（is_push=0,audit_status=2）
                $update_order_data['pay_status'] = 0;
                $order_info = PurchaseOrder::find()->where(['pur_number'=>$pur_number])->asArray()->one();
                if ($order_info['purchas_status'] == 8) {
                    $update_order_data['purchas_status'] = 9;
                } else {
                    $update_order_data['purchas_status'] = 10;
                    //缺货列表更新处理状态
                    $demand_numbers = PurchaseDemand::find()->select('demand_number')->where(['pur_number'=>$pur_number])->column();
                    AmazonOutofstockOrder::updateAll(['status'=>0,'is_show'=>1,'demand_number'=>''],['in','demand_number',$demand_numbers]);
                    //is_purchase=1未采购 is_push=0未推送 source=2erp  purchase_type=3FBA
                    PlatformSummary::updateAll(['level_audit_status'=>5,'is_purchase'=>1,'is_push'=>0],['and',['in','demand_number',$demand_numbers],['source'=>2,'purchase_type'=>3]]);

                    PlatformSummary::updateAll(['is_purchase'=>1,'is_push'=>0],['and',['in','demand_number',$demand_numbers],['source'=>1,'purchase_type'=>3]]);
                    PurchaseDemand::deleteRelation($pur_number);

                }
                PurchaseOrder::updateAll($update_order_data,['pur_number'=>$pur_number]); //订单表
                PurchaseOrderPay::updateAll(['pay_status'=>0],['pur_number'=>$pur_number]); //支付表
            }

            PurchaseOrderCancel::updateAll(['is_push'=>0],['id'=>$cancel_id]);
            PurchaseOrderItems::updateIsCancel($pur_number); //修改采购单子表中的sku是否取消
            $transaction->commit();
            Yii::$app->getSession()->setFlash('success','恭喜你，操作成功了');
        } catch (Exception $e) {
            $transaction->rollBack();
            Yii::$app->getSession()->setFlash('error','对不起，未付款的订单操作失败了');
            return -1;
        }
    }

    /**
     * 全额付款-全部取消-作废
     */
    public static function fullFullCancel($post)
    {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $model_receipt = new PurchaseOrderReceipt(); // 采购单收款表
            $pur_number = $post['pur_number'];
            $result = $model_receipt->verifyRefundEvent($post);

            if($result['error'] == 1 && empty($post['is_compact_cancel'])) {
                Yii::$app->getSession()->setFlash('error',$result['message']);
                return -1;
            }
            $order_s = PurchaseOrder::find()->where(['pur_number' => $pur_number])->one();

            // 部分退款（可多次申请）
            if($post['refund_status'] == 3 && !empty($post['money'])) {
                $order_s->refund_status = 3;
                $data = [
                    'pur_number'        => $pur_number,
                    'supplier_code'     => $order_s->supplier_code,
                    'settlement_method' => $order_s->account_type,
                    'pay_type'          => $order_s->pay_type,
                    'currency_code'     => $order_s->currency_code,
                    'pay_price'         => $post['money'],
                    'applicant'         => Yii::$app->user->id,
                    'application_time'  => date('Y-m-d H:i:s'),
                    'review_notice'     => $post['confirm_note'],
                    'pay_name'          => '供应商退款',
                    'step'              => 3,
                    'cancel_id'              => !empty($post['cancel_id'])?$post['cancel_id'] : null,
                ];
                $a = $model_receipt->insertRow($data);
                $b = $order_s->save(false);
                if ($b == true) {
                } else {
                    $b = $order_s->save();
                }
                $log = [
                    'pur_number' => $pur_number,
                    'note' => "部分退款，退款金额 {$data['pay_price']}",
                ];
                PurchaseLog::addLog($log);
                $is_submit = $a && $b;
                // 全额退款（只能退一次）
            } elseif($post['refund_status'] == 4 && !empty($post['money'])) {

                $order_s->refund_status = 4;
                $order_s->confirm_note  = $post['confirm_note'];
                $data = [
                    'pur_number'        => $pur_number,
                    'supplier_code'     => $order_s->supplier_code,
                    'settlement_method' => $order_s->account_type,
                    'pay_type'          => $order_s->pay_type,
                    'currency_code'     => $order_s->currency_code,
                    'pay_price'         => $post['money'],
                    'applicant'         => Yii::$app->user->id,
                    'application_time'  => date('Y-m-d H:i:s'),
                    'review_notice'     => $post['confirm_note'],
                    'pay_name'          => '供应商退款',
                    'step'              => 4,
                    'cancel_id'              => !empty($post['cancel_id'])?$post['cancel_id'] : null,
                ];
                $a = $model_receipt->saveOne($data);
                $b = $order_s->save(false);
                $log = [
                    'pur_number' => $pur_number,
                    'note' => "全额退款，退款金额 {$data['pay_price']}",
                ];
                PurchaseLog::addLog($log);
                $is_submit = $a && $b;
            } elseif($post['refund_status'] == 10) {
                //更新采购单不再去alibaba拉去物流记录了
                $or = PurchaseOrderOrders::find()->where(['pur_number' => $pur_number])->one();
                if($or) {
                    $or->is_request =1;
                    $or->save();
                }

                // 操作日志
                $datas = [];
                $msg              = '在' . date('Y-m-d H:i:s') . '由' . Yii::$app->user->identity->username . '=====' . $post['pur_number'] . '进行了作废';
                $datas['type']    = 12;
                $datas['pid']     = '';
                $datas['module']  = '采购单作废';
                $datas['content'] = $msg;
                Vhelper::setOperatLog($datas);

                // 更新付款状态为作废
                $a = PurchaseOrderPay::updatePayStatus($pur_number, 0);

                //缺货列表更新处理状态
                $demand_numbers = PurchaseDemand::find()->select('demand_number')->where(['pur_number'=>$pur_number])->column();
                AmazonOutofstockOrder::updateAll(['status'=>0,'is_show'=>1,'demand_number'=>''],['in','demand_number',$demand_numbers]);
                PlatformSummary::updateAll(['level_audit_status'=>5,'is_purchase'=>1,'is_push'=>0],['and',['in','demand_number',$demand_numbers],['source'=>2,'purchase_type'=>3]]);
                // 更新中间表
                PlatformSummary::updateAll(['is_purchase'=>1,'is_push'=>0],['and',['in','demand_number',$demand_numbers],['source'=>1,'purchase_type'=>3]]);
                PurchaseDemand::deleteRelation($pur_number);


                // 作废采购单重新推送
                $order_s->is_push        = 0;
                $order_s->confirm_note   = $post['confirm_note'];
                $order_s->purchas_status = 10;
                $b = $order_s->save(false);
                $is_submit = $a && $b;
            } else {
                $transaction->rollBack();
                Yii::$app->getSession()->setFlash('error','对不起，你提交的数据有误1');
                return -1;
            }
            if($is_submit) {
                PurchaseOrderItems::updateIsCancel($pur_number); //修改采购单子表中的sku是否取消
                $transaction->commit();
                Yii::$app->getSession()->setFlash('success','恭喜你，操作成功了');
                return 1;
            } else {
                $transaction->rollBack();
                Yii::$app->getSession()->setFlash('error','对不起，操作失败了');
                return -1;
            }
        } catch (Exception $e) {
            $transaction->rollBack();
            Yii::$app->getSession()->setFlash('error','对不起，操作失败了');
            return -1;
        }
    }
    /**
     * 查看
     */
    public function actionView()
    {
        $request = Yii::$app->request;
        $get_info  = $request->get();
        $pur_number = $get_info[1]['pur_number'];
        $cancel_id = $get_info[1]['cancel_id'];

        $order_model = new PurchaseOrder();
        $pay_model   = new PurchaseOrderPay();
        $model       = $order_model->findOne(['pur_number' => $pur_number]);
        if(is_null($model)) {
            return '订单信息不存在';
        }

        if(in_array($model->shipfees_audit_status, [0, 2])) {
            return '你有修改过订单信息，还没有审核通过，暂时不能请款';
        }
        $payInfo     = $pay_model->getPayDetail($pur_number);
        $orderInfo   = $order_model->getOrderDetail($pur_number, $payInfo['skuNum']);
        $type        = $model->purchaseOrderPayType;
        $rpt         = $type ? $type->request_type : 0;  // 订单的请款方式

        if($payInfo['countPayMoney'] == 0) {
            $rpt = 0;
        }

        if ( ($model->purchase_type ==2) || ($orderInfo['purchase_type'] ==2) ) {
            return $this->renderAjax('overseas-view', [
                'model'       => $model,
                'orderInfo'   => $orderInfo,
                'payInfo'     => $payInfo,
                'rpt'         => $rpt,
                'cancel_id'   => $cancel_id,
            ]);
        } else {
            return $this->renderAjax('view', [
                'model'       => $model,
                'orderInfo'   => $orderInfo,
                'payInfo'     => $payInfo,
                'rpt'         => $rpt,
                'cancel_id'   => $cancel_id,
            ]);
        }
    }
    /**
     * 删除
     */
    public function actionDeleteCancel()
    {
        $id = Yii::$app->request->get('id');

        $transaction = Yii::$app->db->beginTransaction();
        try {
            $cancel_model = PurchaseOrderCancel::find()->where(['id' => $id])->asArray()->one();
            $allow_delete = [3,4];
            if ( !in_array($cancel_model['audit_status'], $allow_delete) ) {
                Yii::$app->getSession()->setFlash('error','只有驳回的才能够删除');
                return $this->render(Yii::$app->request->referrer);
            }
            if (!empty($cancel_model['requisition_number'])) {
                PurchaseOrderReceipt::deleteAll(['requisition_number'=>$cancel_model['requisition_number']]);
            }
            PurchaseOrderCancelSub::deleteAll(['cancel_id' => $id]);
            $this->findModel($id)->delete();
            $transaction->commit();
            Yii::$app->getSession()->setFlash('success','删除成功');
        } catch(\Exception $e) {
            Yii::$app->getSession()->setFlash('error','删除失败');
            $transaction->rollBack();
        }
        return $this->redirect(Yii::$app->request->referrer);
    }

    /**
     * @param $id
     * @return mixed
     */
    protected function findModel($id)
    {
        if (($model = PurchaseOrderCancel::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}