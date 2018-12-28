<?php

namespace app\controllers;

use app\api\v1\models\ArrivalRecord;
use app\config\Vhelper;
use app\models\ProductTaxRate;
use app\models\PurchaseAbnomal;
use app\models\PurchaseFreightHistory;
use app\models\PlatformSummary;
use app\models\PlatformSummarys;
use app\models\PurchaseDemand;
use app\models\PurchaseOrderItems;
use app\models\PurchaseOrderOrders;
use app\models\PurchaseOrderPay;
use app\models\PurchaseOrderReceipt;
use app\models\PurchaseOrderShip;
use app\models\PurchaseReceive;
use app\models\PurchaseRefunds;
use app\models\PurchaseReply;
use app\models\SkuTotal;
use app\models\Stock;
use app\models\TablesChangeLog;
use app\models\WarehouseResults;
use app\services\BaseServices;
use app\services\CommonServices;
use app\services\PurchaseOrderServices;
use m35\thecsv\theCsv;
use Yii;
use app\models\PurchaseOrder;
use app\models\PurchaseOrderSearch;
use yii\db\Exception;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use app\models\PurchaseLog;
use app\models\PurchaseOrderItemsBak;
use app\models\PurchaseNote;
use app\config\Curd;
use app\models\ProductImgDownload;
use app\models\app\models;
use app\models\PurchaseOrderPayDetail;
use app\models\PurchaseOrderPayType;
use app\models\PurchaseOrderBreakage;
use app\models\OperatLog;
use app\models\PurchaseCompact;
use app\models\PurchaseCompactItems;
use app\models\PurchaseCompactSearch;
use app\models\PurchaseOrderTaxes;
use yii\widgets\ListView;
use yii\data\ActiveDataProvider;

use yii\data\Pagination;
use app\models\Template;
use app\models\SupplierPaymentAccount;
use app\models\PurchasePayForm;
use app\models\PurchaseOrderRefundQuantity;
use app\models\PurchaseEstimatedTime;
use app\models\OverseasPurchaseOrderSearch;
use app\models\TableFields;
use app\models\DemandCheck;
use app\services\PlatformSummaryServices;
use app\models\SupplierBuyer;
use app\services\SupplierServices;
use app\models\ProductProvider;
use app\models\SupplierQuotes;
use app\models\PurchaseUser;
use app\models\DemandLog;
use app\models\OrderPayDemandMap;
use app\models\OverseasPaymentSearch;
use app\models\PurchaseOrderCancel;
use app\models\PurchaseOrderCancelSub;
use yii\web\JsonResponseFormatter;
use app\models\DemandInvoice;
use app\models\HwcAvgDeliveryTime;


class OverseasPurchaseOrder2Controller extends BaseController
{

    public $undonePay = [2, 4, 10]; // 未完成的交易状态
    public $noPayStatus = [0, 3, 11, 12]; // 不做计算的请款状态
    public $disabledOrder = [1, 2, 4, 10]; // 不可再使用的订单状态

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
     * 海外仓-采购单 列表页
     */
    public function actionIndex()
    {
        $cache = Yii::$app->cache;
        $args = Yii::$app->request->queryParams;
        if (isset($_GET['requisition_number'])) {
            $this->layout='ajax';
        }
        $searchModel = new OverseasPurchaseOrderSearch();
        $pageSize = isset($_REQUEST['per-page'])?$_REQUEST['per-page']:null;
        if (!empty($pageSize)) {
            $args = $cache->get('order2_params');
            $args['pageSize'] = $pageSize;
            $cache->set('order2_params', $args);
        } else {
            $cache->set('order2_params', $args);
        }

        $result = $searchModel->search($args);
        $dataProvider = $result['dataProvider'];
        $totalprice = $result['totalprice'];
        
        $userid = Yii::$app->user->identity->getId();
        $fields = TableFields::find()->where(['userid'=>$userid,'table_name'=>'overseas_order_list'])->select('data')->scalar();
        $fields = $fields ? json_decode($fields, true) : [];
        
        return $this->render('index', [
            'model' => $searchModel,
            'dataProvider' => $dataProvider,
            'totalprice' => $totalprice,
            'fields' => $fields,
        ]);
    }
    
    /**
     * 信息提交
     */
    public function actionConfirmInfo()
    {
        $ids = Yii::$app->request->post('id');
        if (empty($ids)) {
            return jsonReturn(0,'请勾选要提交的数据');
        }
        //$buyer_array = Yii::$app->request->post('buyer');
        $purchase_quantity_array = Yii::$app->request->post('purchase_quantity');
        $purchase_quantity_note_array = Yii::$app->request->post('purchase_quantity_note');
        $demand_export_cname_array = Yii::$app->request->post('demand_export_cname');
        $default_demand_export_cname_array = Yii::$app->request->post('default_demand_export_cname');
        $demand_declare_unit_array = Yii::$app->request->post('demand_declare_unit');
        $default_demand_declare_unit_array = Yii::$app->request->post('default_demand_declare_unit');
        //$account_type_array = Yii::$app->request->post('account_type');
        //$pay_type_array = Yii::$app->request->post('pay_type');
        $settlement_ratio_array = Yii::$app->request->post('settlement_ratio');
        //$is_transit_array = Yii::$app->request->post('is_transit');
        $is_drawback_array = Yii::$app->request->post('is_drawback');
        $is_drawback_note_array = Yii::$app->request->post('is_drawback_note');
        $transit_warehouse_array = Yii::$app->request->post('transit_warehouse');
        $date_eta_array = Yii::$app->request->post('date_eta');
        $source_array = Yii::$app->request->post('source');
        $freight_formula_mode_array = Yii::$app->request->post('freight_formula_mode');
        $freight_payer_array = Yii::$app->request->post('freight_payer');
        //$purchase_acccount_array = Yii::$app->request->post('purchase_acccount');
        
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $purchase_orders = $update_order_data = [];
            $update_data_param = [
                //'buyer' => '采购员',
                'purchase_quantity' => '数量',
                'transit_number' => '中转数量',
                'demand_export_cname' => '开票品名',
                'demand_declare_unit' => '开票单位',
                //'account_type' => ['结算方式', SupplierServices::getSettlementMethod()],
                //'pay_type' => ['支付方式', SupplierServices::getDefaultPaymentMethod()],
                'transit_warehouse' => ['中转仓库', PurchaseOrderServices::getTransitWarehouse()],
                'settlement_ratio' => '结算比例',
                'is_drawback' => ['是否退税',[1=>'不退税',2=>'退税']],
                'date_eta' => ['预计到货时间'],
                'source' => ['采购来源', [1=>'合同',2=>'网采']],
                'freight_formula_mode' => ['运费计算方式',['weight'=>'重量','volume'=>'体积']],
                'freight_payer' => ['运费支付方',[1=>'甲方支付',2=>'乙方支付']],
                //'purchase_acccount' => ['账号', BaseServices::getAlibaba()],
            ];
            
            foreach ($ids as $demand_number) {
                $model = PlatformSummary::findOne(['demand_number'=>$demand_number]);
                if (empty($model)) {
                    continue;
                }
                if ($model->demand_status != 1) {
                    return jsonReturn(0,$model->sku.','.$demand_number.',当前状态不能提交修改');
                }
                $purchase_quantity_array[$demand_number] = intval($purchase_quantity_array[$demand_number]);
                if ($purchase_quantity_array[$demand_number] < 1) {
                    return jsonReturn(0,$model->sku.','.$demand_number.',填写的【数量】有误');
                }
                if ($model->purchase_quantity != $purchase_quantity_array[$demand_number] && empty($purchase_quantity_note_array[$demand_number])) {
                    return jsonReturn(0,$model->sku.','.$demand_number.',修改了数量未填写备注');
                }
                if ($this->checkSettlementRatio($settlement_ratio_array[$demand_number]) == false) {
                    return jsonReturn(0,$model->sku.','.$demand_number.',填写的【结算比例】有误');
                }
                if (empty($date_eta_array[$demand_number])) {
                    return jsonReturn(0,$model->sku.','.$demand_number.',请填写【预计到货日期】');
                }
                $avg_delivery_time = HwcAvgDeliveryTime::find()->select('avg_delivery_time')->where(['sku'=>$model->sku])->scalar();
                if ($avg_delivery_time && $avg_delivery_time > 0) {
                    if (strtotime($date_eta_array[$demand_number]) > time() + $avg_delivery_time) {
                        return jsonReturn(0,$model->sku.','.$demand_number.',交期过长，请重新确认');
                    }
                }
                
                $pur_number = PurchaseDemand::find()->where(['demand_number'=>$demand_number])->select('pur_number')->scalar();
                
                //获取当前价格
                $quoteid = ProductProvider::find()->where(['sku'=>$model->sku,'is_supplier'=>1])->select('quotes_id')->scalar();
                $product_quote = SupplierQuotes::find()->where(['id'=>$quoteid])->one();
                $price = $product_quote->supplierprice;
                $pur_ticketed_point = 0;
                if ($is_drawback_array[$demand_number] == 2) {
                    if ($product_quote['is_back_tax'] != 1) {
                        //return jsonReturn(0,$model->sku.','.$demand_number.',此sku不可做退税');
                    }
                    $price += $price*$product_quote->pur_ticketed_point/100;
                    $pur_ticketed_point = $product_quote->pur_ticketed_point;
                    if ($pur_ticketed_point < 1) {
                        return jsonReturn(0,$model->sku.','.$demand_number.',退税采购必须有【开票点】');
                    }
                }
                
                PurchaseOrderItems::updateAll(['price'=>$price,'base_price'=>$product_quote->supplierprice,'pur_ticketed_point'=>$pur_ticketed_point], ['pur_number'=>$pur_number,'sku'=>$model->sku]);
                
                if ($model->purchase_quantity != $purchase_quantity_array[$demand_number]) {//修改了数量
                    $demand_check_model = new DemandCheck();
                    $demand_check_model->demand_number = $demand_number;
                    $demand_check_model->status = 1;
                    
                    $check_data = [];
                    $check_data['purchase_quantity'] = ['数量',$model->purchase_quantity,$purchase_quantity_array[$demand_number]];
                    if ($model->is_transit == 2) {
                        $transit_number = max(0, $model->transit_number + ($purchase_quantity_array[$demand_number] - $model->purchase_quantity));
                        $check_data['transit_number'] = ['中转数量',$model->transit_number,$transit_number];
                    }
                    $demand_check_model->data = json_encode($check_data);
                    $demand_check_model->create_time = date('Y-m-d H:i:s');
                    $demand_check_model->create_user = Yii::$app->user->identity->username;
                    $demand_check_model->remark = $purchase_quantity_note_array[$demand_number];
                    $demand_check_model->save(false);
                    
                    $model->demand_status = 2;
                    $model->audit_level = PlatformSummaryServices::getOverseasChecklevel(1, $price*$purchase_quantity_array[$demand_number]);
                }
                if ($model->demand_status == 1) {
                    $model->demand_status = 3;
                    $model->audit_level = PlatformSummaryServices::getOverseasChecklevel(2, $price*$purchase_quantity_array[$demand_number]);
                }
                //$model->is_transit = $is_transit_array[$demand_number];
                $model->demand_export_cname = $demand_export_cname_array[$demand_number];
                $model->demand_declare_unit = $demand_declare_unit_array[$demand_number];
                $model->transit_warehouse = $transit_warehouse_array[$demand_number];
                
                if (!isset($purchase_orders[$pur_number])) {

                    $order_model = PurchaseOrder::findOne(['pur_number'=>$pur_number]);
                    if ($order_model->is_drawback != $is_drawback_array[$demand_number] && empty($is_drawback_note_array[$demand_number])) {
                        return jsonReturn(0,$model->sku.','.$demand_number.',修改了是否退税未填写备注');
                    }
                    if (empty($order_model->supplier2->supplier_settlement)) {
                        return jsonReturn(0,$model->sku.','.$demand_number.',该供应商结算方式为空，请先维护供应商结算方式');
                    }
                    if (empty($order_model->supplier2->payment_method)) {
                        return jsonReturn(0,$model->sku.','.$demand_number.',该供应商支付方式为空，请先维护供应商支付方式');
                    }
                    $order_model->account_type = $order_model->supplier2->supplier_settlement;
                    $order_model->pay_type = $order_model->supplier2->payment_method;
                    $order_model->is_drawback = $is_drawback_array[$demand_number];
                    $order_model->date_eta = $date_eta_array[$demand_number];
                    $order_model->source = $source_array[$demand_number];
                    $order_model->transit_warehouse = $transit_warehouse_array[$demand_number];
                    $update_order_data[$pur_number] = CommonServices::getUpdateData($order_model, $update_data_param);
                    $order_model->save(false);
                    
                    $pay_type_model = PurchaseOrderPayType::findOne(['pur_number'=>$pur_number]);
                    $pay_type_model->settlement_ratio = $settlement_ratio_array[$demand_number];
                    $pay_type_model->purchase_source = $source_array[$demand_number];
                    $pay_type_model->freight_formula_mode = $freight_formula_mode_array[$demand_number];
                    $pay_type_model->freight_payer = $freight_payer_array[$demand_number];
                    //$pay_type_model->purchase_acccount = $purchase_acccount_array[$demand_number];
                    $update_order_data[$pur_number] = array_merge(
                        $update_order_data[$pur_number], 
                        CommonServices::getUpdateData($pay_type_model, $update_data_param)
                    );
                    $pay_type_model->save(false);
                    
                    $purchase_orders[$pur_number] = $order_model;
                }
                
                $log_message = "确认提交信息";
                if ($model->purchase_quantity != $purchase_quantity_array[$demand_number]) {
                    $log_message .= "<br>修改数量备注:".$purchase_quantity_note_array[$demand_number];
                }
                if ($purchase_orders[$pur_number]->isAttributeChanged('is_drawback',false)) {
                    $log_message .= "<br>修改是否退税备注:".$is_drawback_note_array[$demand_number];
                }
                $update_data_param2 = $update_data_param;
                if ($default_demand_export_cname_array[$demand_number] == $model->demand_export_cname) {
                    unset($update_data_param2['demand_export_cname']);
                }
                if ($default_demand_declare_unit_array[$demand_number] == $model->demand_declare_unit) {
                    unset($update_data_param2['demand_declare_unit']);
                }
                $update_data = array_merge(
                    $update_order_data[$pur_number],
                    CommonServices::getUpdateData($model, $update_data_param2)
                );
                PurchaseOrderServices::writelog($model->demand_number, $log_message, $pur_number, $update_data);
                
                $model->save(false);
            }
            
            $transaction->commit();
            
        } catch (Exception $e) {
            $transaction->rollBack();
            return jsonReturn(0,'数据异常！保存失败,请重试');
        }
        Yii::$app->getSession()->setFlash('success',"操作成功");
        return jsonReturn();
    }
    
    /**
     * 变更采购员
     */
    public function actionChangeBuyer()
    {
        if (isset($_POST['data'])) {
            $data = Yii::$app->request->post('data');
            if (empty($data)) {
                Yii::$app->getSession()->setFlash('warning','请至少选择一条数据');
                return $this->redirect(Yii::$app->request->referrer);
            }
            $demand_list = PlatformSummary::find()->where(['in','demand_number',$data])->asArray()->all();
            if (empty($demand_list)) {
                Yii::$app->getSession()->setFlash('warning','数据有误');
                return $this->redirect(Yii::$app->request->referrer);
            }
            foreach ($demand_list as $k=>$v) {
                if ($v['demand_status'] != 1) {
                    unset($demand_list[$k]);
                    continue;
                }
            }
            if (empty($demand_list)) {
                Yii::$app->getSession()->setFlash('warning','没有可以审核的数据');
                return $this->redirect(Yii::$app->request->referrer);
            }
            $demand_numbers = array_column($demand_list,'demand_number');
            $pur_numbers = PurchaseDemand::find()->where(['in','demand_number',$demand_numbers])->select('pur_number')->column();
            $purchases = PurchaseOrder::find()->where(['in','pur_number',$pur_numbers])->asArray()->all();
            $model = new PurchaseOrder();
            return $this->renderAjax('change-buyer',['model'=>$model,'data'=>$purchases]);
        }
        if (isset($_POST['PurchaseOrder'])) {
            $buyer = Yii::$app->request->post('PurchaseOrder');
            $buyer = $buyer['buyer'];
            $pur_numbers = Yii::$app->request->post('pur_numbers');
            
            $transaction = Yii::$app->db->beginTransaction();
            try {
                foreach ($pur_numbers as $pur_number) {
                    $order_model = PurchaseOrder::findOne(['pur_number'=>$pur_number]);
                    $order_model->buyer = $buyer;
                    
                    $demand_numbers = PurchaseDemand::find()->where(['pur_number'=>$pur_number])->select('demand_number')->column();
                    $update_data = CommonServices::getUpdateData($order_model, ['buyer'=>'采购员']);
                    PurchaseOrderServices::writelog($demand_numbers, '变更采购员', $pur_number, $update_data);
                    
                    $order_model->save(false);
                    
                    $buyer_model = SupplierBuyer::findOne(['supplier_code'=>$order_model->supplier_code,'type'=>2,'status'=>1]);
                    if (empty($buyer_model) || $buyer_model->buyer != $buyer) {
                        if (empty($buyer_model)) {
                            $buyer_model = new SupplierBuyer();
                            $buyer_model->supplier_code = $order_model->supplier_code;
                            $buyer_model->type = 2;
                            $buyer_model->status = 1;
                        }
                        $buyer_model->buyer = $buyer;
                        $buyer_model->save(false);
                    }
                }
                $transaction->commit();
                
            } catch (Exception $e) {
                $transaction->rollBack();
                Yii::$app->getSession()->setFlash('warning','数据异常！保存失败,请联系管理员');
                return $this->redirect(Yii::$app->request->referrer);
            }
            Yii::$app->getSession()->setFlash('success',"操作成功");
            return $this->redirect(Yii::$app->request->referrer);
        }
    }
    
    /**
     * 信息提交审核
     */
    public function actionInfoAudit()
    {
        $grade = PurchaseUser::find()->where(['pur_user_id'=>Yii::$app->user->identity->getId()])->select('grade')->scalar();
        if (isset($_POST['data'])) {
            if ($grade != 2 && $grade != 3) {
                Yii::$app->getSession()->setFlash('warning','您没有权限审核数据');
                return $this->redirect(Yii::$app->request->referrer);
            }
            $data = Yii::$app->request->post('data');
            if (empty($data)) {
                Yii::$app->getSession()->setFlash('warning','请至少选择一条数据');
                return $this->redirect(Yii::$app->request->referrer);
            }
            $demand_list = PlatformSummary::find()->where(['in','demand_number',$data])->asArray()->all();
            if (empty($demand_list)) {
                Yii::$app->getSession()->setFlash('warning','数据有误');
                return $this->redirect(Yii::$app->request->referrer);
            }
            foreach ($demand_list as $k=>$v) {
                if ( ($v['demand_status'] != 2) || ( $grade == 2 && $v['audit_level'] == 2 ) ) {
                    unset($demand_list[$k]);
                    continue;
                }
                $check_data = DemandCheck::find()->where(['demand_number'=>$v['demand_number'],'status'=>1])->asArray()->one();
                $check_data['data'] = json_decode($check_data['data'], true);
                $demand_list[$k]['check_data'] = $check_data;
            }
            if (empty($demand_list)) {
                Yii::$app->getSession()->setFlash('warning','没有可以审核的数据');
                return $this->redirect(Yii::$app->request->referrer);
            }
            return $this->renderAjax('info-audit',['data'=>$demand_list]);
        }
        if (isset($_POST['id'])) {
            $type = Yii::$app->request->post('type');
            $id = Yii::$app->request->post('id');
            $note = Yii::$app->request->post('note');
            
            $model = DemandCheck::findOne(['id'=>$id]);
            if ($model->status != 1) {
                return jsonReturn(0, '这个数据已经经过审核了');
            }
            if ($grade != 2 && $grade != 3) {
                return jsonReturn(0, '您没有权限审核数据');
            }
            $transaction = Yii::$app->db->beginTransaction();
            try {
                $demand_model = PlatformSummary::findOne(['demand_number'=>$model->demand_number]);
                if ($grade == 2 && $demand_model->audit_level == 2) {
                    return jsonReturn(0, '您没有权限审核数据');
                }
                $pur_number = PurchaseDemand::find()->where(['demand_number'=>$model->demand_number])->select('pur_number')->scalar();
                if ($type == 1) {//同意
                    $check_data = json_decode($model->data, true);
                    foreach ($check_data as $field=>$v) {
                        $demand_model->$field = $v[2];
                    }
                    if ($demand_model->demand_status == 2) {
                        $price = PurchaseOrderItems::find()->where(['pur_number'=>$pur_number,'sku'=>$demand_model->sku])->select('price')->scalar();
                        $demand_model->demand_status = 3;
                        $demand_model->audit_level = PlatformSummaryServices::getOverseasChecklevel(2, $price*$demand_model->purchase_quantity);
                    }
                    $model->status = 2;
                    $update_data = CommonServices::getUpdateData($demand_model, [
                        'purchase_quantity' => '数量',
                        'transit_number' => '中转数量',
                    ]);
                    PurchaseOrderServices::writelog($model->demand_number, '变更信息审核【通过】[审核备注:'.$note.']', $pur_number, $update_data);
                    
                    $demand_model->push_to_erp = 0;
                    $demand_model->is_push = 0;
                    
                } else {//驳回
                    if ($demand_model->demand_status == 2) {
                        $demand_model->demand_status = 1;
                        $demand_model->audit_level = 0;
                    }
                    $model->status = 3;
                    PurchaseOrderServices::writelog($model->demand_number, '变更信息审核【驳回】[驳回备注:'.$note.']', $pur_number);
                }
                
                $demand_model->save(false);
                
                $model->audit_note = $note;
                $model->audit_time = date('Y-m-d H:i:s');
                $model->audit_user = Yii::$app->user->identity->username;
                $model->save(false);
                
                $transaction->commit();
                
            } catch (Exception $e) {
                $transaction->rollBack();
                return jsonReturn(0, '数据保存失败，请重试');
            }
            return jsonReturn();
        }
    }
    
    /**
     * 采购审核
     */
    public function actionPurchaseAudit()
    {
        $grade = PurchaseUser::find()->where(['pur_user_id'=>Yii::$app->user->identity->getId()])->select('grade')->scalar();
        if (isset($_POST['data'])) {
            if ($grade != 2 && $grade != 3) {
                Yii::$app->getSession()->setFlash('warning','您没有权限审核');
                return $this->redirect(Yii::$app->request->referrer);
            }
            $data = Yii::$app->request->post('data');
            if (empty($data)) {
                Yii::$app->getSession()->setFlash('warning','请至少选择一条数据');
                return $this->redirect(Yii::$app->request->referrer);
            }
            $demand_list = PlatformSummary::find()->where(['in','demand_number',$data])->asArray()->all();
            if (empty($demand_list)) {
                Yii::$app->getSession()->setFlash('warning','数据有误');
                return $this->redirect(Yii::$app->request->referrer);
            }
            foreach ($demand_list as $k=>$v) {
                if ( ($v['demand_status'] != 3) || ( $grade == 2 && $v['audit_level'] == 2 ) ) {
                    unset($demand_list[$k]);
                    continue;
                }
            }
            if (empty($demand_list)) {
                Yii::$app->getSession()->setFlash('warning','没有可以审核的数据');
                return $this->redirect(Yii::$app->request->referrer);
            }
            return $this->renderAjax('purchase-audit',['data'=>$demand_list]);
        }
        if (isset($_POST['ids'])) {
            $type = Yii::$app->request->post('type');
            $ids = explode(',',Yii::$app->request->post('ids'));
            $note = Yii::$app->request->post('note');
            $models = PlatformSummary::find()->where(['in','demand_number',$ids])->all();
            if ($grade != 2 && $grade != 3) {
                return jsonReturn(0, '您没有权限审核数据');
            }
            $transaction = Yii::$app->db->beginTransaction();
            try {
                foreach ($models as $model) {
                    if ($model->demand_status != 3) {
                        continue;
                    }
                    if ($grade == 2 && $model->audit_level == 2) {
                        continue;
                    }
                    $pur_number = PurchaseDemand::find()->where(['demand_number'=>$model->demand_number])->select('pur_number')->scalar();
                    if ($type == 1) {//同意
                        $model->demand_status = 5;
                        PurchaseOrderServices::writelog($model->demand_number, '采购审核【通过】[审核备注:'.$note.']', $pur_number);
                    } else {//驳回
                        $model->demand_status = 1;
                        PurchaseOrderServices::writelog($model->demand_number, '采购审核【驳回】[驳回备注:'.$note.']', $pur_number);
                    }
                    $model->audit_level = 0;
                    $model->save(false);
                }
                
                $transaction->commit();
                
            } catch (Exception $e) {
                $transaction->rollBack();
                return jsonReturn(0, '数据保存失败，请重试');
            }
            return jsonReturn();
        }
    }
    
    /**
     * 销售审核
     */
    public function actionSaleAudit()
    {
        if (isset($_POST['data'])) {
            $data = Yii::$app->request->post('data');
            if (empty($data)) {
                Yii::$app->getSession()->setFlash('warning','请至少选择一条数据');
                return $this->redirect(Yii::$app->request->referrer);
            }
            $demand_list = PlatformSummary::find()->where(['in','demand_number',$data])->asArray()->all();
            if (empty($demand_list)) {
                Yii::$app->getSession()->setFlash('warning','数据有误');
                return $this->redirect(Yii::$app->request->referrer);
            }
            foreach ($demand_list as $k=>$v) {
                if ($v['demand_status'] != 5) {
                    unset($demand_list[$k]);
                    continue;
                }
            }
            if (empty($demand_list)) {
                Yii::$app->getSession()->setFlash('warning','没有可以审核的数据');
                return $this->redirect(Yii::$app->request->referrer);
            }
            return $this->renderAjax('sale-audit',['data'=>$demand_list]);
        }
        if (isset($_POST['ids'])) {
            $type = Yii::$app->request->post('type');
            $ids = explode(',',Yii::$app->request->post('ids'));
            $note = Yii::$app->request->post('note');
            $models = PlatformSummary::find()->where(['in','demand_number',$ids])->all();
            $transaction = Yii::$app->db->beginTransaction();
            try {
                foreach ($models as $model) {
                    if ($model->demand_status != 5) {
                        continue;
                    }
                    $pur_number = PurchaseDemand::find()->where(['demand_number'=>$model->demand_number])->select('pur_number')->scalar();
                    if ($type == 1) {//同意
                        $model->demand_status = 6;
                        PurchaseOrderServices::writelog($model->demand_number, '销售审核【通过】[审核备注:'.$note.']', $pur_number);
                    } else {//驳回
                        $model->demand_status = 1;
                        PurchaseOrderServices::writelog($model->demand_number, '销售审核【驳回】[驳回备注:'.$note.']', $pur_number);
                    }
                    $model->save(false);
                }
                
                $transaction->commit();
                
            } catch (Exception $e) {
                $transaction->rollBack();
                return jsonReturn(0, '数据保存失败，请重试');
            }
            return jsonReturn();
        }
    }
    
    /**
     * 驳回
     */
    public function actionPurchaseDisagree()
    {
        $ids = Yii::$app->request->post('id');
        $remark = Yii::$app->request->post('remark');
        $demand_list = PlatformSummary::find()->where(['in','demand_number',$ids])->all();
        $un_demands = [];
        foreach ($demand_list as $model) {
            if ($model->demand_status > 6) {
                $un_demands[] = "需求号:".$model->demand_number." 的状态不支持驳回";
                continue;
            }
        }
        if ($un_demands) {
            Yii::$app->getSession()->setFlash('warning', "只有【未生成进货单】状态的订单才能驳回.<br>".implode("<br>",$un_demands));
            return $this->redirect(Yii::$app->request->referrer);
        }
        
        $transaction = Yii::$app->db->beginTransaction();
        try {
            PlatformSummary::updateAll(['demand_status'=>1,'audit_level'=>0], ['in','demand_number',$ids]);
            foreach ($demand_list as $model) {
                if ($model->demand_status == 2) {
                    DemandCheck::updateAll(['status'=>4], ['demand_number'=>$model->demand_number,'status'=>1]);
                }
            }
            PurchaseOrderServices::writelog($ids, '待生成进货单驳回,驳回原因:'.$remark);
            $transaction->commit();
        } catch (Exception $e) {
            $transaction->rollBack();
            Yii::$app->getSession()->setFlash('warning','数据异常！保存失败,请重试');
            return $this->redirect(Yii::$app->request->referrer);
        }
        Yii::$app->getSession()->setFlash('success', '操作成功');
        return $this->redirect(Yii::$app->request->referrer);
    }
    
    /**
     * 拆分采购单
     */
    public function actionSplitPurchase()
    {
        if (isset($_POST['data'])) {
            $data = Yii::$app->request->post('data');
            if (empty($data)) {
                return '请至少选择一条数据';
            }
            $pur_numbers = PurchaseDemand::find()->where(['in','demand_number',$data])->select('pur_number')->column();
            $pur_numbers = array_unique($pur_numbers);
            if (count($pur_numbers) > 1) {
                return '只能选择一个采购单';
            }
            $pur_number = $pur_numbers[0];
            $demand_numbers = PurchaseDemand::find()->where(['pur_number'=>$pur_number])->select('demand_number')->column();
            $demand_list = PlatformSummary::find()->where(['in','demand_number',$demand_numbers])->andWhere("demand_status != 14")->asArray()->all();
            if (empty($demand_list) || $demand_list[0]['demand_status'] > 6) {
                return '只有未生成进货单的采购单才能拆分';
            }
            if (count($demand_list) < 2) {
                return '采购单包含的个未作废需求少于2个，不能拆分';
            }
            return $this->renderAjax('split-purchase',['pur_number'=>$pur_number,'data'=>$demand_list]);
        }
        if (isset($_POST['split_purchase_form'])) {
            $new_demand_number = Yii::$app->request->post('new_demand_number');
            if (empty($new_demand_number)) {
                return jsonReturn(0, '请选择要拆分的需求');
            }
            $pur_number = Yii::$app->request->post('pur_number');
            $demand_numbers = PurchaseDemand::find()->where(['pur_number'=>$pur_number])->select('demand_number')->column();
            $demand_list = PlatformSummary::find()->where(['in','demand_number',$demand_numbers])->andWhere("demand_status != 14")->asArray()->all();
            if (count($demand_list) == count($new_demand_number)) {
                return jsonReturn(0, '不能把原来的采购单全部拆走啊');
            }
            $transaction = Yii::$app->db->beginTransaction();
            try {
                $skus = [];
                foreach ($new_demand_number as $demand_number) {
                    $model = PlatformSummary::findOne(['demand_number'=>$demand_number]);
                    if ($model->demand_status != 1) {
                        return jsonReturn(0, $demand_number.',当前状态不能拆单');
                    }
                    $sku = strtoupper($model->sku);
                    if (isset($skus[$sku])) {
                        $skus[$sku]['qty'] += $model->purchase_quantity;
                    } else {
                        $skus[$sku]['name'] = $model->product_name;
                        $skus[$sku]['qty'] = $model->purchase_quantity;
                    }
                }
                
                $purchase_model = PurchaseOrder::findOne(['pur_number'=>$pur_number]);
                $model_order = new PurchaseOrder();
                $model_order->pur_number      = CommonServices::getNumber('ABD');
                $model_order->operation_type  ='2';
                $model_order->warehouse_code = $purchase_model->warehouse_code;
                $model_order->supplier_code = $purchase_model->supplier_code;
                $model_order->e_supplier_name = $model_order->supplier_name = $purchase_model->supplier_name;
                $model_order->created_at      = date('Y-m-d H:i:s');
                $model_order->creator         = Yii::$app->user->identity->username;
                $model_order->merchandiser  = $purchase_model->merchandiser;
                $model_order->buyer           = $purchase_model->buyer;
                $model_order->purchas_status  = 1;//待确认
                $model_order->create_type     = 1;//创建类型
                $model_order->is_transit      = $purchase_model->is_transit;
                $model_order->purchase_type   = 2;//海外
                $model_order->e_account_type  = $model_order->account_type = $purchase_model->account_type;
                $model_order->transit_warehouse   = $purchase_model->transit_warehouse;//中转
                $model_order->pay_type = $purchase_model->pay_type;
                $model_order->is_drawback = $purchase_model->is_drawback;
                $model_order->shipping_method = $purchase_model->shipping_method;
                $model_order->source = $purchase_model->source;
                $model_order->save(false);
                
                $pay_model = PurchaseOrderPayType::findOne(['pur_number'=>$pur_number]);
                $model_order_type = new PurchaseOrderPayType();
                $model_order_type->pur_number              = $model_order->pur_number;
                $model_order_type->freight_formula_mode    = $pay_model->freight_formula_mode;
                $model_order_type->purchase_source         = 2;
                $model_order_type->save(false);
                
                foreach ($skus as $sku=>$sku_info) {
                    $item_model = new PurchaseOrderItems();
                    $item_model->pur_number = $model_order->pur_number;
                    $item_model->sku = $sku;
                    $item_model->name = $sku_info['name'];
                    $item_model->save(false);
                }
                
                PurchaseDemand::updateAll(['pur_number'=>$model_order->pur_number], ['in','demand_number',$new_demand_number]);
                
                //修正order_items表
                $demand_numbers = PurchaseDemand::find()->where(['pur_number'=>$pur_number])->select('demand_number')->column();
                $order_item_model = PurchaseOrderItems::findAll(['pur_number'=>$pur_number]);
                foreach ($order_item_model as $item_model) {
                    $qty = PlatformSummary::find()->select("sum(purchase_quantity) as purchase_quantity")
                        ->where(['in','demand_number',$demand_numbers])
                        ->andWhere(['sku'=>$item_model->sku])
                        ->scalar();
                    if (empty($qty)) {
                        $item_model->delete();
                    } else {
                        $item_model->ctq = $qty;
                        $item_model->save(false);
                    }
                }
                
                PurchaseOrderServices::writelog($new_demand_number, "采购单拆单<br>采购单号：{$pur_number} -> {$model_order->pur_number}");
                
                $transaction->commit();
                
            } catch (Exception $e) {
                $transaction->rollBack();
                Yii::$app->getSession()->setFlash('warning','数据异常！保存失败,请联系管理员');
                return $this->redirect(Yii::$app->request->referrer);
            }
            Yii::$app->getSession()->setFlash('success',"操作成功");
            return $this->redirect(Yii::$app->request->referrer);
        }
    }
    
    /**
     * 生成进货单
     */
    public function actionConfirmOrder()
    {
        $ids = Yii::$app->request->post('id');
        $demands = PurchaseDemand::find()->where(['in','demand_number',$ids])->asArray()->all();
        $pur_numbers = array_unique(array_column($demands, 'pur_number'));
        $demand_matchs = PurchaseDemand::find()->where(['in','pur_number',$pur_numbers])->all();
        $demand_numbers = $demand_match_purs = [];
        foreach ($demand_matchs as $v) {
            $demand_numbers[] = $v->demand_number;
            $demand_match_purs[$v->demand_number] = $v->pur_number;
        }
        foreach ($demand_numbers as $demand_number) {
            if (!in_array($demand_number, $ids)) {
                Yii::$app->getSession()->setFlash('warning',"请勾选PO下所有的需求");
                return $this->redirect(Yii::$app->request->referrer);
            }
        }
        $demand_list = PlatformSummary::find()->where(['in','demand_number',$demand_numbers])->all();
        $un_demands = $sku_numbers = [];
        foreach ($demand_list as $model) {
            if ($model->demand_status > 6) {
                Yii::$app->getSession()->setFlash('warning',"订单状态异常,需求号:".$model->demand_number." ,订单状态:".PurchaseOrderServices::getOverseasOrderStatus($model->demand_status));
                return $this->redirect(Yii::$app->request->referrer);
            }
            if ($model->demand_status < 6) {
                $un_demands[] = "PO:".$demand_match_purs[$model->demand_number].", 需求号:".$model->demand_number.", 订单状态:".PurchaseOrderServices::getOverseasOrderStatus($model->demand_status);
            }
            $sku = strtoupper($model->sku);
            if (isset($sku_numbers[$demand_match_purs[$model->demand_number]][$sku])) {
                $sku_numbers[$demand_match_purs[$model->demand_number]][$sku] += $model->purchase_quantity;
            } else {
                $sku_numbers[$demand_match_purs[$model->demand_number]][$sku] = $model->purchase_quantity;
            }
        }
        if ($un_demands) {
            Yii::$app->getSession()->setFlash('warning',"有未经过审核的需求<br>".implode("<br>",$un_demands));
            return $this->redirect(Yii::$app->request->referrer);
        }
        $purchase_orders = PurchaseOrder::find()->where(['in','pur_number',$pur_numbers])->asArray()->all();
        $_sources = array_unique(array_column($purchase_orders,'source'));
        if (count($_sources) > 1) {
            Yii::$app->getSession()->setFlash('warning',"【合同采购订单】和【网采订单】不能一起生成进货单");
            return $this->redirect(Yii::$app->request->referrer);
        }
        $source = $purchase_orders[0]['source'];
        if ($source == 1) {//合同
            if (count(array_unique(array_column($purchase_orders,'supplier_code'))) > 1) {
                Yii::$app->getSession()->setFlash('warning',"【供应商】不一致，不能生成同个合同");
                return $this->redirect(Yii::$app->request->referrer);
            }
            if (count(array_unique(array_column($purchase_orders,'is_drawback'))) > 1) {
                Yii::$app->getSession()->setFlash('warning',"【是否退税】不一致，不能生成同个合同");
                return $this->redirect(Yii::$app->request->referrer);
            }
            if (count(array_unique(array_column($purchase_orders,'pay_type'))) > 1) {
                Yii::$app->getSession()->setFlash('warning',"【支付方式】不一致，不能生成同个合同");
                return $this->redirect(Yii::$app->request->referrer);
            }
            if (count(array_unique(array_column($purchase_orders,'shipping_method'))) > 1) {
                Yii::$app->getSession()->setFlash('warning',"【供应商运输方式】不一致，不能生成同个合同");
                return $this->redirect(Yii::$app->request->referrer);
            }
            if (count(array_unique(array_column($purchase_orders,'account_type'))) > 1) {
                Yii::$app->getSession()->setFlash('warning',"【结算方式】不一致，不能生成同个合同");
                return $this->redirect(Yii::$app->request->referrer);
            }
            $date_eta = substr($purchase_orders[0]['date_eta'],0,10);
            foreach ($purchase_orders as $k=>$v) {
                if ($k == 0) continue;
                if ($date_eta != substr($v['date_eta'],0,10)) {
                    Yii::$app->getSession()->setFlash('warning',"【预计到货时间】日期不一致，不能生成同个合同");
                    return $this->redirect(Yii::$app->request->referrer);
                }
            }
            if (count(array_unique(array_column($purchase_orders,'transit_warehouse'))) > 1) {
                Yii::$app->getSession()->setFlash('warning',"【中转仓库】不一致，不能生成同个合同");
                return $this->redirect(Yii::$app->request->referrer);
            }
            $paytypes = PurchaseOrderPayType::find()->where(['in','pur_number',$pur_numbers])->asArray()->all();
            if (count(array_unique(array_column($paytypes,'settlement_ratio'))) > 1) {
                Yii::$app->getSession()->setFlash('warning',"【结算比例】不一致，不能生成同个合同");
                return $this->redirect(Yii::$app->request->referrer);
            }
            
            foreach ($sku_numbers as $pur_number=>$v) {
                foreach ($v as $sku=>$qty) {
                    PurchaseOrderItems::updateAll(['ctq'=>$qty],['pur_number'=>$pur_number,'sku'=>$sku]);
                }
            }
            Yii::$app->getSession()->setFlash('success',"生成进货单成功，请确认采购合同");
            $pur_numbers = implode(',',$pur_numbers);
            return $this->redirect(['compact-confirm','ids'=>$pur_numbers]);
        }
        
        $transaction = Yii::$app->db->beginTransaction();
        try {
            PlatformSummary::updateAll(['demand_status'=>7,'transit_warehouse'=>$purchase_orders[0]['transit_warehouse']],['in','demand_number',$demand_numbers]);
            $username = Yii::$app->user->identity->username;
            PurchaseOrder::updateAll(['purchas_status'=>3,'audit_time'=>date('Y-m-d H:i:s'),'auditor'=>$username],['in','pur_number',$pur_numbers]);
            foreach ($sku_numbers as $pur_number=>$v) {
                foreach ($v as $sku=>$qty) {
                    PurchaseOrderItems::updateAll(['ctq'=>$qty],['pur_number'=>$pur_number,'sku'=>$sku]);
                }
            }
            foreach ($demand_match_purs as $demand_number=>$pur_number) {
                PurchaseOrderServices::writelog($demand_number, '生成进货单', $pur_number);
            }
            
            $transaction->commit();
            
        } catch (Exception $e) {
            $transaction->rollBack();
            Yii::$app->getSession()->setFlash('warning','数据异常！保存失败,请重试');
            return $this->redirect(Yii::$app->request->referrer);
        }
        Yii::$app->getSession()->setFlash('success',"操作成功");
        return $this->redirect(Yii::$app->request->referrer);
    }
    
    /**
     * 确认合同信息
     */
    public function actionCompactConfirm()
    {
        $ids = Yii::$app->request->get('ids');
        if(!$ids) {
            throw new \yii\web\NotFoundHttpException('参数错误');
        }
        $ids = explode(',', $ids);
        $models = PurchaseOrder::find()->where(['in','pur_number',$ids])->all();
        if (empty($models)) {
            throw new \yii\web\NotFoundHttpException('参数错误');
        }
        $demands = PurchaseDemand::find()->where(['in','pur_number',$ids])->asArray()->all();
        $demand_numbers = array_column($demands,'demand_number');
        if (PlatformSummary::find()->where(['in','demand_number',$demand_numbers])->andWhere("demand_status != 6")->one()) {
            throw new \yii\web\NotFoundHttpException('订单状态异常，包含有【非 等待生成进货单】状态的需求');
        }
        if(Yii::$app->request->isPost) {
            //保存备注
            $notes = Yii::$app->request->post('note');
            $note_params = [];
            foreach ($notes as $pur_number=>$note) {
                $note_params[] = ['pur_number'=>$pur_number,'note'=>$note];
            }
            $model_note = new PurchaseNote();
            $model_note->saveNotes($note_params);
            //写入日志
            foreach ($demands as $v) {
                PurchaseOrderServices::writelog($v['demand_number'], "合同采购确认,备注:".$notes[$v['pur_number']], $v['pur_number']);
            }
            return $this->redirect(["compact-create",'ids'=>Yii::$app->request->get('ids')]);
        }
        
        $supplier_codes = array_unique(array_column(ArrayHelper::toArray($models),'supplier_code'));
        if (count($supplier_codes) > 1) {
            throw new \yii\web\NotFoundHttpException('存在不同的供应商');
        }
        return $this->render('compact-confirm', [
            'ids' => $ids,
            'models' => $models,
        ]);
    }
    
    /**
     * 创建合同
     */
    public function actionCompactCreate()
    {
        $tid = 5;
        $ids = Yii::$app->request->get('ids');
        if(!$ids) {
            throw new \yii\web\NotFoundHttpException('参数错误');
        }
        $ids = explode(',', $ids);
        $models = PurchaseOrder::find()->where(['in','pur_number',$ids])->all();
        if (empty($models)) {
            throw new \yii\web\NotFoundHttpException('参数错误');
        }
        $demands = PurchaseDemand::find()->where(['in','pur_number',$ids])->asArray()->all();
        $demand_numbers = array_column($demands,'demand_number');
        if (PlatformSummary::find()->where(['in','demand_number',$demand_numbers])->andWhere("demand_status != 6")->one()) {
            throw new \yii\web\NotFoundHttpException('订单状态异常，包含有【非 等待生成进货单】状态的需求');
        }
        if(Yii::$app->request->isPost) {
            
            $transaction = Yii::$app->db->beginTransaction();
            try {
                PlatformSummary::updateAll(['demand_status'=>7],['in','demand_number',$demand_numbers]);
                $username = Yii::$app->user->identity->username;
                PurchaseOrder::updateAll(['purchas_status'=>4,'audit_time'=>date('Y-m-d H:i:s'),'auditor'=>$username],['in','pur_number',$ids]);
                
                foreach ($demands as $v) {
                    PurchaseOrderServices::writelog($v['demand_number'], "合同确认，生成进货单", $v['pur_number']);
                }
                $compact = Yii::$app->request->post('compact');
                $model = new PurchaseCompact();
                $model->attributes = $compact;
                $model->compact_number = CommonServices::getNumber('ABD-HT');
                $model->tpl_id         = $tid;
                $model->source         = 2;
                $model->compact_status = 3;
                $model->guige = json_encode($compact['guige']);
                $model->create_time    = date('Y-m-d H:i:s', time());
                $model->create_person_name = Yii::$app->user->identity->username;
                $model->create_person_id   = Yii::$app->user->id;
                $model->save(false);
                
                $items = [];
                foreach($ids as $pur_number) {
                    $items[] = [
                        'compact_number' => $model->compact_number,
                        'pur_number' => $pur_number
                    ];
                }
                Yii::$app->db->createCommand()
                ->batchInsert('pur_purchase_compact_items', ['compact_number', 'pur_number'], $items)
                ->execute();
                
                $transaction->commit();
                
            } catch (Exception $e) {
                $transaction->rollBack();
                Yii::$app->getSession()->setFlash('warning','数据异常！保存失败,请重试');
                return $this->redirect(Yii::$app->request->referrer);
            }
            Yii::$app->getSession()->setFlash('success',"操作成功");
            return $this->redirect(['index','OverseasPurchaseOrderSearch[compact_number]'=>$model->compact_number]);
        }
        $supplier_codes = array_unique(array_column(ArrayHelper::toArray($models),'supplier_code'));
        if (count($supplier_codes) > 1) {
            throw new \yii\web\NotFoundHttpException('存在不同的供应商');
        }
        
        $result = PurchaseOrder::getOverseasCompactGeneralData($ids);
        
        $tpl = Template::findOne($tid);
        
        return $this->render("//template/tpls/{$tpl->style_code}", ['data'=>$result]);
    }
    
    
    /**
     * 添加备注
     */
    public function actionAddNote()
    {
        $ids = Yii::$app->request->post('id');
        $note = Yii::$app->request->post('remark');
        foreach ($ids as $demand_number) {
            $model = new PurchaseNote();
            $model->demand_number = $demand_number;
            $model->pur_number = PurchaseDemand::find()->where(['demand_number'=>$demand_number])->select('pur_number')->scalar();
            $model->note = $note;
            $model->create_time = date('Y-m-d H:i:s');
            $model->purchase_type = 2;
            $model->create_id = Yii::$app->user->identity->getId();
            $model->create_user = Yii::$app->user->identity->username;
            $model->save(false);
            
            PurchaseOrderServices::writelog($demand_number, "添加备注:\r\n".$note, $model->pur_number);
        }
        Yii::$app->getSession()->setFlash('success',"添加成功");
        return $this->redirect(Yii::$app->request->referrer);
    }
    
    public function actionPayApply()
    {
        $request = Yii::$app->request;
        $demand_numbers = $request->post('id');
        if (empty($demand_numbers)) {
            return $this->redirect(['index']);
        }
        $purchase_demands = PurchaseDemand::find()->where(['in','demand_number',$demand_numbers])->all();
        $demand_maps = [];
        foreach ($purchase_demands as $v) {
            $demand_maps[$v->demand_number] = $v->pur_number;
        }
        $pur_numbers = array_unique(array_values($demand_maps));
        $purchases = PurchaseOrder::find()->where(['in','pur_number',$pur_numbers])->all();
        $source = 0;
        foreach ($purchases as $v) {
            if ($source == 0) {
                $source = $v->source;
            } elseif ($source != $v->source) {
                Yii::$app->getSession()->setFlash('warning','选择的需求必须有相同的【采购来源】');
                return $this->redirect(Yii::$app->request->referrer);
            }
        }
        $compact_number = '';
        if ($source == 2 && count($pur_numbers) > 1) {
            Yii::$app->getSession()->setFlash('warning','网采单必须是【相同的PO】才能一起请款');
            return $this->redirect(Yii::$app->request->referrer);
        }
        $compact_model = '';
        if ($source == 1) {
            $compact_numbers = PurchaseCompactItems::find()->where(['in','pur_number',$pur_numbers])->andwhere(['bind'=>1])->select('compact_number')->column();
            $compact_numbers = array_unique($compact_numbers);
            if (count($compact_numbers) > 1) {
                Yii::$app->getSession()->setFlash('warning','合同单必须是【同一个合同】才能一起请款');
                return $this->redirect(Yii::$app->request->referrer);
            }
            $compact_number = $compact_numbers[0];
            $compact_model = PurchaseCompact::findOne(['compact_number'=>$compact_number]);
        }
        
        $demands = PlatformSummary::find()->where(['in','demand_number',$demand_numbers])->all();
        foreach ($demands as $model) {
            if (!in_array($model->demand_status, [7,8,9,10,11])) {
                Yii::$app->getSession()->setFlash('warning','需求单【'.$model->demand_number.'】的状态不能申请付款');
                return $this->redirect(Yii::$app->request->referrer);
            }
            
            $model->price = PurchaseOrderItems::find()->where(['sku'=>$model->sku,'pur_number'=>$demand_maps[$model->demand_number]])->select('price')->scalar();
            $model->cancel_cty = PurchaseOrderServices::getOverseasDemandCancelCty($model->demand_number);
        }
        
        $pay_maps_data = OrderPayDemandMap::find()->where(['in','demand_number',$demand_numbers])->all();
        $pay_maps = $has_amount = $cancel_amount = $check_requisition_numbers = [];
        $pay_amount_total = 0;
        if ($pay_maps_data) {
            foreach ($pay_maps_data as $v) {
                if (!in_array($v->requisition_number, $check_requisition_numbers)) {
                    $pay_type_model = PurchaseOrderPay::findOne(['requisition_number'=>$v->requisition_number]);
                    if(in_array($pay_type_model->pay_status, $this->undonePay)) {
                        Yii::$app->getSession()->setFlash('warning',"存在没有支付的申请,需求号:{$v->demand_number},申请单号:{$v->requisition_number}");
                        return $this->redirect(Yii::$app->request->referrer);
                    }
                    $check_requisition_numbers[$v->requisition_number] = $pay_type_model;
                }
                if (!in_array($check_requisition_numbers[$v->requisition_number]->pay_status,[5,6])) continue;
                $pay_maps[$v->demand_number] = $v;
                if (isset($has_amount[$v->demand_number])) {
                    $has_amount[$v->demand_number] += $v->pay_amount;
                } else {
                    $has_amount[$v->demand_number] = $v->pay_amount;
                }
                $pay_amount_total += $v->pay_amount;
            }
        }
        
        if (isset($_POST['pay_amount'])) {
            
            $paytype_model = $purchases[0]->purchaseOrderPayType;
            
            $freights = $request->post("freight");
            $discounts = $request->post("discount");
            $price_types = $request->post("price_type");
            $pay_ratios = $request->post("pay_ratio");
            $pay_amounts = $request->post("pay_amount");
            
            $transaction = Yii::$app->db->beginTransaction();
            try {
                
                $pay_model = new PurchaseOrderPay();
                $pay_model->requisition_number = CommonServices::getNumber('PP');
                $pay_model->pay_status = 10;
                $pay_model->pur_number = $source == 1 ? $compact_number : $purchases[0]->pur_number;
                $pay_model->supplier_code = $purchases[0]->supplier_code;
                $pay_model->settlement_method = $purchases[0]->account_type;
                $pay_model->create_notice = $request->post('create_notice');
                $pay_model->applicant = Yii::$app->user->id;
                $pay_model->application_time = date('Y-m-d H:i:s');
                $pay_model->pay_type = $purchases[0]->pay_type;
                $pay_model->currency = $purchases[0]->currency_code;
                $pay_model->source = $source;
                $pay_model->js_ratio = $paytype_model->settlement_ratio;
                $pay_model->purchase_account = $request->post('purchase_account');
                $pay_model->pai_number = $request->post('pai_number');
                
                $order_freight = round($request->post("order_freight"),2);
                if ($order_freight > 0) {
                    $paytype_model->freight = $order_freight;
                }
                $order_discount = round($request->post("order_discount"),2);
                if ($order_discount > 0) {
                    $paytype_model->discount = $order_discount;
                }
                if ($pay_model->purchase_account) {
                    $paytype_model->purchase_acccount = $pay_model->purchase_account;
                }
                if ($pay_model->pai_number) {
                    $paytype_model->platform_order_number = $pay_model->pai_number;
                }
                $update_data = CommonServices::getUpdateData($paytype_model, [
                    'purchase_acccount'=>'账号',
                    'platform_order_number'=>'拍单号',
                ]);
                $paytype_model->save(false);
                
                if ($source == 1 && ($order_freight > 0 || $order_discount > 0)) {
                    if ($order_freight > 0) {
                        $compact_model->freight = $order_freight;
                    }
                    if ($order_discount > 0) {
                        $compact_model->discount = $order_discount;
                    }
                    $compact_model->real_money = $compact_model->product_money + $compact_model->freight - $compact_model->discount;
                    $compact_model->save(false);
                }
                $pay_price = 0;
                foreach ($demands as $demand) {
                    
                    $demand_number = $demand->demand_number;
                    $model = new OrderPayDemandMap();
                    $model->demand_number = $demand_number;
                    $model->requisition_number = $pay_model->requisition_number;
                    $model->freight = round($freights[$demand_number],2);
                    $model->discount = round($discounts[$demand_number],2);
                    $model->price_type = intval($price_types[$demand_number]);
                    $model->pay_ratio = isset($pay_ratios[$demand_number]) ? intval($pay_ratios[$demand_number]) : 0;
                    $model->pay_amount = round($pay_amounts[$demand_number],2);
                    
                    $item_totalprice = $demand->price * $demand->purchase_quantity;                    
                    $item_has_price = isset($has_amount[$demand->demand_number]) ? $has_amount[$demand->demand_number] : 0;
                    if ($item_totalprice < $item_has_price + $model->pay_amount) {
                        Yii::$app->getSession()->setFlash('warning','支付金额超过了总金额,需求单号:'.$demand_number.',sku:'.$demand->sku);
                        return $this->redirect(Yii::$app->request->referrer);
                    }
                    
                    $model->save(false);
                    
                    if ($demand->pay_status != 6) {
                        $demand->pay_status = 10;
                        $demand->save(false);
                    }
                    
                    $log_message = "申请付款,请款单:{$model->requisition_number}\r\n";
                    $log_message .= $model->price_type == 1 ? '【比例请款】' : '【手动请款】';
                    $log_message .= '【运费：'.$model->freight.'】';
                    $log_message .= '【优惠：'.$model->discount.'】';
                    if ($model->price_type == 1) {
                        $log_message .= '【结算比例：'.$model->pay_ratio.'%】';
                    }
                    $log_message .= '【请款金额：'.$model->pay_amount.'】';
                    PurchaseOrderServices::writelog($demand_number, $log_message, '', $update_data);
                    $pay_price += $model->pay_amount;
                }
                if ($source == 1) {
                    $pay_model->pay_price = $pay_price + $order_freight - $order_discount;
                } else {
                    $pay_model->pay_price = $pay_price;
                }
                
                $pay_model->save(false);
                
                if ($source == 1) {
                    $payform = new PurchasePayForm();
                    $payform->compact_number = $compact_number;
                    $payform->pay_id = $pay_model->id;
                    $payform->pay_price = $pay_model->pay_price;
                    $payform->fk_name = BaseServices::getBuyerCompany($purchases[0]->is_drawback,'name');
                    $payform->create_time = date('Y-m-d H:i:s');
                    $payform->supplier_name = $purchases[0]->supplier_name;
                    $payform->account = $request->post('account');
                    $payform->payment_platform_branch = $request->post('payment_platform_branch');
                    $payform->payment_reason = $request->post('payment_reason');
                    $payform->tpl_id = 2;
                    $payform->save(false);
                }
                
                $transaction->commit();
                
            } catch (Exception $e) {
                $transaction->rollBack();
                Yii::$app->getSession()->setFlash('warning','数据异常！保存失败,请重试');
                return $this->redirect(Yii::$app->request->referrer);
            }
            
            Yii::$app->getSession()->setFlash('success','操作成功');
            return $this->redirect(['index']);
        }
        
        return $this->render('pay-apply', [
            'model' => $purchases[0],
            'items' => $demands,
            'has_amount' => $has_amount,
            'compact_number' => $compact_number,
            'compact_model' => $compact_model,
            'pur_numbers' => $pur_numbers,
            'demand_maps' => $demand_maps,
            'pay_amount_total' => $pay_amount_total,
            'pay_model' => $purchases[0]->purchaseOrderPayType,
        ]);
    }
    
    public function actionGetRmb()
    {
        $price = Yii::$app->request->post('price');
        return Vhelper::num_to_rmb($price);
    }
    
    //未使用
    public function actionPayApplyPaper()
    {
        $id = Yii::$app->request->get('id');
        $model = PurchaseOrderPay::findOne(['requisition_number'=>$id]);
        return $this->render('pay-apply-paper', [
            'model' => $model,
        ]);
    }
    
    /**
     * 请款单
     */
    public function actionPayment()
    {
        $args = Yii::$app->request->queryParams;
        $searchModel = new OverseasPaymentSearch();
        $dataProvider = $searchModel->search($args);
        
        $userid = Yii::$app->user->identity->getId();
        $fields = TableFields::find()->where(['userid'=>$userid,'table_name'=>'overseas_payment'])->select('data')->scalar();
        $fields = $fields ? json_decode($fields, true) : [];
        
        return $this->render('payment', [
            'model' => $searchModel,
            'dataProvider' => $dataProvider,
            'fields' => $fields,
        ]);
    }
    
    /**
     * 请款单审核
     */
    public function actionPaymentAudit()
    {
        $grade = PurchaseUser::find()->where(['pur_user_id'=>Yii::$app->user->identity->getId()])->select('grade')->scalar();
        if (isset($_POST['data'])) {
            if ($grade != 2 && $grade != 3) {
                Yii::$app->getSession()->setFlash('warning','您没有权限审核');
                return $this->redirect(Yii::$app->request->referrer);
            }
            $data = Yii::$app->request->post('data');
            if (empty($data)) {
                Yii::$app->getSession()->setFlash('warning','请至少选择一条数据');
                return $this->redirect(Yii::$app->request->referrer);
            }
            $models = PurchaseOrderPay::find()->where(['in','id',$data])->asArray()->all();
            if (empty($models)) {
                Yii::$app->getSession()->setFlash('warning','数据有误');
                return $this->redirect(Yii::$app->request->referrer);
            }
            foreach ($models as $k=>$model) {
                $audit_leve = PlatformSummaryServices::getOverseasChecklevel(3, $model['pay_price']);
                if ( ($model['pay_status'] != 10) || ( $grade == 2 && $audit_leve == 2 ) ) {
                    unset($models[$k]);
                    continue;
                }
            }
            if (empty($models)) {
                Yii::$app->getSession()->setFlash('warning','没有可以审核的数据');
                return $this->redirect(Yii::$app->request->referrer);
            }
            return $this->renderAjax('payment-audit',['data'=>$models]);
        }
        if (isset($_POST['ids'])) {
            $type = Yii::$app->request->post('type');
            $ids = explode(',',Yii::$app->request->post('ids'));
            $note = Yii::$app->request->post('note');
            $models = PurchaseOrderPay::find()->where(['in','id',$ids])->all();
            if ($grade != 2 && $grade != 3) {
                return jsonReturn(0, '您没有权限审核数据');
            }
            $transaction = Yii::$app->db->beginTransaction();
            try {
                foreach ($models as $model) {
                    if ($model->pay_status != 10) {
                        continue;
                    }
                    $audit_leve = PlatformSummaryServices::getOverseasChecklevel(3, $model->pay_price);
                    if ($grade == 2 && $audit_leve == 2) {
                        continue;
                    }
                    $demand_numbers = OrderPayDemandMap::find()->where(['requisition_number'=>$model->requisition_number])->select('demand_number')->column();
                    if ($type == 1) {//同意
                        $model->pay_status = 2;
                        PurchaseOrderServices::writelog($demand_numbers, "请款单采购审核【通过】\r\n审核备注:{$note}\r\n请款单:{$model->requisition_number}");
                    } else {//驳回
                        $model->pay_status = 11;
                        PurchaseOrderServices::writelog($demand_numbers, "请款单采购审核【驳回】\r\n审核备注:{$note}\r\n请款单:{$model->requisition_number}");
                    }
                    $model->auditor = Yii::$app->user->identity->id;
                    $model->review_time = date('Y-m-d H:i:s');
                    $model->save(false);
                    
                    PlatformSummary::updateAll(['pay_status'=>$model->pay_status], ['in','demand_number',$demand_numbers]);
                }
                
                $transaction->commit();
                
            } catch (Exception $e) {
                $transaction->rollBack();
                return jsonReturn(0, '数据保存失败，请重试');
            }
            return jsonReturn();
        }
        
        $ids = Yii::$app->request->post('id');
        $models = PurchaseOrderPay::find()->where(['in','id',$ids])->all();
        foreach ($models as $model) {
            if ($model->pay_status != 10) {
                Yii::$app->getSession()->setFlash('warning','订单状态不能进行采购审核：'.$model->requisition_number);
                return $this->redirect(Yii::$app->request->referrer);
            }
            if ($grade == 2) {
                $audit_leve = PlatformSummaryServices::getOverseasChecklevel(3, $model->pay_price);
                if ($audit_leve == 2) {
                    Yii::$app->getSession()->setFlash('warning','此单需经理审核：'.$model->requisition_number);
                    return $this->redirect(Yii::$app->request->referrer);
                }
            }
        }
        PurchaseOrderPay::updateAll(['pay_status'=>2], ['in','id',$ids]);
        Yii::$app->getSession()->setFlash('warning','操作成功');
        return $this->redirect(Yii::$app->request->referrer);
    }
    
    /**
     * 取消订单
     */
    public function actionCancelOrder()
    {
        // if (isset($_POST['data'])) {
        if (Yii::$app->request->isAjax) {
            $ids = Yii::$app->request->post('data');
            $demands = PlatformSummary::find()->where(['in','demand_number',$ids])->asArray()->all();
            //判断需求是否完结
            foreach ($demands as $demand) {
                if (in_array($demand['demand_status'],[9,11,12,13,14])) {
                    Yii::$app->getSession()->setFlash('warning',"需求:{$demand['demand_status']},sku:{$demand['sku']},当前状态不能作废");
                    return $this->redirect(Yii::$app->request->referrer);
                }
            }
            //获取需求和采购单号关联数据
            $purchaseDemands = PurchaseDemand::find()->where(['in','demand_number',$ids])->all();
            $pur_numbers = $purchase_demand_sku_map = [];

            foreach ($purchaseDemands as $v) {
                if (!in_array($v->pur_number,$pur_numbers)) {
                    $pur_numbers[] = $v->pur_number;
                }
                //保存PO，需求号，sku的关系
                $purchase_demand_sku_map[] = ['pur_number' =>$v->pur_number, 'demand_number'=>$v->demand_number, 'sku'=>$v->platformSummary->sku];
            }

            $res_unique = array_unique($pur_numbers);
            //不是一个单的，不能作废
            if ( count($res_unique) !==1 ) {
                Yii::$app->getSession()->setFlash('warning',"同一个采购单的需求才可以作废");
                return $this->redirect(Yii::$app->request->referrer);
            }

            $res_cancel = PurchaseOrderCancel::find()->where(['pur_number'=>$res_unique[0],'audit_status'=>1])->orderBy('id DESC')->one();
            if (!empty($res_cancel)) {
                Yii::$app->getSession()->setFlash('warning',"此采购单还有 作废订单待审核 的需求未审核");
                return $this->redirect(Yii::$app->request->referrer);
            }

            //订单子表详情
            $items_details = PurchaseOrderItems::getDetails($purchase_demand_sku_map);
            //订单主表信息
            $order_details = PurchaseOrder::getDetails($pur_numbers[0]);
            return $this->renderAjax('cancel-index', [
                'purchase_demand_sku_map' => $purchase_demand_sku_map,
                'items_details' => $items_details['res'],
                'order_details' => $order_details,
            ]);

            $purchases = PurchaseOrder::find()->where(['in','pur_number',$pur_numbers])->all();
            $purchase = $purchases[0];
            foreach ($purchases as $k=>$v) {
                $purchases[$v->pur_number] = $v;
                unset($purchases[$k]);
            }
            $compact = '';
            if ($purchase->source == 1 && $demands[0]['demand_status'] > 6) {
                //是合同单并且已生成合同
                $compact_number = PurchaseCompactItems::find()->select('compact_number')->where(['in','pur_number',$pur_numbers])->andWhere(['bind'=>1])->column();
                if (count($compact_number) > 1) {
                    Yii::$app->getSession()->setFlash('warning',"不同合同的需求不能一起作废");
                    return $this->redirect(Yii::$app->request->referrer);
                }
                $compact = PurchaseCompact::findOne(['compact_number'=>$compact_number[0]]);
            } else {
                if (count($pur_numbers) > 1) {
                    Yii::$app->getSession()->setFlash('warning',"不同采购单的需求不能一起作废");
                    return $this->redirect(Yii::$app->request->referrer);
                }
            }
            
            $demand_numbers = PurchaseDemand::find()->where(['in','pur_number',$pur_numbers])->select('demand_number')->column();
            $cancel_type = 1; //1:整单作废,2:部分作废
            foreach ($demand_numbers as $demand_number) {
                if (!in_array($demand_number, $ids)) {
                    $cancel_type = 2;
                    break;
                }
            }
            foreach ($demands as &$demand) {
                $_pur_number = $purchase_demand_map[$demand['demand_number']];
                $demand['price'] = PurchaseOrderItems::find()->select('price')->where(['pur_number'=>$_pur_number,'sku'=>$demand['sku']])->scalar();
            }
            
            $pay_amount = $cancel_amount = 0;
            $order_amount = 0;
            return $this->renderAjax('cancel-order',[
                'cancel_type' => $cancel_type,
                'purchase' => $purchase,
                'purchases' => $purchases,
                'demands' => $demands,
                'purchase_demand_map' => $purchase_demand_map,
                'compact' => $compact,
                'pay_amount' => $pay_amount,
                'cancel_amount' => $cancel_amount,
            ]);
        } else {
            //保存作废的信息
            $res = PurchaseOrderCancel::saveCancelInfo(4);
            return $this->redirect(Yii::$app->request->referrer);
        }
    }
    /**
     * 审核作废订单
     */
    public function actionCancelAudit()
    {
        if (Yii::$app->request->isAjax) {
            $ids = Yii::$app->request->post('data');
            $demands = PlatformSummary::find()->where(['in','demand_number',$ids])->asArray()->all();
            //获取需求和采购单号关联数据
            $purchaseDemands = PurchaseDemand::find()->where(['in','demand_number',$ids])->all();
            $pur_numbers = $purchase_demand_sku_map = [];

            foreach ($purchaseDemands as $v) {
                if (!in_array($v->pur_number,$pur_numbers)) {
                    $pur_numbers[] = $v->pur_number;
                }
                //保存PO，需求号，sku的关系
                $purchase_demand_sku_map[] = ['pur_number' =>$v->pur_number, 'demand_number'=>$v->demand_number, 'sku'=>$v->platformSummary->sku];
            }
            $pur_number = array_unique($pur_numbers)[0];

            //订单子表详情
            $items_details = PurchaseOrderItems::getDetails($pur_number);

            if (empty($items_details)) {
                return $this->redirect(Yii::$app->request->referrer);
            }

            //订单主表信息
            $order_details = PurchaseOrder::getDetails($pur_numbers[0],$items_details['cancel_id']);
            return $this->renderAjax('cancel-audit', [
                'cancel_id' => $items_details['cancel_id'],
                'purchase_demand_sku_map' => $purchase_demand_sku_map,
                'items_details' => $items_details['res'],
                'order_details' => $order_details,
            ]);
        } else {
            //审核处理
            self::processAudit();
            return $this->redirect(Yii::$app->request->referrer);
        }
    }
    /**
     * 审核处理
     */
    public static function processAudit()
    {
        $post_info = Yii::$app->request->post();
        $data = Vhelper::changeData($post_info['cancel_ctq']);
        $audit_status = $post_info['audit_status'];
        $surplus_price = 0; //退款金额
        $is_all_surplus = 1; //是否全额退款 1是，-1否

        $transaction = Yii::$app->db->beginTransaction();
        try {
            if ($audit_status ==2) {
                # 审核通过
                foreach ($data as $k => $v) {
                    $items_model = PurchaseOrderItems::find()->where(['pur_number'=>$v['pur_number'],'sku'=>$v['sku']])->one();
                    //如果入库数量大于零，部分到货等待剩余
                    if ((int)$v['instock_qty_count'] >0) {
                        # 部分到货等待剩余
                        if ($v['pay_price']>0) {
                            # 生成退款单 -- 已入库，已付款
                            $tax = PurchaseOrderTaxes::getABDTaxes($v['sku'],$v['pur_number']);
                            if($items_model->purNumber->is_drawback == 2 && $tax>0){//税金税金税金
                                $tax = bcadd(bcdiv($tax,100,3),1,3);
                                $tax_price  = round($tax*$items_model->price*$v['cancel_ctq'],3);
                                $surplus_price += $tax_price;
                            } else {
                                $surplus_price += $v['cancel_ctq']*$v['price']; //此次取消金额
                            }
                        } else {
                            //判断是否全部取消 -- 已入库，未付款
                            $old_cancel_ctq = PurchaseOrderCancelSub::getCancelCtq($v['pur_number'],$v['sku']); //之前已取消的
                            $res_cancel_ctq = $old_cancel_ctq+$v['cancel_ctq']; //之前取消的+现在取消的
                            $res_bccomp = bccomp($v['ctq'], $res_cancel_ctq); //相等就等于0
                            if ($res_bccomp === 0) {
                                # 作废
                                PlatformSummary::updateAll(['demand_status'=>14], ['demand_number'=>$v['demand_number']]);
                            } else {
                                # 返回订单状态
                                PlatformSummary::updateAll(['demand_status'=>$v['old_demand_status']], ['demand_number'=>$v['demand_number']]);
                            }
                            PurchaseOrderCancel::updateAll(['audit_status' => $audit_status, 'is_push'=>0], ['id'=>$post_info['cancel_id']]);
                        }
                    } else {                        
                        # 没有入库数量，是未到货的
                        //判断是否有付款记录，如果有付款的，就生成退款单
                        if ($v['pay_price'] > 0) {

                            # 未入库，已付款，生成退款单 --未入库，已付款
                            $tax = PurchaseOrderTaxes::getABDTaxes($v['sku'],$v['pur_number']);

                            if($items_model->purNumber->is_drawback == 2 && $tax>0){//税金税金税金
                                $tax = bcadd(bcdiv($tax,100,3),1,3);
                                $tax_price  = round($tax*$items_model->price*$v['cancel_ctq'],3);
                                $surplus_price += $tax_price;
                            } else {
                                $surplus_price += $v['cancel_ctq']*$v['price']; //此次取消金额
                            }
                        } else {
                            # 未入库，未付款-》直接作废 --未入库，未付款
                            PlatformSummary::updateAll(['demand_status'=>14], ['demand_number'=>$v['demand_number']]);
                            PurchaseOrderCancel::updateAll(['audit_status' => $audit_status, 'is_push'=>0], ['id'=>$post_info['cancel_id']]);
                        }
                        
                    }
                }
                //修改作废信息状态
                // PurchaseOrderCancel::updateAll(['audit_status' => $audit_status], ['id'=>$post_info['cancel_id']]);
            } elseif ($audit_status ==3) {
                # 审核驳回
                foreach ($data as $k => $v) {
                    # 返回订单状态
                    PlatformSummary::updateAll(['demand_status'=>$v['old_demand_status']], ['demand_number'=>$v['demand_number']]);
                }
                PurchaseOrderCancel::updateAll(['audit_status' => $audit_status], ['id'=>$post_info['cancel_id']]);
                $transaction->commit();

                Yii::$app->getSession()->setFlash('success',"驳回成功");
                return ['error'=>200];
            }

            if($surplus_price>0) {
                //退款
                $freight = !empty($post_info['freight']) ? $post_info['freight'] : 0; //运费
                $discount = !empty($post_info['discount']) ? $post_info['discount'] : 0; //优惠

                $post = [
                    'cancel_id' => $post_info['cancel_id'],
                    'pur_number' => $post_info['pur_number'],
                    'refund_status' => 3, //3部分退款，4全额退款，10作废
                    'money' => $surplus_price+$freight-$discount, //退款金额
                    'freight' => $freight, //运费
                    'discount' => $discount, //优惠
                    'confirm_note' => '海外仓-取消未到货退款', //退款备注
                ];
                //生成退款单
                $return_res = self::refundList($post);
                PlatformSummary::updateAll(['demand_status'=>13], ['demand_number'=>$v['demand_number']]);
            }
            if (!empty($return_res) && $return_res == -1) {
                $transaction->rollBack();
            } else {
                PurchaseOrderCancel::saveCancel($post_info,3,1,1);
                $transaction->commit();
            }
        } catch(\Exception $e) {
            $transaction->rollBack();
            Yii::$app->getSession()->setFlash('warning','对不起，操作失败了');
        }
    }
    /**
     * 生成退款单
     */
    public static function refundList($post)
    {
        $model_receipt = new PurchaseOrderReceipt(); // 采购单收款表
        $order_s = PurchaseOrder::find()->where(['pur_number' => $post['pur_number']])->one();
        if($post['refund_status'] == 3 && !empty($post['money'])) {
            $order_s->refund_status = 3;
            $data = [
                'pur_number'        => $post['pur_number'],
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
                'cancel_id'         => !empty($post['cancel_id'])?$post['cancel_id'] : null,
            ];
            $a = $model_receipt->insertRow($data);
            $b = $order_s->save(false);
            $log = [
                'pur_number' => $post['pur_number'],
                'note' => "部分退款，退款金额 {$data['pay_price']}",
            ];
            PurchaseLog::addLog($log);
            $is_submit = $a && $b;
        }
        if($is_submit) {
            Yii::$app->getSession()->setFlash('success','恭喜你，操作成功了');
            return 1;
        } else {
            Yii::$app->getSession()->setFlash('warning','对不起，操作失败了');
            return -1;
        }
    }
    
    /**
     * 修改采购到货时间
     */
    public function actionUpdatePurchaseArrivalDate()
    {
        $demand_number = Yii::$app->request->post('demand_number');
        $arrival_date = Yii::$app->request->post('arrival_date');
        $model = PlatformSummary::findOne(['demand_number'=>$demand_number]);
        $_purchase_arrival_date = $model->purchase_arrival_date == '0000-00-00 00:00:00' ? '' : $model->purchase_arrival_date;
        if ($model->demand_status < 7) {
            return jsonReturn(0, '需求的当前状态不能修改采购到货日期',['arrival_date'=>$_purchase_arrival_date]);
        }
        if ($arrival_date < $model->agree_time) {
            return jsonReturn(0, '填写的日期不能早于采购单创建日期',['arrival_date'=>$_purchase_arrival_date]);
        }
        if ($model->purchase_arrival_date_total > 2) {
            $grade = PurchaseUser::find()->where(['pur_user_id'=>Yii::$app->user->identity->getId()])->select('grade')->scalar();
            if ($grade != 2 && $grade != 3) {
                return jsonReturn(0, '该需求的采购到货日期已修改过2次，您没有权限修改，如果需要修改，请找主管',['arrival_date'=>$_purchase_arrival_date]);
            }
        }
        if ($model->purchase_arrival_date == $arrival_date) {
            return jsonReturn(0, '提交的日期与原日期一致',['arrival_date'=>$_purchase_arrival_date]);
        }
        $model->purchase_arrival_date = $arrival_date;
        $model->purchase_arrival_date_total++;
        
        $update_data = CommonServices::getUpdateData($model, ['purchase_arrival_date'=>'采购到货日期']);
        PurchaseOrderServices::writelog($demand_number, '修改采购到货日期', '', $update_data);
        
        $model->save(false);
        
        return jsonReturn();
    }
    
    /**
     * 增加物流单号
     */
    public function actionAddExpressNo()
    {
        if (isset($_POST['data'])) {
            $data = Yii::$app->request->post('data');
            if (empty($data)) {
                Yii::$app->getSession()->setFlash('warning','请至少选择一条数据');
                return $this->redirect(Yii::$app->request->referrer);
            }
            $demand_list = PlatformSummary::find()->where(['in','demand_number',$data])->asArray()->all();
            if (empty($demand_list)) {
                Yii::$app->getSession()->setFlash('warning','数据有误');
                return $this->redirect(Yii::$app->request->referrer);
            }
            foreach ($demand_list as $k=>$v) {
                if ($v['demand_status'] < 7) {
                    unset($demand_list[$k]);
                    continue;
                }
            }
            if (empty($demand_list)) {
                Yii::$app->getSession()->setFlash('warning','没有可以添加物流单号的需求');
                return $this->redirect(Yii::$app->request->referrer);
            }
            $model = new PurchaseOrderShip();
            return $this->renderAjax('add-express-no',['model'=>$model,'data'=>$demand_list]);
        }
        if (isset($_POST['PurchaseOrderShip'])) {
            $ships = Yii::$app->request->post('PurchaseOrderShip');
            $demand_numbers = Yii::$app->request->post('demand_numbers');
            
            $transaction = Yii::$app->db->beginTransaction();
            try {
                foreach ($demand_numbers as $demand_number) {
                    
                    $model = new PurchaseOrderShip();
                    $model->demand_number = $demand_number;
                    $model->pur_number = PurchaseDemand::find()->select('pur_number')->where(['demand_number'=>$demand_number])->scalar();
                    $model->demand_number = $demand_number;
                    $model->express_no = $ships['express_no'];
                    $model->cargo_company_id = $ships['cargo_company_id'];
                    $model->create_user_id = Yii::$app->user->identity->id;
                    $model->create_time = date('Y-m-d H:i:s');
                    $model->save(false);
                }
                PurchaseOrderServices::writelog($demand_numbers, "添加物流单号[{$model->id}]\r\n快递公司：{$model->cargo_company_id}\r\n快递号：{$model->express_no}");
                $transaction->commit();
                
            } catch (Exception $e) {
                $transaction->rollBack();
                Yii::$app->getSession()->setFlash('warning','数据异常！保存失败,请联系管理员');
                return $this->redirect(Yii::$app->request->referrer);
            }
            Yii::$app->getSession()->setFlash('success',"操作成功");
            return $this->redirect(Yii::$app->request->referrer);
        }
    }
    
    /**
     * 删除物流单号
     */
    public function actionDeleteExpressNo()
    {
        $id = Yii::$app->request->post('id');
        $model = PurchaseOrderShip::findOne(['id'=>$id]);
        if ($model->demand_number == '') {
            return jsonReturn(0, '此物流单号不能删除');
        }
        PurchaseOrderServices::writelog($model->demand_number, "删除物流单号[{$id}]\r\n快递公司：{$model->cargo_company_id}\r\n快递号：{$model->express_no}");
        $model->delete();
        return jsonReturn();
    }
    
    /**
     * 采购异常回复
     */
    public function actionPurchaseAbnormalAnswer()
    {
        $ids = Yii::$app->request->post('id');
        $note = Yii::$app->request->post('remark');
        foreach ($ids as $demand_number) {
            $model = new PurchaseReply();
            $model->pur_number = PurchaseDemand::find()->where(['demand_number'=>$demand_number])->select('pur_number')->scalar();
            $model->demand_number = $demand_number;
            $model->note = $note;
            $model->create_time = date('Y-m-d H:i:s');
            $model->purchase_type = 2;
            $model->replay_type = 1;
            $model->create_id = Yii::$app->user->identity->getId();
            $model->create_user = Yii::$app->user->identity->username;
            $model->save(false);
            
            PurchaseOrderServices::writelog($demand_number, "添加采购异常回复:\r\n".$note, $model->pur_number);
        }
        Yii::$app->getSession()->setFlash('success',"添加成功");
        return $this->redirect(Yii::$app->request->referrer);
    }
    
    /**
     * 销售反馈
     */
    public function actionSaleFeedback()
    {
        $ids = Yii::$app->request->post('id');
        $note = Yii::$app->request->post('remark');
        foreach ($ids as $demand_number) {
            $model = new PurchaseReply();
            $model->pur_number = PurchaseDemand::find()->where(['demand_number'=>$demand_number])->select('pur_number')->scalar();
            $model->demand_number = $demand_number;
            $model->note = $note;
            $model->create_time = date('Y-m-d H:i:s');
            $model->purchase_type = 2;
            $model->replay_type = 2;
            $model->create_id = Yii::$app->user->identity->getId();
            $model->create_user = Yii::$app->user->identity->username;
            $model->save(false);
            
            PurchaseOrderServices::writelog($demand_number, "添加销售反馈:\r\n".$note, $model->pur_number);
        }
        Yii::$app->getSession()->setFlash('success',"添加成功");
        return $this->redirect(Yii::$app->request->referrer);
    }
    
    /**
     * 开票
     */
    public function actionInvoice()
    {
        if (isset($_POST['data'])) {
            $data = Yii::$app->request->post('data');
            if (empty($data)) {
                Yii::$app->getSession()->setFlash('warning','请至少选择一条数据');
                return $this->redirect(Yii::$app->request->referrer);
            }
            $demand_list = PlatformSummary::find()->where(['in','demand_number',$data])->asArray()->all();
            if (empty($demand_list)) {
                Yii::$app->getSession()->setFlash('warning','数据有误');
                return $this->redirect(Yii::$app->request->referrer);
            }
            foreach ($demand_list as $k=>$v) {
                if ($v['demand_status'] < 7) {
                    unset($demand_list[$k]);
                    continue;
                }
            }
            if (empty($demand_list)) {
                Yii::$app->getSession()->setFlash('warning','没有可以开票的需求');
                return $this->redirect(Yii::$app->request->referrer);
            }
            return $this->renderAjax('invoice',['data'=>$demand_list]);
        }
        if (isset($_POST['qty'])) {
            $qtys = Yii::$app->request->post('qty');
            $invoice_codes = Yii::$app->request->post('invoice_code');
            
            $transaction = Yii::$app->db->beginTransaction();
            try {
                foreach ($qtys as $demand_number=>$qty) {
                    $qty = intval($qty);
                    if ($qty < 1) {
                        return jsonReturn(0, '输入的数量异常');
                    }
                    $invoice_code = trim($invoice_codes[$demand_number]);
                    if (empty($invoice_code)) {
                        return jsonReturn(0, '请输入发票编号');
                    }
                    $has_qty = OverseasPurchaseOrderSearch::getInvoiceQty($demand_number, 'qty');
                    $demand_model = PlatformSummary::findOne(['demand_number'=>$demand_number]);
                    if ($has_qty + $qty > $demand_model->purchase_quantity) {
                        return jsonReturn(0, '输入的数量超过了采购数量');
                    }
                    $model = new DemandInvoice();
                    $model->demand_number = $demand_number;
                    $model->qty = $qty;
                    $model->invoice_code = $invoice_code;
                    $model->create_user = Yii::$app->user->identity->username;
                    $model->create_time = date('Y-m-d H:i:s');
                    $model->save(false);
                    
                    $update_data = CommonServices::getUpdateData($model, ['qty'=>'开票数量','invoice_code'=>'发票编号']);
                    PurchaseOrderServices::writelog($demand_number, "添加开票数量", '', $update_data);
                }
                
                $transaction->commit();
                
            } catch (Exception $e) {
                $transaction->rollBack();
                Yii::$app->getSession()->setFlash('warning','数据异常！保存失败,请联系管理员');
                return $this->redirect(Yii::$app->request->referrer);
            }
            Yii::$app->getSession()->setFlash('success',"操作成功");
            return $this->redirect(Yii::$app->request->referrer);
        }
    }
    
    /**
     * 需求日志
     */
    public function actionDemandLog()
    {
        $demand_number = Yii::$app->request->get('demand_number');
        $list = DemandLog::find()->where(['demand_number'=>$demand_number])->all();
        return $this->renderAjax('demand-log',['list'=>$list]);
    }
    
    public function actionGetAllBuyer()
    {
        $buyers = PlatformSummary::find()->alias("summary")
            ->leftJoin(PurchaseDemand::tableName()." d", "summary.demand_number = d.demand_number")
            ->leftJoin(PurchaseOrder::tableName()." o", "o.pur_number = d.pur_number")
            ->where(['summary.demand_status'=>1])
            ->andWhere("summary.agree_time > '2018-08-29 10:00:00'")
            ->select('o.buyer')
            ->distinct()
            ->column();
        return jsonReturn(1, 'success', $buyers);
    }
    
    private function checkSettlementRatio($ratio)
    {
        if (!$ratio) return false;
        $ratio = explode('+', $ratio);
        if (count($ratio) > 3) return false;
        $t = 0;
        foreach ($ratio as $v) {
            $t += intval($v);
            if ($t > 100 || $t < 1) {
                return false;
            }
        }
        return $t == 100;
    }
}
