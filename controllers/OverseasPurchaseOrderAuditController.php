<?php
namespace app\controllers;
use app\models\PlatformSummary;
use app\models\PurchaseDemand;
use app\models\TablesChangeLog;
use Yii;
use app\config\Vhelper;
use app\models\PurchaseOrderItems;
use app\models\PurchaseSuggest;
use app\models\PurchaseSuggestHistory;
use app\models\PurchaseUser;
use app\models\Stock;
use app\models\SupplierQuotes;
use app\models\PurchaseOrder;
use app\models\PurchaseOrderSearch;
use yii\filters\VerbFilter;
use app\models\PurchaseLog;
use app\models\PurchaseCompact;
use app\models\PurchaseCompactItems;
use app\models\Template;
class OverseasPurchaseOrderAuditController extends BaseController
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

    public function actionIndex()
    {
        $args = Yii::$app->request->queryParams;
        $param = isset($args['PurchaseOrderSearch']) ? $args['PurchaseOrderSearch'] : [];
        $searchModel = new PurchaseOrderSearch();
        $searchModel->source = isset($args['source']) ? (int)$args['source'] : 1;
        if($searchModel->source == 1) {
            $data = $searchModel->search12($param);
            return $this->render('index', [
                'searchModel' => $searchModel,
                'list' => $data['list'],
                'pagination' => $data['pagination'],
                'source' => 1
            ]);
        } elseif(in_array($searchModel->source, [2, 3])) {
            $data = $searchModel->search7($param);
            return $this->render('index', [
                'searchModel' => $searchModel,
                'dataProvider' => $data,
                'source' => 2
            ]);
        } else {
            throw new \yii\web\NotFoundHttpException;
        }
    }

    public  function actionReview()
    {
        $page = $_REQUEST['page'];

        if (Yii::$app->request->isAjax)
        {
            $id = Yii::$app->request->get('id');
            $map['pur_purchase_order.id'] = $id;
            //单个审核
            $ordersitmes = PurchaseOrder::find()->joinWith(['purchaseOrderItems','supplier','orderNote'])->where($map)->one();

            $grade=PurchaseUser::findOne(['pur_user_id'=>Yii::$app->user->id]);

            return $this->renderAjax('review', [
                'model' =>$ordersitmes,
                'page' =>$page,
                'grade' =>$grade,
                'name'  =>Yii::$app->request->get('name'),
            ]);


        } elseif(Yii::$app->request->isPost) {
            //vd($_POST);
            $id                                 = Yii::$app->request->post()['PurchaseOrder']['id'];
            $purchas_status                     = Yii::$app->request->post()['PurchaseOrder']['purchas_status'];
            $audit_note                         = Yii::$app->request->post()['PurchaseOrder']['audit_note'];
            $ordersitmes                        = PurchaseOrder::find()->where(['in','id',$id])->all();

            $transaction = Yii::$app->db->beginTransaction();
            try {
                foreach ($ordersitmes as $ordersitem) {
                    if ($purchas_status == 3) {
                        //更新在途库存 暂时关闭
                        //$mods = PurchaseOrderItems::getSKUc($ordersitem->pur_number);
                        //Stock::saveStock($mods,$ordersitem->warehouse_code);
                        //已审核
                        $ordersitem->purchas_status = 3;
                        $ordersitem->review_status = 3;
                        $ordersitem->all_status = 5;
                        $ordersitem->pay_status = 1;
                        $ordersitem->audit_return = 2;
                        $ordersitem->audit_note = $audit_note;
                        $ordersitem->audit_time = date('Y-m-d H:i:s');

                        $order_items_info = PurchaseOrderItems::find()->where(['in', 'pur_number', $ordersitem['pur_number']])->all();
                        foreach ($order_items_info as $k => $v) {
                            $supplierQuotes[$k]['sku'] = $v['sku'];
                            $supplierQuotes[$k]['supplier_code'] = $ordersitem['supplier_code'];
                            $supplierQuotes[$k]['price'] = $v['price'];
                            $supplierQuotes[$k]['link'] = $v['product_link'] ? $v['product_link'] : SupplierQuotes::getUrl($v['sku']);
                            $supplierQuotes[$k]['buyer'] = $ordersitem['buyer'];
                        }

                        $demand_numbers = PurchaseDemand::find()->select('demand_number')
                                ->where(['in','pur_number',$ordersitem['pur_number']])->column();
                        PlatformSummary::updateAll(['is_push'=>0],['and',['in','demand_number',$demand_numbers],['is_push'=>1]]);

                        //修改供应商报价表的的单价
                        $model_supplier = new SupplierQuotes();
                        $model_supplier->saveSupplierQuotes($supplierQuotes);

                        //修改采购建议处理状态
                        //历史采购建议
                        $model_suggest_history = new PurchaseSuggestHistory();
                        $model_suggest_history->updateState($supplierQuotes);

                        //采购单日志添加
                        $s = [
                            'pur_number' => $ordersitem->pur_number,
                            'note' => '采购审核通过',
                        ];
                        PurchaseLog::addLog($s);
                    } elseif ($purchas_status == 4) {
                        //审核退回标志
                        $ordersitem->audit_return = 1;
                        //回退采购状态到待确认
                        $ordersitem->purchas_status = 1;
                        $ordersitem->audit_note = $audit_note;
                        //采购单日志添加
                        $s = [
                            'pur_number' => $ordersitem->pur_number,
                            'note' => '采购审核回退至采购确认',
                        ];
                        PurchaseLog::addLog($s);
                    } else {
                        //复审3
                        $ordersitem->audit_return = 2;
                        $ordersitem->audit_note = $audit_note;
                        //采购单日志添加
                        $s = [
                            'pur_number' => $ordersitem->pur_number,
                            'note' => '采购复审',
                        ];
                        PurchaseLog::addLog($s);

                    }

                    /* $grade=PurchaseUser::findOne(['pur_user_id'=>Yii::$app->user->id])->grade;

                     $ordersitem->review_status=$grade;
                     $remarks=date('Y-m-d H:i:s').' '.Yii::$app->params['grade'][$grade].':'.Yii::$app->user->identity->username.' 审核';
                     if(!empty($ordersitem->review_remarks)){
                         $ordersitem->review_remarks=$ordersitem->review_remarks.','.$remarks;
                     }else{
                         $ordersitem->review_remarks=$remarks;
                     }*/

                    //表修改日志-更新
                    $change_content = TablesChangeLog::updateCompare($ordersitem->attributes, $ordersitem->oldAttributes);
                    $change_data = [
                        'table_name' => 'pur_purchase_order', //变动的表名称
                        'change_type' => '2', //变动类型(1insert，2update，3delete)
                        'change_content' => $change_content, //变更内容
                    ];
                    TablesChangeLog::addLog($change_data);

                    $ordersitem->save(false);

                    if ($ordersitem->purchas_status == 3 && $ordersitem->review_status == 3) {//更新采购建议的状态
                        $poi = PurchaseOrderItems::getSKUc($ordersitem->pur_number);
                        if (!empty($poi)) {
                            foreach ($poi as $pv) {
                                $suggest_model = PurchaseSuggest::findOne(['sku' => $pv['sku']]);
                                if (empty($suggest_model)) continue;
                                $suggest_model->state = 2;

                                //表修改日志-更新
                                $change_content = TablesChangeLog::updateCompare($suggest_model->attributes, $suggest_model->oldAttributes);
                                $change_data = [
                                    'table_name' => 'pur_purchase_suggest', //变动的表名称
                                    'change_type' => '2', //变动类型(1insert，2update，3delete)
                                    'change_content' => $change_content, //变更内容
                                ];
                                TablesChangeLog::addLog($change_data);

                                $suggest_model->save();
                            }
                        }
                    }

                }
                $transaction->commit();
            }catch(Exception $e){
                $transaction->rollBack();
            }

            Yii::$app->getSession()->setFlash('success','恭喜你,审核成功');
            return $this->redirect(Yii::$app->request->referrer);
        }

    }


    /**
     * 批量审核
     */
    public  function actionAllReview()
    {
        ini_set('display_errors', true);
        error_reporting(E_ERROR);
        $page = $_REQUEST['page'];

        if (Yii::$app->request->isAjax)
        {
            $id = Yii::$app->request->get('id');
            $map['pur_purchase_order.id'] = $id;
            $ordersitmes = PurchaseOrder::find()->joinWith(['purchaseOrderItems','supplier','orderNote'])->where($map)->all();
            $grade=PurchaseUser::findOne(['pur_user_id'=>Yii::$app->user->id]);

            return $this->renderAjax('batch-review', [
                'page' =>$page,
                'grade' =>$grade,
                'model' =>$ordersitmes,
                'name'  =>Yii::$app->request->get('name'),
            ]);
        }else{

            $id                                 = Yii::$app->request->post()['PurchaseOrder']['id'];
            $purchas_status                     = Yii::$app->request->post()['PurchaseOrders']['purchas_status'];
            $b= Vhelper::changeData(Yii::$app->request->post()['PurchaseOrder']);
            $ordersitmes                        = PurchaseOrder::find()->where(['in','id',$id])->all();
            $transaction = Yii::$app->db->beginTransaction();
            try {
                foreach ($ordersitmes as $k => $ordersitem) {
                    if ($purchas_status[$k] == 3) {
                        //更新在途库存
                        //$mods = PurchaseOrderItems::getSKUc($ordersitem->pur_number);
                        //Stock::saveStock($mods);
                        //已审核
                        $ordersitem->purchas_status = 3;
                        $ordersitem->review_status = 3;
                        $ordersitem->all_status = 5;
                        $ordersitem->pay_status = 1;
                        $ordersitem->audit_return = 2;
                        $ordersitem->audit_note = $b[$k]['audit_note'];
                        $ordersitem->audit_time = date('Y-m-d H:i:s');
                        //采购单日志添加
                        $s = [
                            'pur_number' => $ordersitem->pur_number,
                            'note' => '批量采购审核通过',
                        ];

                        $order_items_info = PurchaseOrderItems::find()->where(['in', 'pur_number', $ordersitem->pur_number])->all();
                        foreach ($order_items_info as $k => $v) {
                            $supplierQuotes[$k]['sku'] = $v['sku'];
                            $supplierQuotes[$k]['supplier_code'] = $ordersitem['supplier_code'];
                            $supplierQuotes[$k]['price'] = $v['price'];
                            $supplierQuotes[$k]['link'] = $v['product_link'] ? $v['product_link'] : SupplierQuotes::getUrl($v['sku']);
                            $supplierQuotes[$k]['buyer'] = $ordersitem['buyer'];
                        }

                        $demand_numbers = PurchaseDemand::find()->select('demand_number')
                            ->where(['pur_number'=>$ordersitem->pur_number])->column();
                        PlatformSummary::updateAll(['is_push'=>0],['and',['in','demand_number',$demand_numbers],['is_push'=>1]]);

                        //修改供应商报价表的的单价
                        $model_supplier = new SupplierQuotes();
                        $model_supplier->saveSupplierQuotes($supplierQuotes);

                        //修改采购建议处理状态
                        //历史采购建议
                        $model_suggest_history = new PurchaseSuggestHistory();
                        $model_suggest_history->updateState($supplierQuotes);
                        //采购建议
                        /* $model_suggest = new PurchaseSuggest();
                         $model_suggest->updateState($supplierQuotes);*/

                        PurchaseLog::addLog($s);
                    } elseif ($purchas_status[$k] == 4) {
                        //审核退回标志
                        $ordersitem->audit_return = 1;
                        //回退采购状态到待确认
                        $ordersitem->purchas_status = 1;
                        $ordersitem->audit_note = $b[$k]['audit_note'];
                        //采购单日志添加
                        $s = [
                            'pur_number' => $ordersitem->pur_number,
                            'note' => '批量采购审核回退至采购确认',
                        ];
                        PurchaseLog::addLog($s);
                    } else {
                        //复审3
                        $ordersitem->audit_return = 2;
                        $ordersitem->audit_note = $b[$k]['audit_note'];
                        //采购单日志添加
                        $s = [
                            'pur_number' => $ordersitem->pur_number,
                            'note' => '批量采购复审',
                        ];
                        PurchaseLog::addLog($s);

                    }

                    $grade = PurchaseUser::findOne(['pur_user_id' => Yii::$app->user->id])->grade;

                    $ordersitem->review_status = $grade;
                    $remarks = date('Y-m-d H:i:s') . ' ' . Yii::$app->params['grade'][$grade] . ':' . Yii::$app->user->identity->username . ' 审核';
                    if (!empty($ordersitem->review_remarks)) {
                        $ordersitem->review_remarks = $ordersitem->review_remarks . ',' . $remarks;
                    } else {
                        $ordersitem->review_remarks = $remarks;
                    }

                    //表修改日志-更新
                    $change_content = TablesChangeLog::updateCompare($ordersitem->attributes, $ordersitem->oldAttributes);
                    $change_data = [
                        'table_name' => 'pur_purchase_order', //变动的表名称
                        'change_type' => '2', //变动类型(1insert，2update，3delete)
                        'change_content' => $change_content, //变更内容
                    ];
                    TablesChangeLog::addLog($change_data);
                    $ordersitem->save(false);

                    if ($ordersitem->purchas_status == 3 && $ordersitem->review_status == 3) {//更新采购建议的状态
                        $poi = PurchaseOrderItems::getSKUc($ordersitem->pur_number);
                        if (!empty($poi)) {
                            foreach ($poi as $pv) {
                                $suggest_model = PurchaseSuggest::findOne(['sku' => $pv['sku']]);
                                if (empty($suggest_model)) continue;
                                $suggest_model->state = 2;

                                //表修改日志-更新
                                $change_content = TablesChangeLog::updateCompare($suggest_model->attributes, $suggest_model->oldAttributes);
                                $change_data = [
                                    'table_name' => 'pur_purchase_suggest', //变动的表名称
                                    'change_type' => '2', //变动类型(1insert，2update，3delete)
                                    'change_content' => $change_content, //变更内容
                                ];
                                TablesChangeLog::addLog($change_data);
                                $suggest_model->save();
                            }
                        }
                    }

                }
                $transaction->commit();
            }catch(Exception $e){
                $transaction->rollBack();
            }

            Yii::$app->getSession()->setFlash('success','恭喜你,操作成功');
            return $this->redirect(Yii::$app->request->referrer);
        }
    }

    /**************合同审核相关 开始******************/
    // 合同审核
    public function actionCompactReview()
    {
        $request = Yii::$app->request;
        if(Yii::$app->request->isPost) {
            $POST = Yii::$app->request->post();
            $audit_note = $POST['audit_note'];
            $status = $POST['status'];
            $pos = $POST['pos'];
            $cpn = $POST['compact_number'];
            $order_list = PurchaseOrder::find()->where(['in', 'pur_number', $pos])->all();
            $compact_model = PurchaseCompact::find()->where(['compact_number' => $cpn])->one();
            $transaction = Yii::$app->db->beginTransaction();
            try {
                foreach($order_list as $order) {
                    if($status == 3) {
                        $order->purchas_status = 3; // 已审批
                        $order->review_status = 3;
                        $order->all_status = 5;
                        $order->pay_status = 1;     // 未申请付款
                        $order->audit_return = 2;
                        $order->audit_note = $audit_note;
                        $order->audit_time = date('Y-m-d H:i:s');
                        $order->auditor = Yii::$app->user->identity->username;
                        $order_items_info = PurchaseOrderItems::find()->where(['in', 'pur_number', $order['pur_number']])->all();
                        foreach($order_items_info as $k => $v) {
                            $supplierQuotes[$k]['sku'] = $v['sku'];
                            $supplierQuotes[$k]['supplier_code'] = $order['supplier_code'];
                            $supplierQuotes[$k]['price'] = $v['price'];
                            $supplierQuotes[$k]['link'] = $v['product_link'] ? $v['product_link'] : SupplierQuotes::getUrl($v['sku']);
                            $supplierQuotes[$k]['buyer'] = $order['buyer'];
                        }
                        $model_supplier = new SupplierQuotes();
                        $model_supplier->saveSupplierQuotes($supplierQuotes);
                        $model_suggest_history = new PurchaseSuggestHistory();
                        $model_suggest_history->updateState($supplierQuotes);
                        $log = [
                            'pur_number' => $order->pur_number,
                            'note' => '海外仓合同审核：审核通过，采购单变为已审批状态'
                        ];
                        PurchaseLog::addLog($log);
                    } elseif ($status == 4) {
                        $order->audit_return = 1;
                        $order->purchas_status = 1; // 待确认状态
                        $order->source = 2;         // 驳回后，重新改为网采单
                        $order->audit_note = $audit_note;
                        $s = [
                            'pur_number' => $order->pur_number,
                            'note' => '合同单采购审核：被驳回，采购单回退至待确认状态',
                        ];
                        PurchaseLog::addLog($s);
                    } else {
                        $order->audit_return = 2;
                        $order->audit_note = $audit_note;
                        $s = [
                            'pur_number' => $order->pur_number,
                            'note' => '采购复审',
                        ];
                        PurchaseLog::addLog($s);
                    }
                    $grade = PurchaseUser::find()->where(['pur_user_id' => Yii::$app->user->id])->one();
                    if($grade) {
                        $order->review_status = $grade->grade;
                        $remarks = date('Y-m-d H:i:s').' '.Yii::$app->params['grade'][$grade->grade].':'.Yii::$app->user->identity->username.' 审核';
                    } else {
                        $order->review_status = 0;
                        $remarks = date('Y-m-d H:i:s').'未知级别:'.Yii::$app->user->identity->username.' 审核';
                    }
                    if(!empty($order->review_remarks)) {
                        $order->review_remarks = $order->review_remarks.','.$remarks;
                    }else{
                        $order->review_remarks = $remarks;
                    }

                    //表修改日志-更新
                    $change_content = TablesChangeLog::updateCompare($order->attributes, $order->oldAttributes);
                    $change_data = [
                        'table_name' => 'pur_purchase_order', //变动的表名称
                        'change_type' => '2', //变动类型(1insert，2update，3delete)
                        'change_content' => $change_content, //变更内容
                    ];
                    TablesChangeLog::addLog($change_data);
                    $order->save(false);

                    // 更新采购建议的状态
                    if($order->purchas_status == 3 && $order->review_status == 3) {
                        $poi = PurchaseOrderItems::getSKUc($order->pur_number);
                        if(!empty($poi)) {
                            foreach($poi as $pv) {
                                $suggest_model = PurchaseSuggest::findOne(['sku' => $pv['sku']]);
                                if(empty($suggest_model)) continue;
                                $suggest_model->state = 2;

                                //表修改日志-更新
                                $change_content = TablesChangeLog::updateCompare($suggest_model->attributes, $suggest_model->oldAttributes);
                                $change_data = [
                                    'table_name' => 'pur_purchase_suggest', //变动的表名称
                                    'change_type' => '2', //变动类型(1insert，2update，3delete)
                                    'change_content' => $change_content, //变更内容
                                ];
                                TablesChangeLog::addLog($change_data);
                                $suggest_model->save();
                            }
                        }
                    }
                }

                if($status == 3) { // 通过

                    $compact_model->compact_status = 3;

                } else if($status == 4) { // 驳回

                    $compact_model->compact_status = 10; // 作废合同

                    // 表修改日志-更新
                    $change_data = [
                        'table_name' => 'pur_purchase_compact', // 变动的表名称
                        'change_type' => '2', // 变动类型(1insert，2update，3delete)
                        'change_content' => "update:compact_number:{$cpn},bind:=>0", // 变更内容
                    ];

                    TablesChangeLog::addLog($change_data);

                    PurchaseCompactItems::updateAll(['bind' => 0], ['compact_number' => $cpn]);
                }

                $compact_model->audit_time = date('Y-m-d H:i:s', time());
                $compact_model->audit_person_name = Yii::$app->user->identity->username;
                $compact_model->audit_person_id = Yii::$app->user->id;
                $compact_model->audit_note = $audit_note;

                // 表修改日志-更新
                $change_content = TablesChangeLog::updateCompare($compact_model->attributes, $compact_model->oldAttributes);
                $change_data = [
                    'table_name' => 'pur_purchase_compact', // 变动的表名称
                    'change_type' => '2', // 变动类型(1insert，2update，3delete)
                    'change_content' => $change_content, // 变更内容
                ];
                TablesChangeLog::addLog($change_data);
                $compact_model->save(false);

                // write log
                PurchaseLog::addLog([
                    'pur_number' => $cpn,
                    'note' => '海外仓合同审核',
                ]);
                $transaction->commit();
                Yii::$app->getSession()->setFlash('success','恭喜你，审核成功');
                return $this->redirect(Yii::$app->request->referrer);
            } catch(Exception $e) {
                $transaction->rollBack();
                Yii::$app->getSession()->setFlash('error','对不起，审核失败');
                return $this->redirect(Yii::$app->request->referrer);
            }
        } else {
            $cpn = $request->get('cpn');
            $pos = PurchaseCompact::getPurNumbers($cpn);
            $orders = PurchaseOrder::find()->where(['in', 'pur_number', $pos])->all();
            $model = PurchaseCompact::find()->where(['compact_number' => $cpn])->one();
            $products = [];
            foreach($pos as $p) {
                $skus = PurchaseOrderItems::find()->where(['pur_number' => $p])->asArray()->all();
                if($skus) {
                    $products[$p] = $skus;
                }
            }
            $tpl = Template::findOne($model->tpl_id);
            $tplPath = $tpl->style_code.'.php';
            return $this->renderAjax('compact-review', [
                'orderList' => $orders,
                'model' => $model,
                'products' => $products,
                'tpl' => $tplPath
            ]);
        }
    }
    /**************合同审核相关 结束***********************/

}
