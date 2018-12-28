<?php
namespace app\controllers;
use app\config\Vhelper;
use app\models\PurchaseOrder;
use app\models\PurchaseOrdersV2;
use Yii;
use app\models\PurchaseOrderPay;
use app\models\PurchaseOrderItems;
use app\models\PurchaseOrderPayType;
use app\models\PurchaseOrderPaySearch;
use app\services\SupplierServices;
use app\services\BaseServices;
use app\services\PurchaseOrderServices;
use app\controllers\BaseController;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use app\models\PurchaseLog;
use m35\thecsv\theCsv;
use app\models\PurchaseCompact;
use app\models\PurchaseCompactItems;
use app\models\OrderPayDemandMap;
use app\models\PlatformSummary;
class PurchaseOrderPayNotificationController extends BaseController
{

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

    // 付款通知首页
    public function actionIndex()
    {
        $args = Yii::$app->request->queryParams;
        $searchModel = new PurchaseOrderPaySearch();
        $searchModel->source = isset($args['source']) ? (int)$args['source'] : 1;
        if(in_array($searchModel->source, [1, 2, 3])) {
            $dataProvider = $searchModel->search3($args);
            if(isset($args['order_account'])){
                $searchModel->order_account = $args['order_account'];
            }
            return $this->render('index', [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
                'source' => $searchModel->source
            ]);
        } else {
            throw new \yii\web\NotFoundHttpException;
        }
    }

    // 财务审批请款单-合同请款
    public function actionCompactFinanceAudit()
    {
        $request = Yii::$app->request;
        if($request->isPost) {
            $post = $request->post();
            $id = $post['id'];
            $status = (int)$post['status'];
            $tran = Yii::$app->db->beginTransaction();
            try {
                $model = PurchaseOrderPay::findOne($id);
                if ($model->pay_status == 2) {
                    
                } else {
                    Yii::$app->getSession()->setFlash('error','对不起，当前的付款状态不能够审核');
                    return $this->redirect(Yii::$app->request->referrer);
                }
                $model->payment_notice = $post['payment_notice'] ? $post['payment_notice'] : '财务审批';
                $model->pay_status = $status;
                $model->approver = Yii::$app->user->id;
                $model->processing_time = date('Y-m-d H:i:s', time());
                if($model->source == 1 && preg_match('/HT/', $model->pur_number)) { // 合同类请款单，同时更新合同下所有采购的支付状态
                    $pos = PurchaseCompact::getPurNumbers($model->pur_number);
                    if($pos) {
                        PurchaseOrder::updateAll(['pay_status' => $status], ['in', 'pur_number', $pos]);
                    }
                    $logMsg = '财务审批了合同单的请款>'.$id;
                } else {
                    exit('非合同单，不走这个流程');
                }
                PurchaseLog::addLog([
                    'pur_number' => $model->pur_number,
                    'note' => $logMsg
                ]);
                $model->save(false);
                
                //海外仓NEW流程
                $demand_numbers = OrderPayDemandMap::find()->select('demand_number')->where(['requisition_number'=>$model->requisition_number])->column();
                if ($demand_numbers) {
                    //海外仓NEW流程-请款状态
                    PlatformSummary::updateAll(['pay_status'=>$model->pay_status], ['in','demand_number',$demand_numbers]);
                    $message_result = $model->pay_status == 4 ? '通过' : '驳回';
                    $message = "请款单财务审批【{$message_result}】\r\n审批备注:{$post['payment_notice']}\r\n请款单:".$model->requisition_number;
                    PurchaseOrderServices::writelog($demand_numbers, $message);
                }

                $tran->commit();
                Yii::$app->getSession()->setFlash('success','恭喜你，审批成功');
                return $this->redirect(Yii::$app->request->referrer);
            } catch(\Exception $e) {
                $tran->rollback();
                Yii::$app->getSession()->setFlash('error','对不起，审批失败');
                return $this->redirect(Yii::$app->request->referrer);
            }
        } else {
            $request = Yii::$app->request;
            $id = $request->get('id');
            $model = PurchaseOrderPay::findOne($id);
            $data = PurchaseOrderPay::getCompactPayDataV2($model);
            $compact = PurchaseCompact::find()
                ->select([
                    'supplier_name',
                    'settlement_ratio',
                    'product_money',
                    'freight',
                    'discount',
                    'real_money',
                    'dj_money',
                    'wk_money',
                    'wk_total_money',
                    'is_drawback',
                    'create_time',
                    'create_person_name'
                ])
                ->where(['compact_number' => $model->pur_number])
                ->one();
            if(!$data || !$compact) {
                return '请款单或合同不存在';
            }
            return $this->renderAjax('compact-finance-audit', ['model' => $model, 'data' => $data, 'compact' => $compact]);
        }
    }


    // 财务审批请款单（老版本）
    public function actionFinanceAudit()
    {
        $request = Yii::$app->request;
        if($request->isPost) {
            $post = $request->post();
            $id = $post['id'];
            $tran = Yii::$app->db->beginTransaction();
            try {
                $pay = PurchaseOrderPay::findOne($id);
                $order = PurchaseOrder::findOne(['pur_number' => $pay->pur_number]);
                $notice = ($post['processing_notice'] !== '') ? $post['processing_notice'] : '财务审批';
                $pay->payment_notice = $notice;
                $pay->pay_status = $post['pay_status'];
                $pay->approver = Yii::$app->user->id;
                $pay->processing_time = date('Y-m-d H:i:s', time());
                $order->pay_status = $post['pay_status'];

                if($pay->pay_status == 3) {
                    $i = '没有通过';
                } else {
                    $i = '通过了';
                }

                PurchaseLog::addLog([
                    'pur_number' => $pay->pur_number,
                    'note' => '财务审批请款单>'.$id.$i
                ]);
                $pay->save(false);
                $order->save(false);
                
                //海外仓NEW流程
                $demand_numbers = OrderPayDemandMap::find()->select('demand_number')->where(['requisition_number'=>$pay->requisition_number])->column();
                if ($demand_numbers) {
                    //海外仓NEW流程-请款状态
                    PlatformSummary::updateAll(['pay_status'=>$pay->pay_status], ['in','demand_number',$demand_numbers]);
                    $message_result = $pay->pay_status == 4 ? '通过' : '驳回';
                    $message = "请款单财务审批【{$message_result}】\r\n审批备注:{$post['processing_notice']}\r\n请款单:".$pay->requisition_number;
                    PurchaseOrderServices::writelog($demand_numbers, $message);
                }

                $tran->commit();
                return json_encode(['error' => 0, 'message' => 'success']);
            } catch(\Exception $e) {
                $tran->rollback();
                return json_encode(['error' => 1, 'message' => 'error']);
            }
        } else {
            $id = $request->get('id');
            $data = $this->getPayInfo($id);
            if(!$data) {
                return '请款单所属的订单数据不存在';
            }
            return $this->renderAjax('finance-audit', $data);
        }
    }

    //付款通知批量驳回
    public function actionBatchReject(){
        if (Yii::$app->request->isPost&&Yii::$app->request->isAjax)
        {
            $ids = Yii::$app->request->getBodyParam('ids');
            $payment_notice  = Yii::$app->request->getBodyParam('payment_notice');
            $source = Yii::$app->request->getBodyParam('source');

            $res = ['status'=>1,'msg'=>''];
            if($source == 1 && !empty($ids)) {
                foreach ($ids as $id) {
                    $tran = Yii::$app->db->beginTransaction();
                    try {
                        $model                  = PurchaseOrderPay::findOne($id);
                        if($model && $model->pay_status != 2){
                            $tran->rollback();
                            $res['status'] = 0;
                            $res['msg'] = $model->requisition_number.':本次支付的请款单不在待财务审批状态,不能驳回';
                            die(json_encode($res));
                        }
                        $model->payment_notice  = $payment_notice ? strip_tags($payment_notice) : '财务审批';
                        $model->pay_status      = 3;
                        $model->approver        = Yii::$app->user->id;
                        $model->processing_time = date('Y-m-d H:i:s', time());
                        if ($source == 1 && preg_match('/HT/', $model->pur_number)) { // 合同类请款单，同时更新合同下所有采购的支付状态
                            $pos = PurchaseCompact::getPurNumbers($model->pur_number);
                            if ($pos) {
                                PurchaseOrder::updateAll(['pay_status' => 3], ['in', 'pur_number', $pos]);
                            }
                            $logMsg = '财务审批了合同单的请款>' . $id;
                        } else {
                            $res['status'] = 0;
                            $res['msg'] = '非合同单，不走这个流程';
                            die(json_encode($res));
                        }
                        PurchaseLog::addLog([
                            'pur_number' => $model->pur_number,
                            'note' => $logMsg
                        ]);
                        $model->save(false);

                        //海外仓NEW流程
                        $demand_numbers = OrderPayDemandMap::find()->select('demand_number')->where(['requisition_number'=>$model->requisition_number])->column();
                        if ($demand_numbers) {
                            //海外仓NEW流程-请款状态
                            PlatformSummary::updateAll(['pay_status'=>$model->pay_status], ['in','demand_number',$demand_numbers]);
                        }

                        $tran->commit();
                    } catch (\Exception $e) {
                        $tran->rollback();
                        $res['status'] = 0;
                        $res['msg'] = '对不起,批量驳回失败';
                        die(json_encode($res));
                    }
                }
                $res['msg'] = '恭喜你,批量驳回成功';
                die(json_encode($res));
            } else {
                foreach ($ids as $id) {
                    $tran = Yii::$app->db->beginTransaction();
                    try {
                        $pay                  = PurchaseOrderPay::findOne($id);
                        if($pay && $pay->pay_status != 2){
                            $tran->rollback();
                            $res['status'] = 0;
                            $res['msg'] = $pay->requisition_number.':本次支付的请款单不在待财务审批状态,不能驳回';
                            die(json_encode($res));
                        }
                        $order                = PurchaseOrder::findOne(['pur_number' => $pay->pur_number]);
                        $notice               = ($payment_notice !== '') ? strip_tags($payment_notice) : '财务审批';
                        $pay->payment_notice  = $notice;
                        $pay->pay_status      = 3;
                        $pay->approver        = Yii::$app->user->id;
                        $pay->processing_time = date('Y-m-d H:i:s', time());
                        $order->pay_status    = 3;

                        $i = '没有通过';
                        PurchaseLog::addLog([
                            'pur_number' => $pay->pur_number,
                            'note' => '财务审批请款单>' . $id . $i
                        ]);
                        $pay->save(false);
                        $order->save(false);

                        //海外仓NEW流程-请款状态
                        $demand_numbers = OrderPayDemandMap::find()->select('demand_number')->where(['requisition_number'=>$pay->requisition_number])->column();
                        if ($demand_numbers) {
                            //海外仓NEW流程-请款状态
                            PlatformSummary::updateAll(['pay_status'=>$pay->pay_status], ['in','demand_number',$demand_numbers]);
                        }

                        $tran->commit();
                    } catch (\Exception $e) {
                        $tran->rollback();
                        $res['status'] = 0;
                        $res['msg'] = '对不起,批量驳回失败';
                        die(json_encode($res));
                    }
                }
                $res['msg'] = '恭喜你,批量驳回成功';
                die(json_encode($res));
            }
        }
    }

    // 获取请款单信息
    public function getPayInfo($id)
    {
        $pay_model = new PurchaseOrderPay();
        $pay_info = $pay_model->getPayDetailById($id);
        if(!$pay_info)
            return null;
        $skuNum = [];
        if(isset($pay_info['items'])) {
            foreach($pay_info['items'] as $k => $v) {
                $skuNum[$v['sku']] = $v['num'];
            }
        }
        $order_model = new PurchaseOrder();
        $order_info = $order_model->getOrderDetail($pay_info['pur_number'], $skuNum,$pay_info['requisition_number']);
        if(!$order_info)
            return null;
        $order_info['rpt'] = PurchaseOrderPayType::getPayType($pay_info['pur_number']);

        // 获取 请款总金额、运费、优惠
        $model = PurchaseOrderPay::findOne($id);
        $price_list = PurchaseOrderPay::getPrice($model,false,$model->source,true);
        $pay_info['pay_price'] = $price_list['final_money'];
        $pay_info['freight'] = $price_list['freight'];
        $pay_info['discount'] = $price_list['discount'];

        return ['payInfo' => $pay_info, 'orderInfo' => $order_info];
    }

    /**
     * 批量审批
     * @return string|\yii\web\Response
     */
    public function actionAllApproval()
    {
        $request = Yii::$app->request;
        if($request->isAjax) {
            $ids = $request->post('ids');
            if(!$ids) {
                return '你没有选择要审核的请款单数据';
            }
            $models = PurchaseOrderPay::findAll($ids);
            return $this->renderAjax('approval', ['models' => $models]);
        } else {
            $data = $request->post();
            $ids = isset($data['ids']) ? $data['ids'] : [];
            $is_pay_status = PurchaseOrderPay::find()->select('pay_status')->where(['in', 'id', $ids])->asArray()->all();
            $is_pay_status = array_unique(array_column($is_pay_status, 'pay_status'));
            if (count($is_pay_status) == 1 && !empty($is_pay_status[0]) && $is_pay_status[0] == 2) {
                # code...
            } else {
                Yii::$app->getSession()->setFlash('error','审批失败，请款单状态不对');
                return $this->redirect(Yii::$app->request->referrer);
            }
            $review_notice = isset($data['review_notice']) ? $data['review_notice'] : '批量审批';
            if(empty($ids)) {
                Yii::$app->getSession()->setFlash('error','审批失败，数据提交有误');
                return $this->redirect(Yii::$app->request->referrer);
            }
            $update = [
                'pay_status'      => 4, // 待财务付款
                'processing_time' => date('Y-m-d H:i:s', time()),
                'payment_notice'  => $review_notice,
                'approver'        => Yii::$app->user->id,
            ];
            $res = PurchaseOrderPay::updateAll($update, ['in', 'id', $ids]);
            foreach($data['pur_number'] as $v) {
                $s = [
                    'pur_number' => $v,
                    'note'       => '批量审批',
                ];
                PurchaseLog::addLog($s);
            }
            
            //海外仓NEW流程
            $requisition_numbers = PurchaseOrderPay::find()->select('requisition_number')->where(['in','id',$ids])->column();
            $demand_models = OrderPayDemandMap::find()->where(['in','requisition_number',$requisition_numbers])->all();
            if ($demand_models) {
                foreach ($demand_models as $demand_model) {
                    PlatformSummary::updateAll(['pay_status'=>4], ['demand_number'=>$demand_model->demand_number]);
                    $message = "请款单财务审批【通过】\r\n审批备注:{$review_notice}\r\n请款单:".$demand_model->requisition_number;
                    PurchaseOrderServices::writelog($demand_model->demand_number, $message);
                }
            }
            
            if($res) {
                Yii::$app->getSession()->setFlash('success','恭喜你，审核成功');
                return $this->redirect(Yii::$app->request->referrer);
            } else {
                Yii::$app->getSession()->setFlash('error','对不起，审批失败');
                return $this->redirect(Yii::$app->request->referrer);
            }
        }
    }

    /**
     * 导出csv
     * @return \yii\web\Response
     * @throws \yii\web\HttpException
     */
    public function actionExportCsv()
    {
        //以写入追加的方式打开
        $daterangepicker_start = Yii::$app->request->get('daterangepicker_start');
        $daterangepicker_end = Yii::$app->request->get('daterangepicker_end');
        $id = Yii::$app->request->get('ids');
        $pay_status = Yii::$app->request->get('pay_status');

        $id = strpos($id, ',') ? explode(',', $id) : $id;
        $pay_status = strpos($pay_status, ',') ? explode(',', $pay_status) : $pay_status;

        if (!empty($id) && $daterangepicker_start != null) {
            $data = ['and',['in','pur_purchase_order_pay.id',$id],['between', 'pur_purchase_order_pay.application_time', $daterangepicker_start, $daterangepicker_end]];
        } elseif (!empty($id) && $daterangepicker_start == null) {
            $data = ['in','pur_purchase_order_pay.id',$id];
        } elseif($pay_status !='' && $daterangepicker_start != null) {
            $data = ['and',['in', 'pur_purchase_order_pay.pay_status',$pay_status],['between', 'pur_purchase_order_pay.application_time', $daterangepicker_start, $daterangepicker_end]];
        } elseif ($pay_status ==''  && $daterangepicker_start != null) {
            $data = ['and',['in', 'pur_purchase_order_pay.pay_status',[2,3,4,5,6]],['between', 'pur_purchase_order_pay.application_time', $daterangepicker_start, $daterangepicker_end]];
        } else{
            Yii::$app->getSession()->setFlash('error','请选择申请时间',true);
            return $this->redirect(['index']);
        }

        $model = PurchaseOrderPay::find()
            ->joinWith(['purchaseOrder','orderNote','supplier','purchaseOrders'])
            ->where($data)
//            ->createCommand()->getRawSql();
            ->asArray()
            ->all();

        if (empty($model)) {
            Yii::$app->getSession()->setFlash('error','在指定时间区域内，找不到你所需要的数据',true);
            return $this->redirect(['index']);
        }

        $table = [
//            'ID',
            '序号',
            'SKU',
            '产品名称（中文名称）',
            '付款状态',
            '采购单号',
            '申请单号',
            '供应商',
            '结算方式',
            '名称',
            '金额',
            '创建备注',
            '申请人',
            '审核人',
            '审批人',
            '申请时间',
            '审核时间',
            '支付方式',
            '币种',
            '审核备注',
            '费用类型',
            '付款人',
            '支付周期',
            '付款备注',
//            '采购需求建立时间',
            '采购单生成时间',
            '财务审核时间',
            '财务付款时间',
        ];

        $m=1;
        $table_head = [];
        foreach ($model as $k => $v) {
            if (!empty($v['purchaseOrders'])) {
                foreach ($v['purchaseOrders'] as $c => $vb) {
//                    $table_head[$k][$c][] = $v['id'];
                    $table_head[$k][$c][] = $m;
                    $table_head[$k][$c][] = !empty($vb['sku'])?$vb['sku']:'';
                    $table_head[$k][$c][] = !empty($vb['name'])?$vb['name']:'';
                    $table_head[$k][$c][] = strip_tags(PurchaseOrderServices::getPayStatus($v['pay_status']));
                    $table_head[$k][$c][] = $v['pur_number'];
                    $table_head[$k][$c][] = $v['requisition_number'];
                    $table_head[$k][$c][] = $v['supplier']['supplier_name'];
                    $table_head[$k][$c][] = !empty($v['settlement_method']) ? SupplierServices::getSettlementMethod($v['settlement_method']) : '';
                    $table_head[$k][$c][] = $v['pay_name'];
                    $table_head[$k][$c][] = $v['pay_price'];
                    $table_head[$k][$c][] = $v['orderNote']['note'];  //创建备注
                    $table_head[$k][$c][] = !empty($v['applicant']) ? BaseServices::getEveryOne($v['applicant']) : '';
                    $table_head[$k][$c][] = !empty($v['auditor']) ? BaseServices::getEveryOne($v['auditor']) : '';
                    $table_head[$k][$c][] = !empty($v['approver']) ? BaseServices::getEveryOne($v['approver']) : '';
                    $table_head[$k][$c][] = $v['application_time'];
                    $table_head[$k][$c][] = $v['review_time'];
                    $table_head[$k][$c][] = !empty($v['pay_type']) ? SupplierServices::getDefaultPaymentMethod($v['pay_type']) : '';
                    $table_head[$k][$c][] = $v['currency'];
                    $table_head[$k][$c][] = $v['review_notice'];
                    $table_head[$k][$c][] = $v['cost_types'];
                    $table_head[$k][$c][] = !empty($v['payer']) ? BaseServices::getEveryOne($v['payer']) : '';
                    $table_head[$k][$c][] = $v['payment_cycle'];
                    $table_head[$k][$c][] = $v['payment_notice'];
//                    $table_head[$k][$c][] = !empty($vb['create_time'])?$vb['create_time']:'';
                    $table_head[$k][$c][] = $v['purchaseOrder']['created_at'];
                    $table_head[$k][$c][] = $v['processing_time'];
                    $table_head[$k][$c][] = $v['payer_time'];

                    $m++;
                }
            } else {
//                $table_head[$k][$k][] = $v['id'];
                $table_head[$k][$k][] = $m;
                $table_head[$k][$k][] = '';
                $table_head[$k][$k][] = '';
                $table_head[$k][$k][] = strip_tags(PurchaseOrderServices::getPayStatus($v['pay_status']));
                $table_head[$k][$k][] = $v['pur_number'];
                $table_head[$k][$k][] = $v['requisition_number'];
                $table_head[$k][$k][] = $v['supplier']['supplier_name'];
                $table_head[$k][$k][] = !empty($v['settlement_method']) ? SupplierServices::getSettlementMethod($v['settlement_method']) : '';
                $table_head[$k][$k][] = $v['pay_name'];
                $table_head[$k][$k][] = $v['pay_price'];
                $table_head[$k][$k][] = $v['orderNote']['note'];  //创建备注
                $table_head[$k][$k][] = !empty($v['applicant']) ? BaseServices::getEveryOne($v['applicant']) : '';
                $table_head[$k][$k][] = !empty($v['auditor']) ? BaseServices::getEveryOne($v['auditor']) : '';
                $table_head[$k][$k][] = !empty($v['approver']) ? BaseServices::getEveryOne($v['approver']) : '';
                $table_head[$k][$k][] = $v['application_time'];
                $table_head[$k][$k][] = $v['review_time'];
                $table_head[$k][$k][] = !empty($v['pay_type']) ? SupplierServices::getDefaultPaymentMethod($v['pay_type']) : '';
                $table_head[$k][$k][] = $v['currency'];
                $table_head[$k][$k][] = $v['review_notice'];
                $table_head[$k][$k][] = $v['cost_types'];
                $table_head[$k][$k][] = !empty($v['payer']) ? BaseServices::getEveryOne($v['payer']) : '';
                $table_head[$k][$k][] = $v['payment_cycle'];
                $table_head[$k][$k][] = $v['payment_notice'];
//                $table_head[$k][$k][] = '';
                $table_head[$k][$k][] = $v['purchaseOrder']['created_at'];
                $table_head[$k][$k][] = $v['processing_time'];
                $table_head[$k][$k][] = $v['payer_time'];

                $m++;
            }
        }

        theCsv::export([
            'header' =>$table,
            'data' => Vhelper::ThereArrayTwo($table_head),
            'name' => '采购单支付表--' . date('Y-m-d') . '.csv',  //Excel表名字
        ]);
        die;
    }

}
