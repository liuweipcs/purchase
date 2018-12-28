<?php

namespace app\controllers;

use app\config\Vhelper;
use app\models\PurchaseOrderItems;
use app\models\PurchaseSuggest;
use app\models\PurchaseSuggestHistory;
use app\models\PurchaseSuggestNote;
use app\models\PurchaseSuggestQuantity;
use app\models\PurchaseUser;
use app\models\SampleStock;
use app\models\SampleStockLog;
use app\models\Stock;
use app\models\SupplierQuotes;
use app\models\TablesChangeLog;
use Yii;
use app\models\PurchaseOrder;
use app\models\PurchaseOrderSearch;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use app\models\PurchaseLog;
use app\models\PurchaseCompact;
use app\models\PurchaseCompactItems;
use app\models\Template;
use yii\data\Pagination;
use app\models\PurchaseGradeAudit;
use app\services\BaseServices;

/**
 * Created by PhpStorm.
 * User: ztt
 * Date: 2017/3/23 0023
 * Time: 18:41
 */
class PurchaseOrderAuditController extends BaseController
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
     * 采购审核首页
     * Lists all PurchaseOrder models.
     * @return mixed
     */
    public function actionIndex()
    {
        /*        $searchModel = new PurchaseOrderSearch();
                $dataProvider = $searchModel->search1(Yii::$app->request->queryParams);

                //$grade=PurchaseUser::findOne(['pur_user_id'=>Yii::$app->user->id]);

                //组长、主管采购单未审核，统计
                //$countreview=PurchaseOrder::find();

                //$where=['and', 'audit_return=2',['!=','purchase_type','3'],['=','purchas_status','2']];
                //$where2=['and', 'audit_return=2',['!=','purchase_type','3'],['=','purchas_status','3']];

                //$leadsum=$countreview->where($where)->andWhere(['review_status'=>'0'])->count('id');
                //$supervissum=$countreview->where($where2)->andWhere(['review_status'=>'1'])->count('id');

                return $this->render('index', [
                    //'grade'=>$grade,
                    //'leadsum'=>$leadsum,
                    //'supervissum'=>$supervissum,
                    'searchModel' => $searchModel,
                    'dataProvider' => $dataProvider,
                ]);*/


        $args = Yii::$app->request->queryParams;

        $param = isset($args['PurchaseOrderSearch']) ? $args['PurchaseOrderSearch'] : [];
        $searchModel = new PurchaseOrderSearch();
        $searchModel->source = isset($args['source']) ? (int)$args['source'] : 2; // 默认网采单
        if($searchModel->source == 1) {
            $data = $searchModel->search13($param);
            return $this->render('index', [
                'searchModel' => $searchModel,
                'list' => $data['list'],
                'pagination' => $data['pagination'],
                'source' => 1
            ]);
        } elseif($searchModel->source == 2) {
            $data = $searchModel->search1($args);
            return $this->render('index', [
                'searchModel' => $searchModel,
                'dataProvider' => $data,
                'source' => 2
            ]);
        } else {
            throw new \yii\web\NotFoundHttpException;
        }





    }



    /**
     * 采购审核-审核操作
     * Displays a single PurchaseOrder model.
     * @return mixed
     */
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

                    $price_sum = PurchaseOrderItems::find()
                        ->select('SUM(price*ctq) price_sum')
                        ->where(['pur_number'=>$ordersitem['pur_number']])
                        ->groupBy('pur_number')
                        ->scalar();

                    $buyer = $ordersitem['buyer'];
                    $audit_name = yii::$app->user->identity->username;
                    $buyer_group_id = PurchaseUser::getGroupId(null,$buyer); //获取采购员的采购小组
                    $audit_group_id = PurchaseUser::getGroupId(null,$audit_name); //获取审核人的采购小组
                    $audit_price = PurchaseGradeAudit::getAuditPrice($audit_name);
                    // vd($buyer_group_id,$audit_group_id,$price_sum,$audit_price);
                    if ( ($buyer_group_id==$audit_group_id || BaseServices::getIsAdmin(1)) && ($price_sum<=$audit_price) ) {
                        //条件：同一组，审核金额大于等于订单金额
                    } else {
                        $transaction->rollBack();
                        Yii::$app->getSession()->setFlash('error','不是同一小组或审核金额小于采购金额：' . $ordersitem['pur_number']);
                        return $this->redirect(Yii::$app->request->referrer);
                    }

                    
                    if ($purchas_status == 3) {
                        //更新在途库存 暂时关闭
                        //$mods = PurchaseOrderItems::getSKUc($ordersitem->pur_number);
                        //Stock::saveStock($mods,$ordersitem->warehouse_code);

                        //已审核
                        $ordersitem->purchas_status = 7;
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
                            $supplierQuotes[$k]['warehouse_code'] = !empty($ordersitem['warehouse_code']) ? $ordersitem['warehouse_code'] : '';

                            if($ordersitem->warehouse_code == 'de-yida'){
                                $exist = SampleStockLog::find()->where(['sku'=>$v['sku'],'change_pur_number'=>$ordersitem->pur_number,'type'=>1])->exists();
                                if(!$exist){
                                    //样品仓增加在途库存
                                    SampleStock::saveStock($v['sku'],$v['ctq'],0,$ordersitem->warehouse_code,0);
                                    //样品仓增加在途库存日志
                                    SampleStockLog::saveStockLog($v['sku'],'+'.$v['ctq'],0,$ordersitem->pur_number,1,$ordersitem->warehouse_code,0);
                                }
                            }

                            //删除掉采购建议中未处理原因
                            $warehouse_code = !empty($ordersitem['warehouse_code']) ? $ordersitem['warehouse_code'] : '';
                            $note_info = PurchaseSuggestNote::find()->where(['sku'=>$v['sku'], 'warehouse_code'=>$warehouse_code,'status'=>1])->one();
                            if (!empty($note_info)) {

                                //表修改日志-删除
                                $change_content = "update:warehouse_code:{warehouse_code},sku:{$v['sku']}";
                                $change_data = [
                                    'table_name' => 'pur_purchase_suggest_note', //变动的表名称
                                    'change_type' => '2', //变动类型(1insert，2update，3delete)
                                    'change_content' => $change_content, //变更内容
                                ];
                                TablesChangeLog::addLog($change_data);
                                $note_info->status = 0;
                                $note_info->update_time =date('Y-m-d H:i:s',time());
                                $note_info->update_user_name = Yii::$app->user->identity->username;
                                $note_info->save();
                            }
                        }

                        //修改供应商报价表的的单价
                        $model_supplier = new SupplierQuotes();
                        $model_supplier->saveSupplierQuotes($supplierQuotes);

                        //修改采购建议处理状态
                        //历史采购建议
                        $model_suggest_history = new PurchaseSuggestHistory();
                        $model_suggest_history->updateState($supplierQuotes);

                        //修改仓库导入需求的状态
                        $model_suggest_quantity = new  PurchaseSuggestQuantity();
                        $model_suggest_quantity->updateSuggestStatus($supplierQuotes);



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











    public function actionCompactReview()
    {
        $request = Yii::$app->request;
        if(Yii::$app->request->isPost) {
            // 执行审核动作
            $POST = Yii::$app->request->post();
            $audit_note = $POST['audit_note'];
            $status = $POST['status'];
            $orders = $POST['pos'];
            $cpn = $POST['compact_number'];
            $order_list = PurchaseOrder::find()->where(['in', 'pur_number', $orders])->all();
            $compact_model = PurchaseCompact::find()->where(['compact_number' => $cpn])->one();
            $transaction = Yii::$app->db->beginTransaction();
            try {
                foreach ($order_list as $order) {

                    if($status == 3) {
                        // 更新在途库存 暂时关闭
                        // $mods = PurchaseOrderItems::getSKUc($ordersitem->pur_number);
                        // Stock::saveStock($mods,$ordersitem->warehouse_code);
                        // 已审核
                        $order->purchas_status = 3;
                        $order->review_status = 3;
                        $order->all_status = 5;
                        $order->pay_status = 1;
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

                        // 修改供应商报价表的的单价
                        $model_supplier = new SupplierQuotes();
                        $model_supplier->saveSupplierQuotes($supplierQuotes);

                        // 修改采购建议处理状态
                        // 历史采购建议
                        $model_suggest_history = new PurchaseSuggestHistory();
                        $model_suggest_history->updateState($supplierQuotes);

                        // 采购单日志添加
                        $s = [
                            'pur_number' => $order->pur_number,
                            'note' => '采购单审核v3.0',
                        ];
                        PurchaseLog::addLog($s);

                    } elseif ($status == 4) {

                        // 审核退回标志
                        $order->audit_return = 1;
                        // 回退采购状态到待确认
                        $order->purchas_status = 1;
                        $order->audit_note = $audit_note;
                        // 采购单日志添加
                        $s = [
                            'pur_number' => $order->pur_number,
                            'note' => '采购审核回退至采购确认',
                        ];
                        PurchaseLog::addLog($s);

                    } else {

                        // 复审3
                        $order->audit_return = 2;
                        $order->audit_note = $audit_note;
                        // 采购单日志添加
                        $s = [
                            'pur_number' => $order->pur_number,
                            'note' => '采购复审',
                        ];
                        PurchaseLog::addLog($s);

                    }

                    // 审核人级别
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

                // 标记合同表审核信息
                $compact_model->compact_status = 3; // 已审批（同订单一致）
                $compact_model->audit_time = date('Y-m-d H:i:s', time());
                $compact_model->audit_person_name = Yii::$app->user->identity->username;
                $compact_model->audit_person_id = Yii::$app->user->id;
                $compact_model->audit_note = $audit_note;

                //表修改日志-更新
                $change_content = TablesChangeLog::updateCompare($compact_model->attributes, $compact_model->oldAttributes);
                $change_data = [
                    'table_name' => 'pur_purchase_compact', //变动的表名称
                    'change_type' => '2', //变动类型(1insert，2update，3delete)
                    'change_content' => $change_content, //变更内容
                ];
                TablesChangeLog::addLog($change_data);
                $compact_model->save(false);

                // 写合同日志
                PurchaseLog::addLog([
                    'pur_number' => $cpn,
                    'note' => '合同审核',
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
            set_time_limit(3600);

            // 渲染审核界面
            $cpn = $request->get('cpn');
            $pos = PurchaseCompact::getPurNumbers($cpn);
            $orders = PurchaseOrder::find()->where(['in', 'pur_number', $pos])->all();

            $model = PurchaseCompact::find()->where(['compact_number' => $cpn])->one();
            $items = $model->purchaseCompactItems;
            $products = [];
            foreach($items as $m) {
                $pur_number = $m->pur_number;
                $skus = PurchaseOrderItems::find()->where(['pur_number' => $pur_number])->asArray()->all();
                if($skus) {
                    $products[$pur_number] = $skus;
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






















    /**
     * 批量审核
     */
    public  function actionAllReview()
    {
        //ini_set('display_errors', true);
        //error_reporting(E_ERROR);
        //\yii::$app->response->format = 'raw';
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

                    $price_sum = PurchaseOrderItems::find()
                        ->select('SUM(price*ctq) price_sum')
                        ->where(['pur_number'=>$ordersitem['pur_number']])
                        ->groupBy('pur_number')
                        ->scalar();

                    $buyer = $ordersitem['buyer'];
                    $audit_name = yii::$app->user->identity->username;
                    $buyer_group_id = PurchaseUser::getGroupId(null,$buyer); //获取采购员的采购小组
                    $audit_group_id = PurchaseUser::getGroupId(null,$audit_name); //获取审核人的采购小组
                    $audit_price = PurchaseGradeAudit::getAuditPrice($audit_name);
                    // vd($buyer_group_id,$audit_group_id,$price_sum,$audit_price);
                    if ( ($buyer_group_id==$audit_group_id || BaseServices::getIsAdmin(1)) && ($price_sum<=$audit_price) ) {
                        //条件：同一组，审核金额大于等于订单金额
                    } else {
                        $transaction->rollBack();
                        Yii::$app->getSession()->setFlash('error','不是同一小组或审核金额小于采购金额：' . $ordersitem['pur_number']);
                        return $this->redirect(Yii::$app->request->referrer);
                    }


                    



                    if ($purchas_status[$k] == 3) {
                        //更新在途库存
                        //$mods = PurchaseOrderItems::getSKUc($ordersitem->pur_number);
                        //Stock::saveStock($mods);
                        //已审核
                        $ordersitem->purchas_status = 7;
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

                        $order_items_info = PurchaseOrderItems::find()->where(['in', 'pur_number', $ordersitem['pur_number']])->all();
                        foreach ($order_items_info as $k => $v) {
                            $supplierQuotes[$k]['sku'] = $v['sku'];
                            $supplierQuotes[$k]['supplier_code'] = $ordersitem['supplier_code'];
                            $supplierQuotes[$k]['price'] = $v['price'];
                            $supplierQuotes[$k]['link'] = $v['product_link'] ? $v['product_link'] : SupplierQuotes::getUrl($v['sku']);
                            $supplierQuotes[$k]['buyer'] = $ordersitem['buyer'];
                            $supplierQuotes[$k]['warehouse_code'] = !empty($ordersitem['warehouse_code']) ? $ordersitem['warehouse_code'] : '';

                            if($ordersitem->warehouse_code == 'de-yida'){
                                //样品仓增加在途库存
                                SampleStock::saveStock($v['sku'],$v['ctq'],0,$ordersitem->warehouse_code,0);
                                //样品仓增加在途库存日志
                                SampleStockLog::saveStockLog($v['sku'],'+'.$v['ctq'],0,$ordersitem->pur_number,1,$ordersitem->warehouse_code,0);
                            }

                            //删除掉采购建议中未处理原因
                            $warehouse_code = !empty($ordersitem['warehouse_code']) ? $ordersitem['warehouse_code'] : '';
                            $note_info = PurchaseSuggestNote::find()->where(['sku'=>$v['sku'], 'warehouse_code'=>$warehouse_code,'status'=>1])->one();
                            if (!empty($note_info)) {
                                //表修改日志-删除
                                $change_content = "update:warehouse_code:{warehouse_code},sku:{$note_info->id}";
                                $change_data = [
                                    'table_name' => 'pur_purchase_suggest_note', //变动的表名称
                                    'change_type' => '2', //变动类型(1insert，2update，3delete)
                                    'change_content' => $change_content, //变更内容
                                ];
                                TablesChangeLog::addLog($change_data);
                                $note_info->status = 0;
                                $note_info->update_time = date('Y-m-d H:i:s',time());
                                $note_info->update_user_name = Yii::$app->user->identity->username;
                                $note_info->save();
                            }
                        }

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

                        //修改仓库导入需求的状态
                        $model_suggest_quantity = new  PurchaseSuggestQuantity();
                        $model_suggest_quantity->updateSuggestStatus($supplierQuotes);

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

}
