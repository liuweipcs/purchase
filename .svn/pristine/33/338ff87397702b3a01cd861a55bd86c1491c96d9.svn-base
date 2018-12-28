<?php

namespace app\controllers;

use app\api\v1\models\ArrivalRecord;
use app\config\Vhelper;
use linslin\yii2\curl;
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
use app\models\OverseasCheckPriv;


class OverseasPurchaseOrder2Controller extends BaseController
{

    public $undonePay = [2, 4, 10]; // 未完成的交易状态
    public $noPayStatus = [0, 3, 11, 12]; // 不做计算的请款状态
    public $disabledOrder = [1, 2, 4, 10]; // 不可再使用的订单状态
    static  public $applyForPayment = [2,4,5,6,10,13]; //已申请付款的订单

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
        $session = Yii::$app->session;
        $args = Yii::$app->request->queryParams;
        if (isset($_GET['requisition_number'])) {
            $this->layout='ajax';
        }
        $searchModel = new OverseasPurchaseOrderSearch();
        $pageSize = isset($_REQUEST['per-page'])?$_REQUEST['per-page']:null;
        if (!empty($pageSize)) {
            $args_tmp = $session->get('order2_params');// $args 改变了查询条件的值导致查询出错
            $args['pageSize'] = $pageSize;
            $args_tmp['pageSize'] = $pageSize;
            $session->set('order2_params', $args_tmp);
            $session->set('pageSize',$pageSize);
        } else {
            if (!empty($session->get('pageSize'))) {
                $pageSize = $session->get('pageSize');
                $args['pageSize'] = $pageSize;
            } else {
                $session->set('order2_params', $args);
            }
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
                if(empty($product_quote))
                    return jsonReturn(0,$model->sku.','.$demand_number.',查询不到价格');
                $price = $product_quote->supplierprice;
                $pur_ticketed_point = 0;
                if ($is_drawback_array[$demand_number] == 2) {
                    if ($product_quote['is_back_tax'] != 1) {
                        //return jsonReturn(0,$model->sku.','.$demand_number.',此sku不可做退税');
                    }
                    $price += $price*$product_quote->pur_ticketed_point/100;
                    $pur_ticketed_point = $product_quote->pur_ticketed_point;
                    if (is_null($pur_ticketed_point)) {
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

                    //检查是否有同个采购单下的不同供应商
                    $_pur_demands = PurchaseDemand::find()->where(['pur_number'=>$pur_number])->select('demand_number')->column();
                    $_pur_demands = PlatformSummary::find()->where(['in','demand_number',$_pur_demands])->select('supplier_code')->column();
                    $_pur_demands = array_unique($_pur_demands);
                    if (count($_pur_demands) > 1) {
                        return jsonReturn(0,'PO:'.$pur_number.'有不同的供应商,请拆单');
                    }

                    $order_model = PurchaseOrder::findOne(['pur_number'=>$pur_number]);
                    if ($order_model->is_drawback != $is_drawback_array[$demand_number] && empty($is_drawback_note_array[$demand_number])) {
                        return jsonReturn(0,$model->sku.','.$demand_number.',修改了是否退税未填写备注');
                    }
                    $order_model->supplier_code = $model->supplier_code;
                    if (empty($order_model->supplier->supplier_settlement)) {
                        return jsonReturn(0,$model->sku.','.$demand_number.',该供应商结算方式为空，请先维护供应商结算方式');
                    }
                    if (empty($order_model->supplier->payment_method)) {
                        return jsonReturn(0,$model->sku.','.$demand_number.',该供应商支付方式为空，请先维护供应商支付方式');
                    }
                    $order_model->supplier_name = $order_model->supplier->supplier_name;
                    $order_model->account_type = $order_model->supplier->supplier_settlement;
                    $order_model->pay_type = $order_model->supplier->payment_method;
                    $order_model->is_drawback = $is_drawback_array[$demand_number];
                    $order_model->date_eta = $date_eta_array[$demand_number];
                    $order_model->source = $source_array[$demand_number];
                    $order_model->transit_warehouse = $transit_warehouse_array[$demand_number];
                    $update_order_data[$pur_number] = CommonServices::getUpdateData($order_model, $update_data_param);
                    $order_model->save(false);

                    PlatformSummary::updateAll(['transit_warehouse'=>$transit_warehouse_array[$demand_number],'is_push'=>0],['demand_number'=>$demand_number]);
                    $pay_type_model = PurchaseOrderPayType::findOne(['pur_number'=>$pur_number]);
                    if (empty($pay_type_model)) {
                        $pay_type_model = new PurchaseOrderPayType();
                        $pay_type_model->pur_number = $pur_number;
                    }
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
                    $demand_model->supplier_code = ProductProvider::find()->where(['sku'=>$demand_model->sku,'is_supplier'=>1])->select('supplier_code')->scalar();

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
                        $model->demand_status = 6;
                        PurchaseOrderServices::writelog($model->demand_number, '采购审核【通过】[审核备注:'.$note.']', $pur_number);
                    } else {//驳回
                        $model->demand_status = 1;
                        $model->supplier_code = ProductProvider::find()->where(['sku'=>$model->sku,'is_supplier'=>1])->select('supplier_code')->scalar();
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
                        $model->supplier_code = ProductProvider::find()->where(['sku'=>$model->sku,'is_supplier'=>1])->select('supplier_code')->scalar();
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
                $curr_supplier_code = ProductProvider::find()->where(['sku'=>$model->sku,'is_supplier'=>1])->select('supplier_code')->scalar();
                if ($model->supplier_code != $curr_supplier_code) {
                    $model->supplier_code = $curr_supplier_code;
                    $model->save(false);
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
                $model_order->supplier_code = $model->supplier_code;
                $model_order->e_supplier_name = $model_order->supplier_name = $model->supplier2->supplier_name;
                $model_order->created_at      = date('Y-m-d H:i:s');
                $model_order->creator         = Yii::$app->user->identity->username;
                $model_order->merchandiser  = $purchase_model->merchandiser;
                $model_order->buyer           = $purchase_model->buyer;
                $model_order->purchas_status  = 1;//待确认
                $model_order->create_type     = 1;//创建类型
                $model_order->is_transit      = $purchase_model->is_transit;
                $model_order->purchase_type   = 2;//海外
                //$model_order->e_account_type  = $model_order->account_type = $purchase_model->account_type;
                $model_order->transit_warehouse   = $purchase_model->transit_warehouse;//中转
                //$model_order->pay_type = $purchase_model->pay_type;
                $model_order->is_drawback = $purchase_model->is_drawback;
                $model_order->shipping_method = $purchase_model->shipping_method;
                $model_order->source = $purchase_model->source;
                $model_order->pur_type = 0;
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
                    $item_model->qty = 0;
                    $item_model->items_totalprice = 0;
                    $item_model->save(false);
                }

                PurchaseDemand::updateAll(['pur_number'=>$model_order->pur_number], ['in','demand_number',$new_demand_number]);

                PlatformSummary::updateAll(['is_push'=>0],['in','demand_number',$new_demand_number]);
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
     * @pur_number 采购单号  po
     * @demand_number 需求单号
     */
    public function actionConfirmOrder()
    {
        $ids = Yii::$app->request->post('id');
        $demands = PurchaseDemand::find()->where(['in','demand_number',$ids])->asArray()->all();
        $pur_numbers = array_unique(array_column($demands, 'pur_number'));
        //查询出采购单号下的所有需求号
        $demand_matchs = PurchaseDemand::find()->where(['in','pur_number',$pur_numbers])->all();
        $demand_numbers_t =  [];
        foreach ($demand_matchs as $vs) {
            $demand_numbers_t[] = $vs->demand_number;
        }
        //排除作废的需求单号
        $normal_demand_list = PlatformSummary::find()->where(['and',['in','demand_number',$demand_numbers_t],['<','demand_status','12']])->all();
        //$ids 选择的需求单号.
        foreach ($normal_demand_list as $normal_demand) {
            if (!in_array($normal_demand->demand_number, $ids)) {
                Yii::$app->getSession()->setFlash('warning', "请勾选PO下所有的需求".$normal_demand->demand_number);
                return $this->redirect(Yii::$app->request->referrer);
            }
        }

        //查询出所有的需求号
        $demand_list = PlatformSummary::find()->where(['in','demand_number',$ids])->all();
        $demand_numbers = $demand_match_purs = [];
        foreach ($normal_demand_list as $v) {
            $demand_numbers[] = $v->demand_number;
            $demand_match_purs[$v->demand_number] = $v->demand->pur_number;
        }

        $un_demands = $sku_numbers = [];
        foreach ($demand_list as $model) {
            if ($model->demand_status > 6) {
                Yii::$app->getSession()->setFlash('warning',"订单状态异常,需求号:".$model->demand_number." ,订单状态:".PurchaseOrderServices::getOverseasOrderStatus($model->demand_status));
                return $this->redirect(Yii::$app->request->referrer);
            }
            if ($model->demand_status < 6) {
                $un_demands[] = "PO:".$demand_match_purs[$model->demand_number].", 需求号:".$model->demand_number.", 订单状态:".PurchaseOrderServices::getOverseasOrderStatus($model->demand_status);
            }
            if( $model->demand_status == 6 ) {
                $sku = strtoupper($model->sku);
                if (isset($sku_numbers[$demand_match_purs[$model->demand_number]][$sku])) {
                    $sku_numbers[$demand_match_purs[$model->demand_number]][$sku] += $model->purchase_quantity;
                } else {
                    $sku_numbers[$demand_match_purs[$model->demand_number]][$sku] = $model->purchase_quantity;
                }
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
            if (count(array_unique(array_column($purchase_orders,'is_transit'))) > 1) {
                Yii::$app->getSession()->setFlash('warning',"【中转方式】不一致，不能生成同个合同");
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

            //采购单为退税单时，保存开票点
            $taxes_data = [];
            foreach ($demand_matchs as $dmv) {
                if ($dmv->platformSummary->demand_status==1) {
                    $taxes = OverseasPurchaseOrderSearch::getSkuQuoteValue($dmv->platformSummary->sku, 'pur_ticketed_point');
                    $taxes_data['taxes'][] = ['pur_number'=>$dmv->pur_number,'sku'=>$dmv->platformSummary->sku,'taxes'=>$taxes];
                } else {
                    $pur_ticketed_point = PurchaseOrderItems::find()->select('pur_ticketed_point')->where(['pur_number'=>$dmv->pur_number,'sku'=>$dmv->platformSummary->sku])->scalar();
                    $taxes = !empty($pur_ticketed_point) ? $pur_ticketed_point : 0;
                    $taxes_data['taxes'][] = ['pur_number'=>$dmv->pur_number,'sku'=>$dmv->platformSummary->sku,'taxes'=>$taxes];
                }
            }
            if (!empty($taxes_data)) $taxes_res = PurchaseOrderTaxes::saveTax($taxes_data);

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

        if (PlatformSummary::find()->where(['in','demand_number',$demand_numbers])->andWhere("demand_status != 6")->andWhere("demand_status < 12")->one()) {
            throw new \yii\web\NotFoundHttpException('订单状态异常，包含有【非 等待生成进货单】状态的需求');
        }
        //查询出正常等待生成进货单的数据

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
        $PlatformSummary = PlatformSummary::find()->where(['in','demand_number',$demand_numbers])->andWhere("demand_status != 6")->andWhere("demand_status < 12")->all();
        if (!empty($PlatformSummary)) {
            throw new \yii\web\NotFoundHttpException('订单状态异常，包含有【非 等待生成进货单】状态的需求');
        }
        if(Yii::$app->request->isPost) {
            $transaction = Yii::$app->db->beginTransaction();
            try {
                PlatformSummary::updateAll(['demand_status'=>7],['and',['in','demand_number',$demand_numbers],['=','demand_status','6']]);
                $username = Yii::$app->user->identity->username;
                PurchaseOrder::updateAll(['purchas_status'=>3,'audit_time'=>date('Y-m-d H:i:s'),'auditor'=>$username],['in','pur_number',$ids]);

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
                $model->is_drawback = PurchaseOrder::find()->select('is_drawback')->where(['in', 'pur_number',$ids])->scalar();
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

                //采购单为退税单时，保存开票点
                $taxes_data = [];
                $demands_model = PurchaseDemand::find()->where(['in','demand_number',$demand_numbers])->all();
                foreach ($demands_model as $dmv) {
                    if ($dmv->platformSummary->demand_status==1) {
                        $taxes = OverseasPurchaseOrderSearch::getSkuQuoteValue($dmv->platformSummary->sku, 'pur_ticketed_point');
                        $taxes_data['taxes'][] = ['pur_number'=>$dmv->pur_number,'sku'=>$dmv->platformSummary->sku,'taxes'=>$taxes];
                    } else {
                        $pur_ticketed_point = PurchaseOrderItems::find()->select('pur_ticketed_point')->where(['pur_number'=>$dmv->pur_number,'sku'=>$dmv->platformSummary->sku])->scalar();
                        $taxes = !empty($pur_ticketed_point) ? $pur_ticketed_point : 0;
                        $taxes_data['taxes'][] = ['pur_number'=>$dmv->pur_number,'sku'=>$dmv->platformSummary->sku,'taxes'=>$taxes];
                    }
                }
                if (!empty($taxes_data)) $taxes_res = PurchaseOrderTaxes::saveTax($taxes_data);

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
    /**
     * 申请请款
     * 1、根据采购建议单号（采购sku），查询出该采购建议单所属的采购单号（采购单与需求单号关系表）。
     * 2、查询采购单详细信息，查询采购建议详细信息。
     * 3、查询请款单下对应的需求单。
     *
     */
    public function actionPayApply()
    {
        $request = Yii::$app->request;
        $demand_numbers = $request->post('id');
        if (empty($demand_numbers)) {
            return $this->redirect(['index']);
        }
        $demands_ns = PurchaseDemand::find()->where(['in','demand_number',$demand_numbers])->asArray()->all();
        $pur_numbers_o = array_unique(array_column($demands_ns, 'pur_number'));
        //查询出采购单下所有的采购建议
        $purchase_demands = PurchaseDemand::find()->where(['in','pur_number',$pur_numbers_o])->all();

        //$purchase_demands = PurchaseDemand::find()->where(['in','demand_number',$demand_numbers])->all();
        $demand_maps = [];
        $arrSupplierCode = []; //供应商
        foreach ($purchase_demands as $v) {
            $demand_maps[$v->demand_number] = $v->pur_number;
            $arrSupplierCode[] = $v->platformSummary->supplier_code;
        }
        $arrSupplierCodes = array_unique($arrSupplierCode);
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
        if ($source == 2 && count($arrSupplierCodes) > 1) {
            Yii::$app->getSession()->setFlash('warning','网采单必须是【相同的供应商】才能一起请款');
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
            if (!isset($compact_numbers[0])) {
                Yii::$app->getSession()->setFlash('warning','合同号不能为空');
                return $this->redirect(Yii::$app->request->referrer);
            }
            $compact_number = $compact_numbers[0];
            $compact_model = PurchaseCompact::findOne(['compact_number'=>$compact_number]);
        }
        $purchase_quantitys = []; //保存对应采购单的sku数量
        $demands = PlatformSummary::find()->where(['and',['in','demand_number',$purchase_demands],['in','demand_status',[7,8,9,10,11]]])->all();
        foreach ($demands as $model) {
            if (!in_array($model->demand_status, [7,8,9,10,11])) {
                Yii::$app->getSession()->setFlash('warning','需求单【'.$model->demand_number.'】的状态不能申请付款');
                return $this->redirect(Yii::$app->request->referrer);
            }
            if (!empty($purchase_quantitys[$demand_maps[$model->demand_number]])) {
                $purchase_quantitys[$demand_maps[$model->demand_number]] += $model->purchase_quantity;
            } else {
                $purchase_quantitys[$demand_maps[$model->demand_number]] = $model->purchase_quantity;
            }

            $model->price = PurchaseOrderItems::find()->where(['sku'=>$model->sku,'pur_number'=>$demand_maps[$model->demand_number]])->andWhere(['>', 'ctq','0'])->select('price')->scalar();
            $model->cancel_cty = PurchaseOrderServices::getOverseasDemandCancelCty($model->demand_number);
        }
        //作废了应该解绑map表的交易号
        $pay_maps_data = OrderPayDemandMap::find()->where(['in','demand_number',$demand_numbers])->all();
        $pay_maps = $has_amount = $cancel_amount = $check_requisition_numbers = $demand_number_list = [];
        $pay_amount_total = 0;

        if ($pay_maps_data) {
            foreach ($pay_maps_data as $v) {
                if (!in_array($v->requisition_number, $check_requisition_numbers)) {
                    $pay_type_model = PurchaseOrderPay::findOne(['requisition_number'=>$v->requisition_number]);
                    if(isset($pay_type_model->pay_status)) {
                        if (in_array($pay_type_model->pay_status, $this->undonePay)) {
                            Yii::$app->getSession()->setFlash('warning', "存在没有支付的申请,需求号:{$v->demand_number},申请单号:{$v->requisition_number}");
                            return $this->redirect(Yii::$app->request->referrer);
                        }
                    }
					if(!empty($pay_type_model)){
						$check_requisition_numbers[$v->requisition_number] = $pay_type_model;

					}
                }

                if (isset($check_requisition_numbers[$v->requisition_number]->pay_status) && in_array($check_requisition_numbers[$v->requisition_number]->pay_status,[5,6])) {
                    //已付款的
                } else {
                    //未付款的
                    continue;
                }
                $pay_maps[$v->demand_number] = $v;
                if (isset($has_amount[$v->demand_number]) && !in_array($v->demand_number,$demand_number_list)) {
                    $has_amount[$v->demand_number] += $v->pay_amount;
                } else {
                    $has_amount[$v->demand_number] = $v->pay_amount;
                    $demand_number_list[] = $v->demand_number;
                }
                $pay_amount_total += $v->pay_amount;
            }
        }

        $pay_amount_total = PurchaseCompact::getCompactPayTotalPrice($compact_number);
        $cancel_total_price = PurchaseOrderCancel::getNewCompactCancelPrice($compact_number);//取消总额(扣除尾款的金额)
        // $cancel_total_price = PurchaseCompact::getCompactCancelPrice($compact_number); //取消总额
        // $receipt_total_price = PurchaseCompact::getCompactReceiptPrice($compact_number); //退款总额

        if (isset($_POST['pay_amount'])) {


            $freights = $request->post("freight");
            $discounts = $request->post("discount");
            $price_types = $request->post("price_type");
            $pay_ratios = $request->post("pay_ratio");
            $payPrice = $request->post("pay_price");
            $pay_amounts = $request->post("pay_amount");
            $order_freight = round($request->post("order_freight"),2);
            $order_discount = round($request->post("order_discount"),2);

            $transaction = Yii::$app->db->beginTransaction();
            try {
                $pur_numbers = [];
                $add_freight = $order_freight*100;
                $add_discount = $order_discount*100;
                if($source == 2) {
                    foreach ($purchases as $k => $v) {

                        if ($source == 2) {
                            $net_freight = round($purchase_quantitys[$v->pur_number] * $order_freight*100 / array_sum($purchase_quantitys)); //网采单运费计算
                            $net_discount = round($purchase_quantitys[$v->pur_number]*$order_discount*100 /array_sum($purchase_quantitys)); //网采单优惠额计算

                            if (count($purchases)-1 == $k) {
                                $net_freight =$add_freight;
                                $net_discount =$add_discount;
                            }
                            $add_freight -= $net_freight; //最后超出的
                            $add_discount -= $net_discount; //最后超出的
                        }

                        $paytype_model = $v->purchaseOrderPayType;

                        //请款单
                        $pay_model = new PurchaseOrderPay();
                        $pay_model->requisition_number = CommonServices::getNumber('PP');
                        $pay_model->pay_status = 10;
                        $pay_model->pur_number = $source == 1 ? $compact_number : $v->pur_number;
                        $pay_model->supplier_code = $v->supplier_code;
                        $pay_model->settlement_method = $v->account_type;
                        $pay_model->create_notice = $request->post('create_notice');
                        $pay_model->applicant = Yii::$app->user->id;
                        $pay_model->application_time = date('Y-m-d H:i:s');
                        $pay_model->pay_type = $v->pay_type;
                        $pay_model->currency = $v->currency_code;
                        $pay_model->source = $source;
                        $pay_model->js_ratio = $paytype_model->settlement_ratio;
                        $pay_model->purchase_account = $request->post('purchase_account');
                        $pay_model->pai_number = $request->post('pai_number');

                        if ($order_freight > 0) {
                            if ($source == 2) {
                                $paytype_model->freight = $net_freight/100;
                            } else {
                                $paytype_model->freight = $order_freight;
                            }
                        }
                        if ($order_discount > 0) {
                            if ($source == 2) {
                                $paytype_model->discount = $net_discount/100;
                            } else {
                                $paytype_model->discount = $order_discount;
                            }
                        }
                        if ($pay_model->purchase_account) {
                            $paytype_model->purchase_acccount = $pay_model->purchase_account;
                        }
                        if ($pay_model->pai_number) {
                            $paytype_model->platform_order_number = $pay_model->pai_number;
                        }
                        $update_data = CommonServices::getUpdateData($paytype_model, [
                            'purchase_acccount' => '账号',
                            'platform_order_number' => '拍单号',
                        ]);
                        $paytype_model->save(false);

                        if ($source == 1 && ($order_freight > 0 || $order_discount > 0)) {
                            if ($order_freight > 0) {
                                $compact_model->freight = $paytype_model->freight = $order_freight;
                            }
                            if ($order_discount > 0) {
                                $compact_model->discount = $order_discount;
                            }
                            $compact_model->real_money = $compact_model->product_money + $compact_model->freight - $compact_model->discount;
                            $compact_model->save(false);
                        }

                        $order_demands = []; //该订单存在的需求号
                        foreach ($v->demand as $order_demand) {
                            $order_demands[] = $order_demand->demand_number;
                        }
                        $pay_price = 0;
                        foreach ($demands as $demand) {
                            if (!in_array($demand->demand_number, $order_demands)) continue;
                            $demand_number = $demand->demand_number;
                            $model = new OrderPayDemandMap();
                            $model->demand_number = $demand_number;
                            $model->requisition_number = $pay_model->requisition_number;
                            $model->freight = round($freights[$demand_number], 2);
                            $model->discount = round($discounts[$demand_number], 2);
                            $model->price_type = intval($price_types[$demand_number]);
                            $model->pay_ratio = isset($pay_ratios[$demand_number]) ? intval($pay_ratios[$demand_number]) : 0;
                            $model->pay_amount = round($pay_amounts[$demand_number], 2); //此次请款金额

                            $item_totalprice = $demand->price * $demand->purchase_quantity; //需求的采购金额
                            $item_has_price = isset($has_amount[$demand->demand_number]) ? $has_amount[$demand->demand_number] : 0; //已请款金额
                            $bcc = bccomp($item_totalprice, ($item_has_price + $model->pay_amount));
                            if ($bcc == -1) {
                                Yii::$app->getSession()->setFlash('warning', '支付金额超过了总金额,需求单号:' . $demand_number . ',sku:' . $demand->sku . "产品总额：{$item_totalprice},已申请金额：{$item_has_price},请款金额：{$model->pay_amount}");
                                return $this->redirect(Yii::$app->request->referrer);
                            }

                            $model->save(false);

                            if ($demand->pay_status != 6) {
                                $demand->pay_status = 10;
                                $demand->save(false);
                            }

                            $log_message = "申请付款,请款单:{$model->requisition_number}\r\n";
                            $log_message .= $model->price_type == 1 ? '【比例请款】' : '【手动请款】';
                            $log_message .= '【运费：' . $model->freight . '】';
                            $log_message .= '【优惠：' . $model->discount . '】';
                            if ($model->price_type == 1) {
                                $log_message .= '【结算比例：' . $model->pay_ratio . '%】';
                            }
                            $log_message .= '【请款金额：' . $model->pay_amount . '】';
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
                            $payform->fk_name = BaseServices::getBuyerCompany($purchases[0]->is_drawback, 'name');
                            $payform->create_time = date('Y-m-d H:i:s');
                            $payform->supplier_name = $purchases[0]->supplier_name;
                            $payform->account = $request->post('account');
                            $payform->payment_platform_branch = $request->post('payment_platform_branch');
                            $payform->payment_reason = $request->post('payment_reason');
                            $payform->tpl_id = 2;
                            $payform->save(false);
                        }
                    }
                }elseif ($source == 1) {//合同
                    $account_type = [];
                    $tot_pay_price = 0;
                    $update_demand_number = [];
                    $log_array = [];
                    $purchases_freight = 0;
                    $purchases_discount = 0;
                    foreach ($purchases as $k => $v) {
                        $pur_numbers[] = $v->pur_number;
                        $account_type[] = $v->account_type;
                        $pay_type       = $v->pay_type;
                        $currency_code  = $v->currency_code;

                        $paytype_model = $v->purchaseOrderPayType;
                        if ($request->post('purchase_account')) {
                            $paytype_model->purchase_acccount = $request->post('purchase_account');
                        }
                        if ($request->post('pai_number')) {
                            $paytype_model->platform_order_number = $request->post('pai_number');
                        }


                        $compact_model->freight = $order_freight;
                        $compact_model->discount = $order_discount;

                        $compact_model->real_money = $compact_model->product_money + $compact_model->freight - $compact_model->discount;
                        $compact_model->save(false);


                        $order_demands = []; //该订单存在的需求号
                        foreach ($v->demand as $order_demand) {
                            $order_demands[] = $order_demand->demand_number;
                        }
                        $pay_price = 0;
                        $pur_order_freight = 0;
                        $pur_order_discount = 0;
                        foreach ($demands as $demand) {
                            if (!in_array($demand->demand_number, $order_demands)) continue;
                            $demand_number = $demand->demand_number;

                            $model = new OrderPayDemandMap();
                            $model->demand_number = $demand_number;
                            $model->freight = round($freights[$demand_number], 2);
                            $model->discount = round($discounts[$demand_number], 2);
                            $model->price_type = intval($price_types[$demand_number]);
                            $model->pay_ratio = isset($pay_ratios[$demand_number]) ? intval($pay_ratios[$demand_number]) : 0;
                            $model->pay_amount = round($pay_amounts[$demand_number], 3); //此次请款金额

                            $item_totalprice = round(($demand->price * $demand->purchase_quantity),2); //需求的采购金额
                            $item_has_price = isset($has_amount[$demand->demand_number]) ? $has_amount[$demand->demand_number] : 0; //已请款金额
                            $bcc = bccomp($item_totalprice, ($item_has_price + $model->pay_amount));
                            if ($bcc == -1) {
                                Yii::$app->getSession()->setFlash('warning', '支付金额超过了总金额,需求单号:' . $demand_number . ',sku:' . $demand->sku . "产品总额：{$item_totalprice},已申请金额：{$item_has_price},请款金额：{$model->pay_amount}");
                                return $this->redirect(Yii::$app->request->referrer);
                            }

                            $model->save(false);

                            $update_demand_number[] = $model->id;
                            if ($demand->pay_status != 6) {
                                $demand->pay_status = 10;
                                $demand->save(false);
                            }
                            $update_data = CommonServices::getUpdateData($paytype_model, [
                                'purchase_acccount' => '账号',
                                'platform_order_number' => '拍单号',
                            ]);
                            //$log_message = "申请付款,请款单id:{$model->id}\r\n";
                            $log_message = $model->price_type == 1 ? '【比例请款】' : '【手动请款】';
                            $log_message .= '【运费：' . $model->freight . '】';
                            $log_message .= '【优惠：' . $model->discount . '】';
                            if ($model->price_type == 1) {
                                $log_message .= '【结算比例：' . $model->pay_ratio . '%】';
                            }
                            $log_message .= '【请款金额：' . $model->pay_amount . '】';
                            $log_array[] = array($demand_number, $log_message, '', $update_data);

                            $pay_price += $model->pay_amount+ $model->freight - $model->discount;
                            $pur_order_discount += $model->discount;
                            $pur_order_freight +=$model->freight;
                        }

                        $paytype_model->freight = $pur_order_freight;

                        $paytype_model->discount = $pur_order_discount;


                        $paytype_model->save(false);

                        $tot_pay_price += $pay_price;
                    }

                    /**
                     * 请款名称：付款单种类明细
                     */
                    $priceType = $price_types?reset($price_types):1; //请款方式:比例(1)，手动(2)
                    $payRatio = $pay_ratios?reset($pay_ratios):null; //请款比例
                    $payCategory = ($priceType==1)?12:21; //比例请款(12),手动请款(21)
                    $payInfo =  PurchaseOrderPay::getPayCategory($compact_number,$payCategory,$payPrice,$order_freight,$payRatio);

                    /**
                     * 请款名称：付款单种类明细只能为一种：
                     * 运费只请一次
                     * 未付款的不可重新请款
                     */
                    //此合同，已请款的单中，存在同一请款名称的，且针对的是：请款比例和运费这类的
                    $isRepeatPay = PurchaseOrderPay::find()
                        ->where(['pur_number'=> $compact_number,'pay_status'=> self::$applyForPayment])
                        ->andWhere(['and', "pay_category='" . $payInfo['pay_category'] . "'"])
                        ->andWhere(['in', 'pay_category', [11, 12, 13, 20, 22]])
                        ->asArray()->one();
                    if ($isRepeatPay) {
                        $transaction->rollBack();
                        Yii::$app->getSession()->setFlash('warning', "存在不同的付款种类，同一请款单只能存在一种付款种类--" . PurchaseOrderServices::getPayCategory($payInfo['pay_category']));
                        return $this->redirect(Yii::$app->request->referrer);
                    }

                    //合同取消的总金额：付尾款时候判断
                    $compactItemsInfo = PurchaseCompactItems::getCancelJudgeCompact($pur_numbers[0]);
                    if (!empty($compactItemsInfo['orderPayInfo'][0]['id']) && $payInfo['pay_category']!=22) {
                        $cancel_price = PurchaseOrderCancel::getNewCompactCancelPrice($compact_number);
                    } else {
                        $cancel_price = 0;
                    }

                    $tot_pay_price = $tot_pay_price-$cancel_price; //应请款金额
                    if ($tot_pay_price<=0) exit("申请尾款请款金额不能大于应请款金额：应请款金额=原支付尾款金额-取消金额");

                    if(count(array_unique($account_type)) > 1) {
                        $transaction->rollBack();
                        Yii::$app->getSession()->setFlash('warning', "存在不同的请款方式，同一请款单只能存在一种请款方式");
                        return $this->redirect(Yii::$app->request->referrer);
                    }

                    //请款单
                    $pay_model = new PurchaseOrderPay();
                    $pay_model->requisition_number = CommonServices::getNumber('PP');
                    $pay_model->pay_status = 10;
                    $pay_model->pur_number = $compact_number;
                    $pay_model->pay_ratio          = $payInfo['ratio'];          // 请款比例
                    $pay_model->pay_category       = $payInfo['pay_category'];   // 付款种类
                    $pay_model->pay_name           = PurchaseOrderServices::getPayCategory($payInfo['pay_category']);//付款名称
                    $pay_model->supplier_code = $arrSupplierCodes[0];
                    $pay_model->settlement_method = $account_type[0];
                    $pay_model->create_notice = $request->post('create_notice');
                    $pay_model->applicant = Yii::$app->user->id;
                    $pay_model->application_time = date('Y-m-d H:i:s');
                    $pay_model->pay_type = $pay_type;
                    $pay_model->currency = $currency_code;
                    $pay_model->source = $source;
                    $pay_model->js_ratio = $compact_model->settlement_ratio;
                    $pay_model->purchase_account = $request->post('purchase_account');
                    $pay_model->pai_number = $request->post('pai_number');
                    $pay_model->pay_price = round($tot_pay_price,3);
                    $pay_model->save(false);

                    //插入合同支付信息
                    $selfPayName = \app\models\DataControlConfig::find()->select('values')->where(['type'=>'self_pay_name'])->scalar();
                    $self_pay_name_array = $selfPayName ? explode(',',$selfPayName) : ['合同运费','合同运费走私账'];
                    $drawback_name = in_array($pay_model->pay_name,$self_pay_name_array) ? 1 : $purchases[0]->is_drawback;//通过是否退税判断银行卡类型
                    $payform = new PurchasePayForm();
                    $payform->compact_number = $compact_number;
                    $payform->pay_id = $pay_model->id;
                    $payform->pay_price = $pay_model->pay_price;
                    $payform->fk_name = BaseServices::getBuyerCompany($drawback_name, 'name');
                    $payform->create_time = date('Y-m-d H:i:s');
                    $payform->supplier_name = $request->post('payee_account_name');
                    $payform->account = $request->post('account');
                    $payform->payment_platform_branch = $request->post('payment_platform_branch');
                    $payform->payment_reason = $request->post('payment_reason');
                    $payform->tpl_id = 2;
                    $payform->save(false);

                    $numb = OrderPayDemandMap::updateAll(['requisition_number'=>$pay_model->requisition_number],['in','id',$update_demand_number]);
                    if($numb != count($update_demand_number)){
                        $transaction->rollBack();
                        Yii::$app->getSession()->setFlash('warning', "数据保存失败！");
                        return $this->redirect(Yii::$app->request->referrer);
                    }
                    foreach ($log_array as $log){
                        $log_message_str = "申请付款,请款单:{$pay_model->requisition_number}\r\n".$log[1];
                        PurchaseOrderServices::writelog($log[0], $log_message_str, '', $log[3]);
                    }
                }
                $transaction->commit();

            } catch (Exception $e) {
                $transaction->rollBack();
                Yii::$app->getSession()->setFlash('warning', '数据异常！保存失败,请重试'.$e->getMessage());
                return $this->redirect(Yii::$app->request->referrer);
            }

            Yii::$app->getSession()->setFlash('success', '操作成功');
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
            'cancel_total_price' => $cancel_total_price,
            'pay_model' => $purchases[0]->purchaseOrderPayType,
        ]);
    }


    public function actionGetRmb()
    {
        $price = Yii::$app->request->post('price');
        $price = round($price,3);
        return Vhelper::num_to_rmb($price,3);
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

        /**************** 统计总金额 ******************************/
        // 合同单总金额 pay_price
        $dataProvider1 = $searchModel->search($args);
        $dataProvider1->query->select = [" SUM(pay.pay_price) AS pay_price "];
        $dataProvider1->query->andWhere("pay.source=1");
        $dataProvider1->query->groupBy = '';// 去除GROUP
        $totalPayPrice1 = $dataProvider1->query->scalar();

        // 网采单 pur_order_pay_demand_map(运费、优惠金额)
        $dataProvider2 = $searchModel->search($args);
        $dataProvider2->query->select = [" SUM(pay.pay_price+IFNULL(orderpaydemand.freight,0)-IFNULL(orderpaydemand.discount,0)) AS pay_price "];
        $dataProvider2->query->andWhere("pay.source=2");
        $dataProvider2->query->InnerJoin(OrderPayDemandMap::tableName()." orderpaydemand","orderpaydemand.requisition_number = pay.requisition_number");
        $dataProvider2->query->andWhere("(orderpaydemand.id=(SELECT MIN(id) FROM pur_order_pay_demand_map AS tmp2 WHERE tmp2.requisition_number=orderpaydemand.requisition_number))");
        $dataProvider2->query->groupBy = '';// 去除GROUP
        $totalPayPrice2 = $dataProvider2->query->scalar();

        // 网菜单 pur_purchase_order_pay_type(运费、优惠金额)
        $dataProvider3 = $searchModel->search($args);
        $dataProvider3->query->select = [" SUM(pay.pay_price+IFNULL(orderpaytype.freight,0)-IFNULL(orderpaytype.discount,0)) AS pay_price "];
        $dataProvider3->query->LeftJoin(PurchaseOrderPayType::tableName()." orderpaytype","orderpaytype.pur_number = pay.pur_number");
        $dataProvider3->query->andWhere("NOT EXISTS(SELECT tmp.pur_number FROM pur_purchase_order_pay_type AS tmp WHERE tmp.pur_number=pay.pur_number)");
        $dataProvider3->query->andWhere("pay.source=2");
        $dataProvider3->query->groupBy = '';// 去除GROUP
        $totalPayPrice3 = $dataProvider3->query->scalar();

        $totalPayPrice = floatval($totalPayPrice1) + floatval($totalPayPrice2) + floatval($totalPayPrice3);
        /**************** 统计总金额 ******************************/

        $dataProvider = $searchModel->search($args);

        $userid = Yii::$app->user->identity->getId();
        $fields = TableFields::find()->where(['userid'=>$userid,'table_name'=>'overseas_payment'])->select('data')->scalar();
        $fields = $fields ? json_decode($fields, true) : [];

        \Yii::$app->session->set('OverseasPurchasePaymentExport', $args);// 把查询条件的参数缓存起来

        return $this->render('payment', [
            'model' => $searchModel,
            'dataProvider' => $dataProvider,
            'fields' => $fields,
            'totalPayPrice' => $totalPayPrice
        ]);
    }

    /**
     * 请款单信息导出
     */
    public function actionPaymentExport(){
        set_time_limit(0); //用来限制页面执行时间的,以秒为单位
        ini_set('memory_limit', '512M');

        $ids = Yii::$app->request->get('ids');

        // 获取查询条件缓存的参数
        $paramsData = \Yii::$app->session->get('OverseasPurchasePaymentExport');

        $searchModel    = new OverseasPaymentSearch();
        $dataProvider   = $searchModel->search($paramsData);
        $query          =     $dataProvider->query;
        if($ids){// 导出指定的记录ID
            $idsArr = explode(',',$ids);
            $query->andFilterWhere(['in','pay.id',$idsArr]);
        }

        $paymentList = $query->all();

        if($paymentList){

            $objectPHPExcel = new \PHPExcel();
            $objectPHPExcel->setActiveSheetIndex(0);

            //表格头的输出
            $cell_value = ['请款单ID','请款单号','采购主体','采购来源','申请人','申请时间','审核人','审核时间','状态','供应商','联系人',
                '联系电话','申请金额','已付金额','支付方式','运费支付','结算方式','请款备注','审核备注'];
            foreach ($cell_value as $k => $v) {
                $objectPHPExcel->getActiveSheet()->setCellValue(chr($k+65) . '1',$v);
                $objectPHPExcel->getActiveSheet()->getColumnDimension(chr($k+65))->setWidth(20);
            }

            // 明细的输出
            foreach ( $paymentList as $key => $payment_value )
            {
                $num = $key + 2;
                $objectPHPExcel->getActiveSheet()->setCellValue('A'.$num ,$payment_value->id);
                $objectPHPExcel->getActiveSheet()->setCellValue('B'.$num ,$payment_value->requisition_number);
                $buyerCompany = BaseServices::getBuyerCompany(OverseasPaymentSearch::getOrderInfo($payment_value->pur_number,'is_drawback'),'name');
                $objectPHPExcel->getActiveSheet()->setCellValue('C'.$num ,$buyerCompany);
                $objectPHPExcel->getActiveSheet()->setCellValue('D'.$num ,$payment_value->source == 1 ? '合同' : '网采');
                $applicant      = $payment_value->applicant ? BaseServices::getEveryOne($payment_value->applicant):'';
                $objectPHPExcel->getActiveSheet()->setCellValue('E'.$num ,$applicant);
                $objectPHPExcel->getActiveSheet()->setCellValue('F'.$num ,$payment_value->application_time);
                $auditor        = $payment_value->auditor ? BaseServices::getEveryOne($payment_value->auditor) : '';
                $objectPHPExcel->getActiveSheet()->setCellValue('G'.$num ,$auditor);
                $objectPHPExcel->getActiveSheet()->setCellValue('H'.$num ,$payment_value->review_time);
                $objectPHPExcel->getActiveSheet()->setCellValue('I'.$num ,PurchaseOrderServices::getPayStatus($payment_value->pay_status));
                $objectPHPExcel->getActiveSheet()->setCellValue('J'.$num ,BaseServices::getSupplierName($payment_value->supplier_code));
                $objectPHPExcel->getActiveSheet()->setCellValue('K'.$num ,$payment_value->contact_person);
                $objectPHPExcel->getActiveSheet()->setCellValue('L'.$num ,$payment_value->contact_number);

                // 申请金额
                $check_private = '';
                if ($payment_value->pay_status == 10) {
                    if ($payment_value->pay_price < OverseasCheckPriv::getOverseasCheckPirce(3)) {
                        $check_private = '[经理/主管审核]';
                    } else {
                        $check_private = '[经理审核]';
                    }
                }
                if ($payment_value->source == 1) {
                    $show_pay_price = round($payment_value->pay_price, 3).$check_private;
                } else {
                    $show_pay_price = round(PurchaseOrderPay::getPrice($payment_value,true), 2).$check_private;
                }
                $objectPHPExcel->getActiveSheet()->setCellValue('M'.$num ,$show_pay_price);
                $objectPHPExcel->getActiveSheet()->setCellValue('N'.$num ,$payment_value->real_pay_price);
                $objectPHPExcel->getActiveSheet()->setCellValue('O'.$num ,SupplierServices::getDefaultPaymentMethod($payment_value->pay_type));

                $freight_payer = OverseasPaymentSearch::getOrderPayTypeInfo($payment_value->pur_number,'freight_payer');
                $freight_payer = $freight_payer == 1 ? '甲方支付' : '乙方支付';

                $objectPHPExcel->getActiveSheet()->setCellValue('P'.$num ,$freight_payer);
                $objectPHPExcel->getActiveSheet()->setCellValue('Q'.$num ,SupplierServices::getSettlementMethod($payment_value->settlement_method));
                $objectPHPExcel->getActiveSheet()->setCellValue('R'.$num ,$payment_value->create_notice);
                $objectPHPExcel->getActiveSheet()->setCellValue('S'.$num ,empty($payment_value->review_notice)?' ':$payment_value->review_notice);
            }

            header("Content-type:application/vnd.ms-excel;charset=UTF-8");
            header('Content-Type : application/vnd.ms-excel');
            header('Content-Disposition:attachment;filename="'.'请款单-汇总信息导出-'.date("Y年m月j日").'.xls"');
            $objWriter= \PHPExcel_IOFactory::createWriter($objectPHPExcel,'Excel5');
            $objWriter->save('php://output');
            die;

        }else{
            echo '<font color="red">未匹配到目标数据！</font>';
            die;
        }
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
                    if (empty($demand_numbers)) return jsonReturn(0, "{$model->requisition_number}<br />此请款单是老系统的单<br />需要在老系统的【海外仓-请款单】页面请款");
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
                // 付款状态：待财务审批、待财务付款、待采购经理审核 不能作废
                if (in_array($demand['pay_status'],[2,4,10])) {
                    Yii::$app->getSession()->setFlash('warning',"付款状态:{$demand['pay_status']},sku:{$demand['sku']},当前状态不能作废");
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
            return $this->renderAjax('cancel-index', [
                'purchase_demand_sku_map' => $purchase_demand_sku_map,
                'items_details' => $items_details['res'],
                'order_details' => $items_details['order_details'],
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
            $post_info    = Yii::$app->request->post();
            $data = Vhelper::changeData($post_info['cancel_ctq']);
            foreach ($data as $key => $value) {
                $log_message = "作废订单[备注：".$post_info['buyer_note']."]";
                PurchaseOrderServices::writelog($value['demand_number'], $log_message, $value['pur_number'], $update_data='');
            }

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
        $demand_numbers = array_column($data,'demand_number');

        $transaction = Yii::$app->db->beginTransaction();
        try {
            if ($audit_status ==2) {
                $cancelData['data'] = $data;
                $cancelData['pur_number'] = $post_info['pur_number'];
                $cancelData['cancel_id'] = $post_info['cancel_id'];
                $compactItemsInfo = PurchaseCompactItems::getCancelJudgeCompact($post_info['pur_number'], $cancelData);

                //判断是合同还是网采
                if (!empty($compactItemsInfo['orderPayInfo']) && !empty($compactItemsInfo['orderPayInfo'][0]['id']) ) {
                    # 合同：审核通过的，合同单，部分付款的，
                    // $orderInfo = PurchaseOrder::find()->select('purchase_type, is_new')->where(['pur_number'=>$compactItemsInfo['pur_number']])->asArray()->one();
                    //审核逻辑 返回： -1：没有退款的数据，数组：退款数据
                    $return_refund = PurchaseOrderCancelController::_heTongAudit($compactItemsInfo);
                }

                # 审核通过
                foreach ($data as $k => $v) {
                    $items_model = PurchaseOrderItems::find()->where(['pur_number'=>$v['pur_number'],'sku'=>$v['sku']])->one();
                    //如果入库数量大于零，部分到货等待剩余
                    
                    if ((int)$v['instock_qty_count'] >0) {
                        # 部分到货等待剩余 且 未定义$return_refund
                        if ($v['pay_price']>0 && !isset($return_refund)) {
							$surplus_price += round($items_model->price*$v['cancel_ctq'],3);
                        } elseif($v['pay_price']<=0 || (isset($return_refund) && $return_refund==-1) ) {
                            //判断是否全部取消 -- 已入库，未付款
                            $old_cancel_ctq = PurchaseOrderCancelSub::getCancelCtq($v['pur_number'],$v['sku'],$v['demand_number']); //之前已取消的
                            $res_cancel_ctq = $old_cancel_ctq+$v['cancel_ctq']; //之前取消的+现在取消的
                            $res_ruku_bccomp = bccomp($v['ctq'], $res_cancel_ctq+$v['instock_qty_count']); //采购数量=取消数量+入库数量：相等就等于0

                            if ($res_ruku_bccomp == 0) {
                                # 取消所有未到货的：部分到货不等待剩余
                                PlatformSummary::updateAll(['demand_status'=>11, 'is_purchase' => 1], ['demand_number'=>$v['demand_number']]);
                            } else {
                                # 返回订单状态
								PlatformSummary::updateAll(['demand_status'=>$v['old_demand_status'], 'is_purchase' => 1], ['demand_number'=>$v['demand_number']]);
                            }
                            PurchaseOrderCancel::updateAll(['audit_status' => $audit_status, 'is_push'=>0, 'audit_note'=>$post_info['audit_note']], ['id'=>$post_info['cancel_id']]);
                        }
                    } else {
                        # 没有入库数量，是未到货的
                        //判断是否有付款记录，如果有付款的，就生成退款单
                        if ($v['pay_price'] > 0  && !isset($return_refund) ) {
                            $surplus_price += round($items_model->price*$v['cancel_ctq'],3);
                        } elseif($v['pay_price'] <= 0 || (isset($return_refund) && $return_refund==-1) ) {
                            # 未入库，未付款-》直接作废 --未入库，未付款
                            PurchaseOrderCancel::updateAll(['audit_status' => $audit_status, 'is_push'=>0, 'audit_note'=>$post_info['audit_note']], ['id'=>$post_info['cancel_id']]);
                            $summaryData = ['cancel_id'=>$post_info['cancel_id'],'demand_number'=>$v['demand_number']];
                            PlatformSummary::updateCancelAll($summaryData,1);

                            # 同时改变合同的 合同总商品金额 和 合同实际总额
                            //作废金额单金额
                            $cancel_amount = round($v['cancel_ctq']*$v['price'],3); 
                            $compact_number = PurchaseCompactItems::find()->select('compact_number')->where(['pur_number'=>$v['pur_number'],'bind'=>1])->scalar();
                            if($compact_number){
                                $compact_model = PurchaseCompact::find()->where(['compact_number'=>$compact_number])->asArray()->one();
                                $real_money = ($compact_model['real_money']==0) ? 0 :($compact_model['real_money']-$cancel_amount);
                                PurchaseCompact::updateAll(['product_money'=>($compact_model['product_money']-$cancel_amount),'real_money'=>$real_money],['compact_number'=>$compact_number]);
                            }
                        }
                    }

                    //新版作废
                    if (isset($return_refund)) {
                        $ctqInfo = PurchaseOrderCancelSub::getSkuDemandCtq($post_info['pur_number'],false,$v['demand_number']);
                        if (!empty($ctqInfo) && $ctqInfo['cancel_ctq']>= $ctqInfo['demand_ctq'] && $return_refund==-1 ) {
                            //需求作废且没有退款单生成
                            PurchaseOrderCancel::updateAll(['audit_status' => $audit_status, 'is_push'=>0, 'audit_note'=>$post_info['audit_note']], ['id'=>$post_info['cancel_id']]);
                            $summaryData = ['cancel_id'=>$post_info['cancel_id'],'demand_number'=>$v['demand_number']];
                            PlatformSummary::updateCancelAll($summaryData,1);
                        }
                    }
                    $log_message = "审核作废订单【通过】[审核备注:".$post_info['audit_note']."]";
                    PurchaseOrderServices::writelog($v['demand_number'], $log_message, $v['pur_number'], $update_data='');

                    $summary_model = PlatformSummary::findOne(['demand_number'=>$v['demand_number']]);

                    if ($v['old_demand_status'] == 2) {
                        //信息变更等待审核
                        $summary_model->audit_level = PlatformSummaryServices::getOverseasChecklevel(1, $v['price']*$v['ctq']);
                    } elseif ($v['old_demand_status'] == 3) {
                        //采购单审核
                        $summary_model->audit_level = PlatformSummaryServices::getOverseasChecklevel(2, $v['price']*$v['ctq']);
                    } else {
                        $summary_model->audit_level = 0;
                    }
                    $summary_model->save();
                }
                PurchaseOrderItems::updateIsCancel($post_info['pur_number']); //修改采购单子表中的sku是否取消
                //修改作废信息状态
                // PurchaseOrderCancel::updateAll(['audit_status' => $audit_status], ['id'=>$post_info['cancel_id']]);
            } elseif ($audit_status ==3) {
                # 审核驳回
                foreach ($data as $k => $v) {
                    # 返回订单状态
                    PlatformSummary::updateAll(['demand_status'=>$v['old_demand_status']], ['demand_number'=>$v['demand_number']]);
                    $summary_model = PlatformSummary::findOne(['demand_number'=>$v['demand_number']]);

                    if ($v['old_demand_status'] == 2) {
                        //信息变更等待审核
                        $summary_model->audit_level = PlatformSummaryServices::getOverseasChecklevel(1, $v['price']*$v['ctq']);
                    } elseif ($v['old_demand_status'] == 3) {
                        //采购单审核
                        $summary_model->audit_level = PlatformSummaryServices::getOverseasChecklevel(2, $v['price']*$v['ctq']);
                    } else {
                        $summary_model->audit_level = 0;
                    }
                    $summary_model->save();

                    $log_message = "审核作废订单【驳回】[审核备注:". $post_info['audit_note'] ."]";
                    PurchaseOrderServices::writelog($v['demand_number'], $log_message, $v['pur_number'], $update_data='');
                }
                PurchaseOrderCancel::updateAll(['audit_status' => $audit_status], ['id'=>$post_info['cancel_id']]);
                PurchaseOrderItems::updateIsCancel($post_info['pur_number']); //修改采购单子表中的sku是否取消

                $transaction->commit();

                Yii::$app->getSession()->setFlash('success',"驳回成功");
                return ['error'=>200];
            }

            //新版作废：退款金额从尾款里面扣除
            if (isset($return_refund) && is_array($return_refund) ) {
                //该需求的取消数量等于需求数量时
                PlatformSummary::updateAll(['demand_status'=>13], ['in', 'demand_number', $demand_numbers]);// 修改订单状态，13.作废待退款
                //生成退款单
                $return_res = self::refundList($return_refund);
            } else if($surplus_price>0) {
                PlatformSummary::updateAll(['demand_status'=>13], ['in', 'demand_number', $demand_numbers]);// 修改订单状态，13.作废待退款
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

            }
            if (!empty($return_res) && $return_res == -1) {
                $transaction->rollBack();
            } else {
                PurchaseOrderCancel::saveCancel($post_info,3,1,1);
                $transaction->commit();
            }
        } catch(\Exception $e) {
            $transaction->rollBack();
            $message = '';
            $userRoleName = Yii::$app->authManager->getRolesByUser(Yii::$app->user->id);
            if (array_key_exists('超级管理员组',$userRoleName)) {
                $message = $e->getMessage();
            }
            Yii::$app->getSession()->setFlash('warning','对不起，操作失败了 '.$message);
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

    /**
     * 当前所有的 待询价状态的需求关联的采购员名单
     */
    public function actionGetAllBuyer()
    {
        $buyers = PlatformSummary::find()->alias("summary")
            ->leftJoin(PurchaseDemand::tableName()." d", "summary.demand_number = d.demand_number")
            ->leftJoin(PurchaseOrder::tableName()." o", "o.pur_number = d.pur_number")
            ->where(['summary.demand_status'=>1])
            ->andWhere(['summary.level_audit_status'=>1])
            ->andWhere(['summary.purchase_type'=>2])
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


    /**
     * 打印采购单
     * @return string
     */
    public function actionPrintData()
    {
        $ids            = Yii::$app->request->get('ids');
        $idsArr         = explode(',',$ids);

        $searchModel    = new OverseasPurchaseOrderSearch();
        $result         = $searchModel->search([]);
        $dataProvider   = $result['dataProvider'];
        $query          = $dataProvider->query;
        $query->where = ['in','summary.demand_number',$idsArr];

        $purchaseOrderList = $query->all();

        $map = [];
        foreach($purchaseOrderList as $order_value){
            $map['order.pur_number'][] = $order_value['pur_number'];
        }
        if(empty($map)){
            echo '<font color="red">未匹配到目标数据！</font>';
            die;
        }else{
            $ordersitmes     = PurchaseOrder::find()->alias('order')->joinWith(['purchaseOrderItems','supplier'])->where($map)->all();
            return $this->renderPartial('print', ['ordersitmes' => $ordersitmes]);
        }
    }

    public function actionGetPayAccount(){
        if(Yii::$app->request->isAjax&&Yii::$app->request->isPost){
            $request = Yii::$app->request;
            $compact_number= $request->post("compact_number");
            $price_types = $request->post("price_type");
            $pay_ratios = $request->post("pay_ratio");
            $payPrice = $request->post("pay_price");
            $order_freight = round($request->post("order_freight"),2);
            $priceType = $price_types?reset($price_types):1; //请款方式:比例(1)，手动(2)
            $payRatio = $pay_ratios?reset($pay_ratios):null; //请款比例
            $payCategory = ($priceType==1)?12:21; //比例请款(12),手动请款(21)
            $payInfo =  PurchaseOrderPay::getPayCategory($compact_number,$payCategory,$payPrice,$order_freight,$payRatio);
            $pay_name = PurchaseOrderServices::getPayCategory($payInfo['pay_category']);
            $selfPayName = \app\models\DataControlConfig::find()->select('values')->where(['type'=>'self_pay_name'])->scalar();
            $compact = PurchaseCompact::find()->where(['compact_number'=>$compact_number])->one();
            $self_pay_name_array = $selfPayName ? explode(',',$selfPayName) : ['合同运费','合同运费走私账'];
            $accountType = in_array($pay_name,$self_pay_name_array) ? 2 : ($compact->is_drawback==1 ? 2 : 1);//通过是否退税判断银行卡类型
            $bankCardInfo = \app\models\SupplierPaymentAccount::find()
                ->where(['supplier_code'=>$compact->supplier_code])
                ->andWhere(['account_type'=>$accountType])
                ->andWhere(['status'=>1])->one();
            $pay_info=['account_name'=>'','account'=>'','payment_platform_branch'];
            $pay_company_type = in_array($pay_name,$self_pay_name_array) ? 1 : $compact->is_drawback;
            $companyInfo = BaseServices::getBuyerCompany($pay_company_type);
            $pay_info['name'] = isset($companyInfo['name']) ? $companyInfo['name'] : '';
            $pay_info['address'] = isset($companyInfo['address']) ? $companyInfo['address'] : '';
            if(!empty($bankCardInfo)){
                if($accountType==2){
                    $pay_info['account_name'] = '（户名：'.$bankCardInfo->account_name.')';
                }else{
                    $pay_info['account_name'] ='';
                }
                $pay_info['account'] = $bankCardInfo->account;
                $pay_info['payment_platform_branch'] = $bankCardInfo->payment_platform_branch;
            }
            echo json_encode($pay_info);
            Yii::$app->end();
        }
    }


    /**
     * 验证海外仓采购单：网采单、合同单 是否已经申请过运费
     * @throws \yii\base\ExitException
     */
    public function actionVerifyFreight(){
        if(Yii::$app->request->isPost){
            $request        = Yii::$app->request;
            $compact_number = $request->post("compact_number");
            $pur_number     = $request->post("pur_number");

            if($compact_number){
                $have = PurchaseOrderPay::find()
                    ->alias('t')
                    ->select('sum(map.freight)')
                    ->leftJoin(OrderPayDemandMap::tableName().' as map','map.requisition_number=t.requisition_number')
                    ->where(['pur_number' => $compact_number])
                    ->andWhere(['not in','t.pay_status',[0, 3, 11, 12]]) // 作废 驳回
                    ->andWhere(['in','t.pay_status',[5 , 6]])
                    ->andWhere(['>','map.freight',0])
                    ->scalar();
            }else{
                $have = PurchaseOrderPay::find()
                    ->alias('t')
                    ->select('sum(map.freight)')
                    ->leftJoin(OrderPayDemandMap::tableName().' as map','map.requisition_number=t.requisition_number')
                    ->where(['pur_number' => $pur_number])
                    ->andWhere(['not in','t.pay_status',[0, 3, 11, 12]]) // 作废 驳回
                    ->andWhere(['in','t.pay_status',[5 , 6]])
                    ->andWhere(['>','map.freight',0])
                    ->scalar();
            }

            if(empty($have) or $have <= 0){
                $return = ['code' => 'no','message' => ''];
            }else{
                $return = ['code' => 'exists','message' => "您已经请过运费了<br/>【退税合同只可以单独请一次运费】"];
            }

            echo json_encode($return);
            Yii::$app->end();
        }

    }
}
