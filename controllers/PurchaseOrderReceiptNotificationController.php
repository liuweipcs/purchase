<?php

namespace app\controllers;

use app\config\Vhelper;
use app\models\PurchaseNote;
use app\models\PurchaseOrder;
use app\models\PurchaseOrderCancel;
use app\models\PurchaseOrderCancelSub;
use app\models\PurchaseOrderItems;
use app\models\PurchaseOrderPay;
use app\models\PurchaseOrderReceipt;
use app\models\PurchaseOrderRefundQuantity;
use app\models\WarehouseResults;
use app\models\ReturnGoods;
use app\services\BaseServices;
use Yii;
use app\models\PurchaseOrderReceiptSearch;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use app\models\BankCardManagement;
use app\models\PurchaseOrderReceiptWater;
use app\models\PurchaseDemand;
use app\models\AmazonOutofstockOrder;
use app\models\PlatformSummary;
use app\models\PurchaseCompactItems;
use app\models\PurchaseCompact;
use m35\thecsv\theCsv;
/**
 * Created by PhpStorm.
 * User: ztt
 * Date: 2017/3/23 0023
 * Time: 18:41
 */
class PurchaseOrderReceiptNotificationController extends BaseController
{
    /**
     * @inheritdoc
     */
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
     * Lists all PurchaseOrderPay models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new PurchaseOrderReceiptSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }



    // 确认收款
    public function actionView($id)
    {
        $model = PurchaseOrderReceipt::find()
            ->joinWith(['purchaseOrder', 'supplier', 'purchaseOrders', 'purchaseCancelQuantitys'])
            ->where(['pur_purchase_order_receipt.id' => $id])
            ->one();
        $requisition_number = $model->requisition_number;

        $ctq = 0; // sku 确认数量总计
        $cly = 0; // sku 取消数量总计

        if(!empty($model->purchaseOrders)) {
            foreach($model->purchaseOrders as $v) {
                $ctq += $v['ctq'];
            }
        }
        if(!empty($model->purchaseCancelQuantitys)) {
            foreach($model->purchaseCancelQuantitys as $v) {
                $cly += $v['cly'];
            }
        }

        if(Yii::$app->request->isPost) {
            $data = Yii::$app->request->post()['PurchaseOrderReceipt'];
            $transaction = Yii::$app->db->beginTransaction();

            try {
                if($data['pay_status'] == 3) {
                    $model->pay_status = 2;
                    $model->payer      = Yii::$app->user->id;
                    $model->payer_time = $data['payer_time'];

                    // 修改采购单待收款变成已收款
                    if ($model->step == 3) {
                        if (!empty($data['requisition_number'])) {
                            $cancel_info = PurchaseOrderCancel::find()->where(['requisition_number' => $data['requisition_number']])->one();
                            //如果是取消作废的退款单，判断他的取消数量是否大于未到货的数据
                            if (!empty($cancel_info)) {
                                $cancel_instock_status = PurchaseOrderCancelSub::cancelCtqBccomp($data['pur_number']);
                                if ($cancel_instock_status['is_cancel_insrock'] == true) {
                                    Yii::$app->getSession()->setFlash('warning','取消数量大于未到货数量，建议驳回，并通知采购！！');
                                    return $this->redirect(Yii::$app->request->referrer);
                                }
                            }

                            //判断是否是，新版海外仓作废类型
                            if (!empty($cancel_info->purchaseOrderCancelSub[0]['demand_number'])) {
                                # 新版海外仓作废类型
                                foreach ($cancel_info->purchaseOrderCancelSub as $key => $value) {
                                    $res_cancel_ctq = PurchaseOrderCancelSub::getCancelCtq($value['pur_number'],$value['sku'],$value['demand_number']); //之前已取消的
                                    $instock_qty_count = PlatformSummary::getPlatformSummaryOneValue($value['demand_number'],'cty');//入库数量
                                    $ctq = PlatformSummary::getPlatformSummaryOneValue($value['demand_number'],'purchase_quantity');//采购数量;
                                    $surplus_ctq = $ctq-$instock_qty_count; //采购数量-入库数量
                                    $res_bccomp = bccomp($surplus_ctq, $res_cancel_ctq); //相等就等于0
                                    if ($res_bccomp === 0) {
                                        # 剩余的全部取消
                                        if ($value['old_demand_status'] == 10) {
                                            # 如果之前的状态为：部分到货等待剩余到货10->部分到货不等待剩余11
                                            PlatformSummary::updateAll(['demand_status'=>11,'is_purchase' => 1,'is_push'=>0, 'push_to_erp'=>0], ['demand_number'=>$value['demand_number']]);
                                        } else {
                                            # 作废
//                                            PlatformSummary::updateAll(['demand_status'=>14], ['demand_number'=>$value['demand_number']]);
                                            $summaryData = ['cancel_id'=>$value['cancel_id'],'demand_number'=>$value['demand_number']];
                                            PlatformSummary::updateCancelAll($summaryData,1);
                                        }
                                    } else {
                                        # 部分取消:返回之前的状态
                                        PlatformSummary::updateAll(['demand_status'=>$value['old_demand_status'],'is_purchase' => 1,'is_push'=>0, 'push_to_erp'=>0], ['demand_number'=>$value['demand_number']]);
                                    }
                                }
                            } 

                            //判断是否是取消未到货流程
                            if (!empty($cancel_info)) {
                                //比较 取消数量  和  采购数量-入库数量（只算审核通过的）
                                $res_ctq = PurchaseOrderCancelSub::cancelCtqBccomp($data['pur_number']);
                                $cancel_purchase_warehouses = $res_ctq['cancel_purchase_warehouses'];
                                $cancel_purchase = $res_ctq['cancel_purchase'];

                                if ( ($cancel_purchase_warehouses == 0) || ($cancel_purchase_warehouses==1) ) {
                                    $update_data['purchas_status'] = 9;
                                }
                                if ($cancel_purchase==0) {
                                    # 未入库 - 全部取消
                                    $update_data['purchas_status'] = 10;
                                }

                                $update_data['refund_status'] = 2;
                                PurchaseOrder::updateAll($update_data, ['pur_number' => $data['pur_number']]);
                            } else {
                                $rs_ctq = PurchaseOrderRefundQuantity::cancelCtqBccomp($data['pur_number']);
                                if ($rs_ctq == -1) {
                                    PurchaseOrder::updateAll(['purchas_status' => 8, 'refund_status' => 2, 'is_push' => 0], ['pur_number' => $data['pur_number']]);
                                } else {
                                    PurchaseOrder::updateAll(['purchas_status' => 9, 'refund_status' => 2, 'is_push' => 0], ['pur_number' => $data['pur_number']]);
                                }
                            }
                            PurchaseOrderItems::updateIsCancel($data['pur_number']); //修改采购单子表中的sku是否取消
                        }
                    } elseif($model->step == 4) {

                        // 当全额退款时，取消数量不等于确认时，为取消货物退款
                        if (!empty($ctq) && !empty($cly) && ($ctq != $cly)) {
                            PurchaseOrder::updateAll(['refund_status' => 2, 'is_push' => 0], ['pur_number' => $data['pur_number']]);
                        } else {
                            PurchaseOrder::updateAll(['purchas_status'=>10,'refund_status'=>2,'is_push'=>0],['pur_number'=>$data['pur_number']]);

                            //缺货列表更新处理状态
                            $demand_numbers = PurchaseDemand::find()->select('demand_number')->where(['pur_number'=>$data['pur_number']])->column();
                            AmazonOutofstockOrder::updateAll(['status'=>0,'is_show'=>1,'demand_number'=>''],['in','demand_number',$demand_numbers]);
                            //is_purchase=1未采购 is_push=0未推送 source=2erp  purchase_type=3FBA
                            PlatformSummary::updateAll(['level_audit_status'=>5,'is_purchase'=>1,'is_push'=>0],['and',['in','demand_number',$demand_numbers],['source'=>2,'purchase_type'=>3]]);

                            PlatformSummary::updateAll(['is_purchase'=>1,'is_push'=>0],['and',['in','demand_number',$demand_numbers],['source'=>1,'purchase_type'=>3]]);
                            PurchaseDemand::deleteRelation($data['pur_number']);
                        }

                    }

                    $model->save();
                    PurchaseOrderReceiptWater::saveOne($data);
                    //财务收款时，修改取消未到货的推送状态
                    PurchaseOrderCancel::updateIsPush($model['requisition_number']);
                    $transaction->commit();
                    Yii::$app->getSession()->setFlash('success','恭喜您！收款成功');
                    return $this->redirect(Yii::$app->request->referrer);

                } else {
                    $model->pay_status = 0;
                    $model->payer      = Yii::$app->user->id;
                    $model->payer_time = $data['payer_time'];
                    $model->save();

                    //采购单作废逻辑--驳回-作废
                    self::_purchaseCancelLogic($data);

                    $transaction->commit($model['requisition_number']);
                    Yii::$app->getSession()->setFlash('success','修改成功');
                    return $this->redirect(Yii::$app->request->referrer);
                }
            } catch (Exception $e) {
                $transaction->rollBack();
            }
        }
        if (!$model)
        {
            return '信息不存在';
        } else {
            // 加载默认显示的账户
            if ($model->pay_type == 3) {
                //支付方式“银行卡转账”,开票点为0时,默认“上海富友”
                $condition = ['status'=>1,'id'=>138];
                $bank  = BankCardManagement::find()->where($condition)->one();
            } elseif ($model->pay_type==2) {
                # 支付方式“支付宝”,款账户信息默认“我司支付宝账号yibaisuperbuyers”
                $condition = ['status'=>1,'id'=>143];
                $bank  = BankCardManagement::find()->where($condition)->one();
            }
            if(!isset($bank) OR empty($bank)) $bank = BankCardManagement::find()->one();

            return $this->renderAjax('view', [
                'model' => $model,
                'bank'  =>$bank,
                'requisition_number' => $requisition_number,
            ]);
        }

    }
    /**
     * 采购单作废逻辑--驳回-作废
     */
    public static function _purchaseCancelLogic($requisition_number)
    {
        $cancel_model = PurchaseOrderCancel::find()->where(['requisition_number' => $requisition_number])->one();
        if (!empty($cancel_model)) {
            //判断是否是，新版海外仓作废类型
            if (!empty($cancel_model->purchaseOrderCancelSub[0]['demand_number'])) {
                # 新版海外仓作废类型
                foreach ($cancel_model->purchaseOrderCancelSub as $key => $value) {
                    # 部分取消:返回之前的状态
                    PlatformSummary::updateAll(['demand_status'=>$value['old_demand_status'],'is_purchase' => 1,'is_push'=>0, 'push_to_erp'=>0], ['demand_number'=>$value['demand_number']]);
                }
            }
            $cancel_model->audit_status = 4;
            $cancel_model->save();
        
            PurchaseOrderItems::updateIsCancel($cancel_model->pur_number); //修改采购单子表中的sku是否取消                        
        }
    }

    /**
     * 导出cvs
     */
    public function actionExportCvs()
    {
        //以写入追加的方式打开

        $id = Yii::$app->request->get('ids');
        $id = strpos($id,',')?explode(',',$id):$id;
        $model = PurchaseOrderReceipt::find()->joinWith(['purchaseOrders','purchaseOrder','purchaseOrderShip'])->where(['in','pur_purchase_order_receipt.id',$id])->asArray()->all();

        $table = [
            '采购单号',
            '采购仓库',
            '采购员',
            '采购日期',
            'SKU',
            '货品名称',
            '采购单价',
            '采购数量',
            '运费',
            '金额',
            '备注',
        ];
        /* foreach($table as $k=>$v)
         {
             $table[$k]=mb_convert_encoding($v,'gb2312','utf-8');
         }*/
        $table_head = [];
        foreach($model as $k=>$v)
        {
            foreach ($v['purchaseOrders'] as $c=>$vb)
            {
                $table_head[$k][$c][]=$v['pur_number'];
                $table_head[$k][$c][]=BaseServices::getWarehouseCode($v['purchaseOrder']['warehouse_code']);
                $table_head[$k][$c][]=$v['purchaseOrder']['buyer'];
                $table_head[$k][$c][]=date('Y-m-d',strtotime($v['purchaseOrder']['created_at']));
                $table_head[$k][$c][]=$vb['sku'];
                $table_head[$k][$c][]=$vb['name'];
                $table_head[$k][$c][]=$vb['price'];
                $table_head[$k][$c][]=$vb['ctq'];
                $table_head[$k][$c][]=isset($v['purchaseOrderShip']['freight'])?$v['purchaseOrderShip']['freight']:"0";
                $table_head[$k][$c][]=$vb['items_totalprice'];
                $table_head[$k][$c][]=!empty(PurchaseNote::getNote($v['pur_number']))?PurchaseNote::getNote($v['pur_number']):'';

            }

        }
        theCsv::export([
            'header' =>$table,
            'data' => Vhelper::ThereArrayTwo($table_head),
        ]);

    }

    // 驳回退款单
    public function actionReject()
    {
        $request = Yii::$app->request;
        if($request->isPost) {
            $id = $request->post('id');
            $payer_notice = $request->post('payer_notice');
            $transaction = Yii::$app->db->beginTransaction();
            try {
                $model = PurchaseOrderReceipt::findOne($id);
                if(!$model) {
                    return json_encode([
                        'error' => 1,
                        'message' => '没有查找到这条记录'
                    ]);
                }

                //判断是否是：取消未到货的，是的就全部删除
                $requisition_number = $model->requisition_number;
                if (!empty($requisition_number)) {
                    self::_purchaseCancelLogic($requisition_number);
                }
                $orderModel = PurchaseOrder::find()->where(['pur_number' => $model->pur_number])->one();
                $model->pay_status = 10;
                $model->payer = Yii::$app->user->id;
                $model->payer_time = date('Y-m-d H:i:s', time());
                $model->payer_notice = $payer_notice;
                $orderModel->refund_status = 10;
                $a = $model->save(false);
                $b = $orderModel->save(false);
                if($a && $b) {
                    $transaction->commit();
                    return json_encode([
                        'error' => 0,
                        'message' => '操作成功'
                    ]);
                } else {
                    return josn_encode([
                        'error' => 1,
                        'message' => '数据保存失败'
                    ]);
                }
            } catch(\Exception $e) {
                $transaction->rollBack();
                return json_encode([
                    'error' => 1,
                    'message' => '操作失败'
                ]);
            }
        }
    }
}
