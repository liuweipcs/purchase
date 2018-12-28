<?php

namespace app\controllers;

use app\api\v1\models\ArrivalRecord;
use app\config\Vhelper;
use app\models\PurchaseDiscount;
use app\models\PurchaseFreightHistory;
use app\models\PlatformSummary;
use app\models\PlatformSummarys;
use app\models\PurchaseDemand;
use app\models\PurchaseOrderItems;
use app\models\PurchaseOrderOrders;
use app\models\PurchaseOrderPay;
use app\models\PurchaseOrderPayType;
use app\models\PurchaseOrderPayDetail;
use app\models\PurchaseOrderReceipt;
use app\models\PurchaseOrderRefundQuantity;
use app\models\PurchaseOrderShip;
use app\models\PurchaseReceive;
use app\models\PurchaseRefunds;
use app\models\SampleStock;
use app\models\SampleStockLog;
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
use app\models\PurchaseUser;
use app\models\PurchaseOrderBreakage;
use app\models\OperatLog;




use app\models\PurchaseCompact;
use app\models\PurchaseCompactItems;
use app\models\PurchaseCompactSearch;
use yii\data\Pagination;
use app\models\Template;
use app\models\SupplierPaymentAccount;
use app\models\PurchasePayForm;

/**
 * Created by PhpStorm.
 * User: ztt
 * Date: 2017/3/23 0023
 * Time: 18:41
 */
class PurchaseOrderController extends BaseController
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
     * Lists all PurchaseOrder models.
     * @return mixed
     */
    public function actionIndex()
    {
        $args = Yii::$app->request->queryParams;

        $searchModel = new PurchaseOrderSearch();
        $searchModel->source = isset($args['source']) ? (int)$args['source'] : 2; // 默认网采
        if($searchModel->source == 1) {
            $data = $searchModel->search15($args);
            return $this->render('index', [
                'searchModel' => $searchModel,
                'dataProvider' => $data,
                'source' => 1
            ]);
        } elseif($searchModel->source == 2) {
            $data = $searchModel->search3($args);
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
     * 查看产品明细
     * Displays a single PurchaseOrder model.
     * @param string $id  //$map['pur_purchase_order.pur_number'] = strpos($id,',') ? explode(',', $id) : $id;
     * @return mixed
     */
    public function actionView($id,$requisition_number=null)
    {
        $map['pur_purchase_order.pur_number']       = strpos($id,',') ? explode(',',$id):$id;
        $model= PurchaseOrder::find()->joinWith(['purchaseOrderItems'])->where($map)->one();
        if (!$model)
        {
            return '信息不存在';
        } else {
            $where['purchase_order_no']=$map['pur_purchase_order.pur_number'];
            $arriva=ArrivalRecord::find()->select('purchase_order_no,sku,name,delivery_qty,delivery_time,delivery_user,note')->where($where)->all();

            //订单详情
            $ordersitmes = PurchaseOrder::find()->joinWith(['purchaseOrderItems','supplier','orderNote'])->where($map)->one();
            $grade=PurchaseUser::findOne(['pur_user_id'=>Yii::$app->user->id]);

            //退款数量
            $refund_ctq = PurchaseOrderRefundQuantity::find()
                ->select(['sku','refund_qty'])
                ->where(['pur_number'=>$map['pur_purchase_order.pur_number'],'refund_status'=>1])
                ->asArray()
                ->all();
            $arr_refund = [];
            if(count($refund_ctq)>0){
                foreach ($refund_ctq as $key=>$val){
                    $arr_refund[$val['sku']] = $val['refund_qty'];
                }
            }

            return $this->renderAjax('view', [
                'ordersitmes' =>$ordersitmes,
                'grade' =>$grade,
                'name'  =>Yii::$app->request->get('name'),
                'arriva' => $arriva,
                'model' => $model,
                'refund_ctq' => $arr_refund,
                'requisition_number' =>$requisition_number,
            ]);
        }
    }
    /*public function actionView()
    {
        $id = Yii::$app->request->get('id');
        if(!$id)
            return json_encode(['error' => 404, 'message' => '参数有误']);
        //$MP = new PurchaseOrderPay();
        //$payInfo = $MP->getPayDetailById($id);
        //if(!$payInfo)
           // return json_encode(['error' => 404, 'message' => '支付单不存在']);
        //$pur_number = $payInfo['pur_number'];

        $order = new PurchaseOrder();
        $order_model = PurchaseOrder::findOne($id);
        $pur_number = $order_model->pur_number;
        $orderInfo = $order->getOrderDetail($pur_number);

        $arriva = ArrivalRecord::find()
                ->select('purchase_order_no, sku, name, delivery_qty, delivery_time, delivery_user, note')
                ->where(['purchase_order_no' => $pur_number])
                ->all();

        return $this->renderAjax('view', [
                'arriva' => $arriva,
                'orderInfo' => $orderInfo,
                'model' => $order
            ]);
    }*/

    /**
     * 采购审核详细确认
     * Displays a single PurchaseOrder model.
     * @param string $id
     * @return mixed
     */
    public function actionViews($id)
    {
        $map['pur_purchase_order.id']       = strpos($id,',') ? explode(',',$id):$id;
        $ordersitmes     = PurchaseOrder::find()->joinWith(['purchaseOrderItems','supplier'])->where($map)->one();
        return $this->renderAjax('views', [
            'model' =>$ordersitmes,
        ]);
    }


    /**
     * 打印采购单
     * @return string
     */
    public  function actionPrintData()
    {
        $ids             = Yii::$app->request->get('ids');
        $map['pur_purchase_order.id']       = strpos($ids,',') ? explode(',',$ids):$ids;
        $ordersitmes     = PurchaseOrder::find()->joinWith(['purchaseOrderItems','supplier'])->where($map)->all();
        return $this->renderPartial('print', ['ordersitmes'=>$ordersitmes]);
    }
    /**
     * 通过采购单号得出条形码
     * @param $codes
     * @return mixed
     */
    public  function  actionCode($codes)
    {

        $this->BarCode($codes);

    }

    /**
     * 打印合同
     * @return string
     */
    public  function  actionPrint()
    {


        $map['pur_purchase_order.id']       = Yii::$app->request->get('id');
        $ordersitmes     = PurchaseOrder::find()->joinWith(['purchaseOrderItems','supplierContent'])->where($map)->one();
        //Vhelper::dump($ordersitmes);
        return $this->renderPartial('prints', ['model'=>$ordersitmes]);
    }



    /**
     * 添加跟踪记录
     * @return string
     */
    public function  actionAddTracking()
    {
        $model = new PurchaseOrderShip();

        $tran = Yii::$app->db->beginTransaction();
        try {
            if ( $model->load(Yii::$app->request->post()) && $model->save())
            {
                //表修改日志-新增
                $change_content = "insert:新增id值为{$model->id}的记录";
                $change_data = [
                    'table_name' => 'pur_purchase_order_ship', //变动的表名称
                    'change_type' => '1', //变动类型(1insert，2update，3delete)
                    'change_content' => $change_content, //变更内容
                ];
                TablesChangeLog::addLog($change_data);
                $tran->commit();

                Yii::$app->getSession()->setFlash('success','恭喜你,新增物流记录成功');
                return $this->redirect(['index']);
            } else {
                $pur_number = Yii::$app->request->get('pur_number');
                return $this->render('tracking',['model' =>$model,'pur_number'=>$pur_number]);
            }
        } catch (Exception $e) {
            $tran->rollBack();
        }
        Yii::$app->getSession()->setFlash('error','恭喜你,新增物流记录失败');
        return $this->render(Yii::$app->request->referrer);
    }


    /**
     * 编辑跟踪记录
     * @return string
     */
    public function actionEditTracking()
    {
        $pur_number = Yii::$app->request->get('pur_number');
        if(Yii::$app->request->isPost) {
            $post = Yii::$app->request->post();

            $tran = Yii::$app->db->beginTransaction();
            try {
                if(isset($post['pur_number'])) {
                    $model = new PurchaseOrderShip();
                    $model->pur_number = $post['pur_number'];
                    $model->express_no       = $post['express_no'];
                    $model->cargo_company_id = $post['cargo_company_id'];
                    $model->is_push          = 0;
                    $model->save(false);

                    //表修改日志-新增
                    $change_content = "insert:新增id值为{$model->id}的记录";
                    $change_data = [
                        'table_name' => 'pur_purchase_order_ship', //变动的表名称
                        'change_type' => '1', //变动类型(1insert，2update，3delete)
                        'change_content' => $change_content, //变更内容
                    ];
                    TablesChangeLog::addLog($change_data);
                } else {
                    $data = Vhelper::changeData($post['PurchaseOrderShip']);
                    foreach($data as $v) {
                        $mos = PurchaseOrderShip::find()->where(['id' => $v['id']])->one();
                        $mos->cargo_company_id = $v['cargo_company_id'];
                        $mos->express_no       = $v['express_no'];
                        $mos->is_push          = 0;

                        //表修改日志-更新
                        $change_content = TablesChangeLog::updateCompare($mos->attributes, $mos->oldAttributes);
                        $change_data = [
                            'table_name' => 'pur_purchase_order_ship', //变动的表名称
                            'change_type' => '2', //变动类型(1insert，2update，3delete)
                            'change_content' => $change_content, //变更内容
                        ];
                        TablesChangeLog::addLog($change_data);

                        $mos->save(false);
                    }
                }
                $tran->commit();
            } catch (\Exception $e) {
                $tran->rollBack();
            }

            Yii::$app->getSession()->setFlash('success','恭喜你,修改物流记录成功');
            return $this->redirect(Yii::$app->request->referrer);
        } else {
            $model = PurchaseOrderShip::find()->where(['pur_number' => $pur_number])->all();
            return $this->renderAjax('edit-tracking', ['model' => $model, 'pur_number' => $pur_number]);
        }
    }

    /**
     * 获取跟踪记录
     * @return string
     */
    public  function actionGetTracking()
    {
        $id = Yii::$app->request->get('id');
        $model= PurchaseOrderShip::findAll(['pur_number'=>$id]);
        return $this->renderAjax('get-tracking',['model' =>$model]);
    }
    /**ajax进行验证
     * @return array
     */
    public function actionValidateForm () {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $model = new PurchaseOrderShip();
        $model->load(Yii::$app->request->post());
        return \yii\widgets\ActiveForm::validate($model);
    }
    /**ajax进行验证
     * @return array
     */
    public function actionValidateForms () {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $model = new PurchaseOrder();
        $model->load(Yii::$app->request->post());
        return \yii\widgets\ActiveForm::validate($model);
    }

    /**
     * 标记到货日期
     * @return string|\yii\web\Response
     */
    public function actionArrivalDate()
    {

        $model = new PurchaseOrder();

        if (Yii::$app->request->isPost)
        {
            $data     = Yii::$app->request->post()['PurchaseOrder'];
            $order_id =  strpos($data['order_id'],',') ? explode(',',$data['order_id']):$data['order_id'];
            $tran = Yii::$app->db->beginTransaction();
            try {
                if (is_array($order_id)) {
                    foreach ($order_id as $v) {
                        $models_order = $model->findOne(['pur_number' => $v]);
                        $models_order->date_eta = $data['date_eta'];
                        $models_order->is_arrival = $data['arrivaltype'];
                        $models_order->arrival_note = $data['arrival_note'];

                        //表修改日志-更新
                        $change_content = TablesChangeLog::updateCompare($models_order->attributes, $models_order->oldAttributes);
                        $change_data = [
                            'table_name' => 'pur_purchase_order', //变动的表名称
                            'change_type' => '2', //变动类型(1insert，2update，3delete)
                            'change_content' => $change_content, //变更内容
                        ];
                        TablesChangeLog::addLog($change_data);
                        $models_order->save(false);

                    }
                } else {
                    $models_order = $model->findOne(['pur_number' => $order_id]);
                    $models_order->date_eta = $data['date_eta'];
                    $models_order->is_arrival = $data['arrivaltype'];
                    $models_order->arrival_note = $data['arrival_note'];

                    //表修改日志-更新
                    $change_content = TablesChangeLog::updateCompare($models_order->attributes, $models_order->oldAttributes);
                    $change_data = [
                        'table_name' => 'pur_purchase_order', //变动的表名称
                        'change_type' => '2', //变动类型(1insert，2update，3delete)
                        'change_content' => $change_content, //变更内容
                    ];
                    TablesChangeLog::addLog($change_data);
                    $models_order->save(false);
                }
                $tran->commit();
            } catch (\Exception $e) {
                $tran->rollBack();
            }

            Yii::$app->getSession()->setFlash('success','恭喜你,标记成功');
            return $this->redirect(['index']);
        } else {
            $id = Yii::$app->request->get('id');
            //$id = strimplode(',',$id);
            return $this->renderAjax('arrival',['model' =>$model,'id'=>$id]);
        }
    }

    public $pay_type = [
        '1' => '剩余数量',
        '2' => '到货数量',
        '3' => '入库数量',
        '4' => '手动请款',
    ];



    // 执行请款动作
    private function executePayment($post)
    {
        $data = [
            'pay_type'      => (int)$post['payType'],
            'pur_number'    => $post['pur_number'],
            'pay_price'     => $post['pay_price'],
            'create_notice' => $post['create_notice'],
            'pay_status'    => -1,
        ];
        $purNumber = $data['pur_number'];

        // 金额校验
        if($data['pay_price'] <= 0) {
            Yii::$app->getSession()->setFlash('error', '申请失败，申请金额不能小于等于零');
            return $this->redirect(Yii::$app->request->referrer);
        }

        // 校验2
        $m1 = PurchaseOrder::getOrderTotalPrice($purNumber);
        $m2 = PurchaseOrderPay::getOrderPayMoney($purNumber);
        $m3 = PurchaseOrderPayType::getFreightMinusDiscount($purNumber);
        $m4 = $data['pay_price'] + $m2 + $m3;
        $result = bccomp($m1, $m4);
        if($result < 0) {
            Yii::$app->getSession()->setFlash('error', '申请失败，你申请的金额已经超过了订单的总金额');
            return $this->redirect(Yii::$app->request->referrer);
        }

        $pay_model = new PurchaseOrderPay();
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $order = PurchaseOrder::find()->where(['pur_number' => $purNumber])->one();
            $order->pay_status = $data['pay_status'];

            $pay_model->pur_number         = $purNumber;
            $pay_model->requisition_number = CommonServices::getNumber('PP');
            $pay_model->pay_status         = $data['pay_status'];
            $pay_model->pay_price          = $data['pay_price'];          // 请款金额
            $pay_model->pay_name           = '采购费用';
            $pay_model->settlement_method  = $order->account_type;       // 结算方式
            $pay_model->supplier_code      = $order->supplier_code;      // 供应商编码
            $pay_model->currency           = $order->currency_code;      // 币种
            $pay_model->pay_type           = $order->pay_type;           // 支付方式
            $pay_model->create_notice      = $data['create_notice'];     // 申请备注

            // 支付子表
            $pay_detail_model = new PurchaseOrderPayDetail();
            $pay_detail_model->pur_number  = $purNumber;
            $pay_detail_model->freight     = isset($post['freight']) ? $post['freight'] : '';
            $pay_detail_model->discount    = isset($post['discount']) ? $post['discount'] : '';
            $pay_detail_model->requisition_number = $pay_model->requisition_number;

            if(isset($data['sku_list'])) {
                $pay_detail_model->sku_list = json_encode($data['sku_list']);
            }

            //表修改日志-更新
            $change_content = TablesChangeLog::updateCompare($order->attributes, $order->oldAttributes);
            $change_data = [
                'table_name' => 'pur_purchase_order', //变动的表名称
                'change_type' => '2', //变动类型(1insert，2update，3delete)
                'change_content' => $change_content, //变更内容
            ];
            TablesChangeLog::addLog($change_data);

            $a = $order->save(false);
            $b = $pay_model->save(false);
            $d = $pay_detail_model->save(false);

            //表修改日志-新增
            $change_content = "insert:新增id值为{$pay_model->id}的记录";
            $change_data = [
                'table_name' => 'pur_purchase_order_pay', //变动的表名称
                'change_type' => '1', //变动类型(1insert，2update，3delete)
                'change_content' => $change_content, //变更内容
            ];
            TablesChangeLog::addLog($change_data);
            $c = PurchaseOrderPayType::setPayType($purNumber, ['request_type' => $data['pay_type']]); // 请款类型

            // 写采购日志
            PurchaseLog::addLog(['pur_number' => $purNumber, 'note' => '国内仓申请付款（含金额校验）']);

            $submit = $a && $b && $c && $d;

            if($submit) {
                $transaction->commit();
                Yii::$app->getSession()->setFlash('success','恭喜您，申请付款成功，请前往请款单页面确认提交');
                return $this->redirect(Yii::$app->request->referrer);
            } else {
                $transaction->rollBack();
                Yii::$app->getSession()->setFlash('success','对不起，申请付款失败了');
                return $this->redirect(Yii::$app->request->referrer);
            }
        } catch(Exception $e) {
            $transaction->rollBack();
            Yii::$app->getSession()->setFlash('success','对不起，申请付款失败了');
            return $this->redirect(Yii::$app->request->referrer);
        }
    }

    /*
     * 采购订单-申请付款(这是整个请款单出生的地方)
     * 注：每一个新生的请款单，初始状态都是待提交状态-1
     */
    public function actionPayment()
    {
        $request = Yii::$app->request;
        if($request->isPost) {
            $payment = $request->post('Payment');
            if($payment['source'] == 1) {
                return $this->executeCompactPayment($payment);
            } else {
                return $this->executePayment($request->post());
            }
        } else {
            $opn = $request->get('pur_number');

            // 订单请款流程
            $order_model = new PurchaseOrder();
            $pay_model   = new PurchaseOrderPay();
            $model       = $order_model->findOne(['pur_number' => $opn]);

            if(is_null($model)) {
                return '订单信息不存在';
            }

            if(in_array($model->purchas_status, [1, 2, 4, 10])) {
                return '你的采购单状态可能是：待确认、采购已确认（但还未通过审核）、 撤销、 作废，这些状态下，暂时不能请款';
            }

            if(in_array($model->shipfees_audit_status, [0, 2])) {
                return '你有修改过订单信息，还没有审核通过，暂时不能请款';
            }

            $payInfo     = $pay_model->getPayDetail($opn);
            $orderInfo   = $order_model->getOrderDetail($opn, $payInfo['skuNum']);

            $type        = $model->purchaseOrderPayType;
            $rpt         = $type ? $type->request_type : 0;  // 订单的请款方式

            if($payInfo['countPayMoney'] == 0) {
                $rpt = 0;
            }

            return $this->renderAjax('multiple-payment', [
                'model'       => $model,
                'type'        => $type,
                'orderInfo'   => $orderInfo,
                'payInfo'     => $payInfo,
                'rpt'         => $rpt
            ]);



        }


    }

    // 付款申请书
    public function actionWritePayment()
    {
        $request = Yii::$app->request;
        if($request->isPost) {
            $data = $request->post('Payment');
            $model = new PurchasePayForm();
            $model->attributes = $data;
            if(!$model->validate()) {
                Vhelper::dump($model->errors);
            }
            $tran = Yii::$app->db->beginTransaction();
            try {
                PurchaseLog::addLog([
                    'pur_number' => $data['compact_number'],
                    'note' => "创建付款申请书，对应的请款单id为{$data['pay_id']}"
                ]);

                $res = $model->save(false);

                //表修改日志-新增
                $change_content = "insert:新增id值为{$model->id}的记录";
                $change_data = [
                    'table_name' => 'pur_purchase_pay_form', //变动的表名称
                    'change_type' => '1', //变动类型(1insert，2update，3delete)
                    'change_content' => $change_content, //变更内容
                ];
                TablesChangeLog::addLog($change_data);
                $tran->commit();
            } catch (\Exception $e) {
                $tran->rollBack();
            }
            if($res) {
                Yii::$app->getSession()->setFlash('success','恭喜你，保存付款申请书成功');
                return $this->redirect(['index']);
            } else {
                Yii::$app->getSession()->setFlash('success','对不起，保存付款申请书失败');
                return $this->redirect(['index']);
            }
        } else {
            $cpn = $request->get('compact_number');
            $pid = $request->get('pid');
            $tid = $request->get('tid');
            if(!$cpn || !$pid) {
                throw new \yii\web\NotFoundHttpException('参数错误，必须指定正确的合同号与支付单ID');
            }
            if($tid) {
                // 渲染选择的模板
                $tpl = Template::findOne($tid);
                $tplPath = $tpl->style_code;
                // 获取供应商账户信息
                $model = PurchaseOrderPay::findOne($pid);
                $account = SupplierPaymentAccount::find()->where(['supplier_code' => $model->supplier_code])->one();
                $pos = PurchaseCompact::getPurNumbers($model->pur_number);
                return $this->render("//template/tpls/{$tplPath}", [
                    'model' => $model,
                    'account' => $account,
                    'pos' => implode(',', $pos),
                    'tpl_id' => $tpl->id
                ]);
            }
            // 加载模板列表页
            $tpls = Template::find()->where(['platform' => 1, 'type' => 'FKSQS', 'status' => 1])->all();
            return $this->render('write-payment', [
                'tpls' => $tpls,
                'compact_number' => $cpn,
                'pid' => $pid
            ]);
        }
    }





    /**
     * 获取运费和金额
     */
    public  function  actionGetPurchaseAmount()
    {
        //获取运费
        $pay_ship_amount = PurchaseOrderShip::find()->select('sum(freight)')->where(['pur_number'=>Yii::$app->request->post()['ast'][0]])->scalar();
        //统计金额
        $price = PurchaseOrderItems::find()->select('items_totalprice')->where(['pur_number'=>Yii::$app->request->post()['ast'][0]])->asArray()->all();

        $price = ArrayHelper::getColumn($price,'items_totalprice');
        $price = array_sum($price);

        if (Yii::$app->request->post()['ast'][1] && Yii::$app->request->post()['ast'][2])
        {
            //sku金额加上运费
            $data['status'] =1;
            $data['amount'] =$pay_ship_amount + $price;

        } elseif(Yii::$app->request->post()['ast'][1]){
            //sku金额
            $data['status'] =1;
            $data['amount'] = $price;
        } else {
            //运费
            $data['status'] =1;
            $data['amount'] =$pay_ship_amount;
        }
        echo Json::encode($data);

    }


    // 批量付款申请3.0
    public function actionAllpayment()
    {
        $request = Yii::$app->request;
        if($request->isPost) {
            $post = Yii::$app->request->post();
            $payData = $post['AllPayment'];
            $t = Yii::$app->db->beginTransaction();
            $flag = true;
            try {
                foreach($payData as $v) {
                    if($v['pay_price'] <= 0) {
                        Yii::$app->getSession()->setFlash('error','对不起，存在请款金额为零的订单');
                        return $this->redirect(Yii::$app->request->referrer);
                    }
                    $model = PurchaseOrder::find()->where(['pur_number' => $v['pur_number']])->one();
                    if(empty($model)) {
                        Yii::$app->getSession()->setFlash('error','对不起，有一个订单没有数据');
                        return $this->redirect(Yii::$app->request->referrer);
                    }

                    $pay = new PurchaseOrderPay();
                    $pay->requisition_number = CommonServices::getNumber('PP');
                    $pay->pur_number         = $model->pur_number;
                    $pay->settlement_method  = $model->account_type;
                    $pay->supplier_code      = $model->supplier_code;
                    $pay->pay_status         = 2; // 已申请付款(待审批，批量付款直接到财务环节)
                    $pay->pay_name           = '采购费用';
                    $pay->pay_price          = $v['pay_price'];
                    $pay->create_notice      = $model->confirm_note;
                    $pay->currency           = $model->currency_code;
                    $pay->pay_type           = $model->pay_type;

                    if(!in_array($model->purchas_status, [6, 8, 9])) {
                        $model->purchas_status = 7;
                    }

                    $model->pay_status = 2;

                    PurchaseLog::addLog([
                        'pur_number' => $model->pur_number,
                        'note' => '国内批量付款3.0',
                    ]);

                    $a = $pay->save(false);

                    //表修改日志-新增
                    $change_content = "insert:新增id值为{$pay->id}的记录";
                    $change_data = [
                        'table_name' => 'pur_purchase_order_pay', //变动的表名称
                        'change_type' => '1', //变动类型(1insert，2update，3delete)
                        'change_content' => $change_content, //变更内容
                    ];
                    TablesChangeLog::addLog($change_data);

                    //表修改日志-更新
                    $change_content = TablesChangeLog::updateCompare($model->attributes, $model->oldAttributes);
                    $change_data = [
                        'table_name' => 'pur_purchase_order', //变动的表名称
                        'change_type' => '2', //变动类型(1insert，2update，3delete)
                        'change_content' => $change_content, //变更内容
                    ];
                    TablesChangeLog::addLog($change_data);

                    $b = $model->save(false);
                    if(!$a || !$b) {
                        $flag = false;
                        break;
                    }
                }
                if($flag) {
                    $t->commit();
                    Yii::$app->getSession()->setFlash('success','恭喜您，申请付款成功，等待出纳付款');
                    return $this->redirect(Yii::$app->request->referrer);
                } else {
                    $t->rollBack();
                    Yii::$app->getSession()->setFlash('error','对不起，有一个订单在请款时失败了');
                    return $this->redirect(Yii::$app->request->referrer);
                }
            } catch(Exception $e) {
                $t->rollBack();
                Yii::$app->getSession()->setFlash('success','对不起，申请付款出错了');
                return $this->redirect(Yii::$app->request->referrer);
            }
        } else {
            $data = $request->get();
            $ids = isset($data['ids']) ? $data['ids'] : '';
            if($ids == '') {
                return '参数错误，没有可用的订单ID';
            }
            $ids = explode(',', $ids);

            $orders = PurchaseOrder::find()
                ->joinWith(['purchaseOrderItems', 'purchaseOrderPayType', 'purchaseOrderPay'])
                ->where(['in', 'pur_purchase_order.id', $ids])
                ->andWhere(['pur_purchase_order.shipfees_audit_status' => 1, 'pur_purchase_order.buyer' => Yii::$app->user->identity->username])
                ->andWhere(['not in', 'pur_purchase_order.purchas_status', [1, 4, 10]])
                ->asArray()
                ->all();
            if(empty($orders)) {
                $msg = '<h5>对不起，不能请款，可能的原因如下：</h5><p>1. 订单处于待确认、撤销或作废状态。</p><p>2. 订单的采购员不是当前登录用户。</p><p>3. 订单修改过信息，但是还没有通过审核。</p>';
                return $msg;
            }
            foreach($orders as $v) {
                if(isset($v['purchaseOrderPay']) && !empty($v['purchaseOrderPay'])) {
                    return '你勾选的订单中，存在已经申请过付款的，不能再申请付款了';
                }
            }
            return $this->renderAjax('all-payment', ['orders' => $orders]);
        }
    }

    /**
     *  根据采购单号获取采购日志
     */
    public function actionGetPurchaseLog()
    {
        $id = Yii::$app->request->get('id');
        $model= PurchaseLog::findAll(['pur_number'=>$id]);
        if (empty($model))
        {
            return '暂无日志';
        }
        return $this->renderAjax('get-purchase-log',['model' =>$model]);

    }

    /**
     * 获取采购备注
     */
    public function  actionGetPurchaseNote()
    {
        $id = Yii::$app->request->get('id');
        $model = PurchaseNote::getPurchaseNote($id);
        
        $model_pay = PurchaseOrderPay::findAll(['pur_number' => $id]);
        if (empty($model) && empty($model_pay[0]['create_notice']))
        {
            return '暂无备注';
        }
        return $this->renderAjax('get-note',['model' =>$model,'model_pay' => $model_pay]);
    }

    /**
     * 增加采购备注
     * @return string|\yii\web\Response
     */
    public function actionAddPurchaseNote()
    {
        $model = new PurchaseNote();
        $id = Yii::$app->request->get('pur_number');
        $model_get= PurchaseNote::findAll(['pur_number'=>$id]);
        if ( Yii::$app->request->isPost AND Yii::$app->request->post()) {
            $model->load(Yii::$app->request->post());
            $tran = Yii::$app->db->beginTransaction();
            try {
                $message = '';
                if(!$model->save()){// 保存失败 抛出异常
                    foreach($model->errors as $v) {
                        $message = implode(',', $v)."\r";
                    }
                    throw new \Exception($message);
                }

                //表修改日志-新增
                $change_content = "insert:新增id值为{$model->id}的记录";
                $change_data = [
                    'table_name' => 'pur_purchase_note', //变动的表名称
                    'change_type' => '1', //变动类型(1insert，2update，3delete)
                    'change_content' => $change_content, //变更内容
                ];
                TablesChangeLog::addLog($change_data);

                $flag = Yii::$app->request->post()['flag'];
                Yii::$app->getSession()->setFlash('success', '恭喜你,新增备注成功');
                $data = [
                    '1' => Yii::$app->request->referrer,
                    '2' => Yii::$app->request->referrer,
                    '3' => Yii::$app->request->referrer,
                    '4' => Yii::$app->request->referrer,
                ];
                $tran->commit();
                return $this->redirect($data[$flag]);
            } catch (\Exception $e) {
                $tran->rollBack();
                Yii::$app->getSession()->setFlash('error', "抱歉，保存失败\r".$e->getMessage());
                return $this->redirect(Yii::$app->request->referrer);
            }
        } else {
            $pur_number = Yii::$app->request->get('pur_number');
            $flag = Yii::$app->request->get('flag');
            $models = PurchaseNote::findAll(['pur_number' => $pur_number]);
            if (!empty($flag) && $flag > 1) {
                return $this->render('note', ['model' => $model, 'pur_number' => $pur_number, 'flag' => $flag, 'models' => $models, 'model_get' => $model_get]);
            } else {
                return $this->renderAjax('note', ['model' => $model, 'pur_number' => $pur_number, 'flag' => $flag, 'models' => $models, 'model_get' => $model_get]);
            }
        }
    }

    /**
     * 删除备注 第一条备注不能被删除,当前用户只能删除自己的备注
     * @param $id
     * @return \yii\web\Response
     * @throws \Exception
     * @throws \Throwable
     */
    public function actionDeleteNote($id){
        $model  = PurchaseNote::find()->where(['id'=>$id,'create_id'=>Yii::$app->user->id])->one();
        if($model){
            //表修改日志-删除
            $change_content = "delete:删除id值为{$model->id}的记录";
            $change_data = [
                'table_name' => 'pur_purchase_note', //变动的表名称
                'change_type' => '3', //变动类型(1insert，2update，3delete)
                'change_content' => $change_content, //变更内容
            ];
            TablesChangeLog::addLog($change_data);

            $model->delete();
            Yii::$app->getSession()->setFlash('success','恭喜你,删除备注成功');
            return $this->redirect(Yii::$app->request->referrer);
        }else{
            Yii::$app->getSession()->setFlash('error','恭喜你,删除备注失败,你不能删除别人的备注');
            return $this->redirect(Yii::$app->request->referrer);
        }
    }

    /**
     * 编辑采购单
     * 部分退款，全额退款，作废订单
     */
    public function actionEdit()
    {
        if(Yii::$app->request->isPost) {
            try {
                $model_receipt = new PurchaseOrderReceipt(); // 采购单收款表
                $post = Yii::$app->request->post();
                $pur_number = $post['pur_number'];

                $result = $model_receipt->verifyRefundEvent($post); // 校验

                if($result['error'] == 1) {
                    Yii::$app->getSession()->setFlash('error',$result['message']);
                    return $this->redirect(Yii::$app->request->referrer);
                }

                $transaction = Yii::$app->db->beginTransaction();
                $order_s = PurchaseOrder::find()->where(['pur_number' => $pur_number])->one();

                //运费
                $freight = Yii::$app->request->post('freight');
                //优惠
                $discount = Yii::$app->request->post('discount');
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
                        'freight'           => !empty($freight)?$freight:0,
                        'discount'           => !empty($discount)?$discount:0,
                    ];
                    $a = $model_receipt->insertRow($data);

                    //表修改日志-更新
                    $change_content = TablesChangeLog::updateCompare($order_s->attributes, $order_s->oldAttributes);
                    $change_data = [
                        'table_name' => 'pur_purchase_order', //变动的表名称
                        'change_type' => '2', //变动类型(1insert，2update，3delete)
                        'change_content' => $change_content, //变更内容
                    ];
                    TablesChangeLog::addLog($change_data);
                    $b = $order_s->save(false);
                    $is_submit = $a && $b;

                    // 全额退款（只能退一次）
                } elseif($post['refund_status'] == 4 && !empty($post['money'])) {

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
                        'freight'           => !empty($freight)?$freight:0,
                        'discount'           => !empty($discount)?$discount:0,
                    ];
                    $a = $model_receipt->insertRow($data);

                    $order_s->confirm_note  = $post['confirm_note'];
                    $order_s->refund_status = 4;

                    //表修改日志-更新
                    $change_content = TablesChangeLog::updateCompare($order_s->attributes, $order_s->oldAttributes);
                    $change_data = [
                        'table_name' => 'pur_purchase_order', //变动的表名称
                        'change_type' => '2', //变动类型(1insert，2update，3delete)
                        'change_content' => $change_content, //变更内容
                    ];
                    TablesChangeLog::addLog($change_data);
                    $b = $order_s->save(false);
                    $is_submit = $a && $b;


                } elseif($post['refund_status'] == 10) {
                    // 已付款、待财务审批、待财务付款、待采购经理审核、富有付款待审核状态，不可以作废
                    if(in_array($order_s->pay_status,[2,4,10,13]) || in_array($order_s->purchas_status, PurchaseOrder::$arrival_warehouse)){
                        $transaction->rollBack();
                        Yii::$app->getSession()->setFlash('warning','对不起，已付款、待财务审批、待财务付款、待采购经理审核、富有付款待审核状态/部分到货，全到货，部分到货等待剩余、部分到货不等待剩余状态，不可以作废');
                        return $this->redirect(Yii::$app->request->referrer);
                    }

                    // 更新采购单不再去alibaba拉去物流记录了
                    $or = PurchaseOrderOrders::find()->where(['pur_number' => $pur_number])->one();

                    if($or) {
                        $or->is_request = 1;

                        //表修改日志-更新
                        $change_content = TablesChangeLog::updateCompare($or->attributes, $or->oldAttributes);
                        $change_data = [
                            'table_name' => 'pur_purchase_order_orders', //变动的表名称
                            'change_type' => '2', //变动类型(1insert，2update，3delete)
                            'change_content' => $change_content, //变更内容
                        ];
                        TablesChangeLog::addLog($change_data);
                        $or->save();
                    }

                    if($order_s->warehouse_code == 'de-yida') {
                        $items = PurchaseOrderItems::find()->andWhere(['pur_number' => $pur_number])->all();
                        foreach($items as $item) {
                            //样品仓减在途库存
                            SampleStock::saveStock($item->sku, -($item->ctq),0, $order_s->warehouse_code,0);
                            //样品仓增加在途库存日志
                            SampleStockLog::saveStockLog($item->sku,'-'.$item->ctq,0,$pur_number,5, $order_s->warehouse_code,0);
                        }
                    }

                    // 操作日志
                    $datas = [];
                    $msg              = '在' . date('Y-m-d H:i:s') . '由' . Yii::$app->user->identity->username . '对' . $post['pur_number'] . '进行了作废';
                    $datas['type']    = 12;
                    $datas['pid']     = '';
                    $datas['module']  = '采购单作废';
                    $datas['content'] = $msg;

                    Vhelper::setOperatLog($datas);

                    // 更新付款状态为作废
                    $a = PurchaseOrderPay::updatePayStatus($pur_number, 0);

                    // 更新中间表
                    PurchaseDemand::UpdateOne($pur_number);

                    // 作废采购单重新推送
                    $order_s->is_push        = 0;
                    $order_s->confirm_note   = $post['confirm_note'];
                    $order_s->purchas_status = 10;

                    //表修改日志-更新
                    $change_content = TablesChangeLog::updateCompare($order_s->attributes, $order_s->oldAttributes);
                    $change_data = [
                        'table_name' => 'pur_purchase_order', //变动的表名称
                        'change_type' => '2', //变动类型(1insert，2update，3delete)
                        'change_content' => $change_content, //变更内容
                    ];
                    TablesChangeLog::addLog($change_data);
                    $b = $order_s->save(false);
                    $is_submit = $a && $b;
                } else {
                    $transaction->rollBack();
                    Yii::$app->getSession()->setFlash('error','对不起，你提交的数据有误');
                    return $this->redirect(Yii::$app->request->referrer);
                }

                $status = $is_submit;
                if($status){
                    //部分退款、全额退款记录退货数量
                    if($post['refund_status'] == 3 || $post['refund_status'] == 4){
                        //退货sku 数量   根据sku展示的
                        $skuQty = Yii::$app->request->post('refund_qty');
                        if($skuQty) {
                            //本次退款数
                            $this_refund_ctq = 0;
                            //历史退款数
                            $history_ctq = PurchaseOrderRefundQuantity::find()
                                ->select('sum(refund_qty) as total')
                                ->where(['pur_number' => $pur_number])
                                ->scalar();
                            foreach ($skuQty as $key=>$val) {
                                if($val && $val>0) {
                                    $this_refund_ctq += $val;
                                    $result = PurchaseOrderRefundQuantity::verifyRefundQty($pur_number, $key ,$val); // 校验退货数量
                                    if ($result['error'] == 1) {
                                        Yii::$app->getSession()->setFlash('error', $result['message']);
                                        $transaction->rollBack();
                                        return $this->redirect(Yii::$app->request->referrer);
                                    }

                                    $item = PurchaseOrderItems::find()->where(['pur_number' => $pur_number, 'sku' => $key])->one();
                                    if ($item && $model_receipt->requisition_number) {
                                        $refundQuantity = new PurchaseOrderRefundQuantity();
                                        $refundQuantity->sku = $key;
                                        $refundQuantity->name = $item->name;
                                        $refundQuantity->refund_qty = $val;
                                        $refundQuantity->purchase_qty = $item->ctq;
                                        $refundQuantity->price = $item->price;
                                        $refundQuantity->pur_number = $item->pur_number;
                                        $refundQuantity->requisition_number = $model_receipt->requisition_number;//退款申请的编号
                                        $refundQuantity->refund_status = 0;
                                        $refundQuantity->creator = Yii::$app->user->identity->username;
                                        $refundQuantity->created_at = date('Y-m-d H:i:s', time());
                                        $status = $refundQuantity->save();
                                        if (!$status) {
                                            $status = 0;
                                            break;
                                        }
                                    }else{
                                        $status = 0;
                                        break;
                                    }
                                }
                            }
                        }
                    }

                    //作废订单记录要减少的在途数量
                    if($post['refund_status'] == 10){
                        $items = PurchaseOrderItems::find()->andWhere(['pur_number' => $pur_number])->all();
                        if($items && count($items)>0) {
                            foreach ($items as $item){
                                $refundQuantity = new PurchaseOrderRefundQuantity();
                                $refundQuantity->sku = $item->sku;
                                $refundQuantity->name = $item->name;
                                $refundQuantity->refund_qty = $item->ctq;
                                $refundQuantity->purchase_qty = $item->ctq;
                                $refundQuantity->price = $item->price;
                                $refundQuantity->pur_number = $item->pur_number;
                                $refundQuantity->requisition_number = '';//因为作废订单的采购单没有付款  没有收款记录
                                $refundQuantity->refund_status = 0;
                                $refundQuantity->creator = Yii::$app->user->identity->username;
                                $refundQuantity->created_at = date('Y-m-d H:i:s', time());
                                $refundQuantity->is_cancel = 1;//作废订单的标记
                                $status = $refundQuantity->save();
                                if (!$status) {
                                    $status = 0;
                                    break;
                                }
                            }
                        }
                    }

                    if($status){
                        $transaction->commit();
                        Yii::$app->getSession()->setFlash('success','恭喜你，操作成功了');
                    }else{
                        $transaction->rollBack();
                        Yii::$app->getSession()->setFlash('error','对不起，操作失败了');
                    }
                    return $this->redirect(Yii::$app->request->referrer);
                } else {
                    $transaction->rollBack();
                    Yii::$app->getSession()->setFlash('error','对不起，操作失败了');
                    return $this->redirect(Yii::$app->request->referrer);
                }
            } catch (Exception $e) {
                $transaction->rollBack();


                Vhelper::dump($e->getMessage());

                Yii::$app->getSession()->setFlash('error','对不起，操作失败了');
                return $this->redirect(Yii::$app->request->referrer);
            }
        } else {
            $pur_number    = Yii::$app->request->get('pur_number');
            $order_model   = new PurchaseOrder();
            $pay_model     = new PurchaseOrderPay();
            $receipt_model = new PurchaseOrderReceipt();

            $refundInfo    = $receipt_model->getOrderRefundInfo($pur_number);
            $payInfo       = $pay_model->getPayDetail($pur_number);
            $orderInfo     = $order_model->getOrderDetail($pur_number, $payInfo['skuNum']);
            if($payInfo['countPayMoney'] <= 0) {
                $payInfo['countPayMoney'] = 0;
            } else {
                $payInfo['countPayMoney'] = $payInfo['countPayMoney'] + $orderInfo['order_freight'] - $orderInfo['order_discount'];
            }
            if($payInfo['hasPaidMoney'] <= 0) {
                $payInfo['hasPaidMoney'] = 0;
            } else {
                $payInfo['hasPaidMoney'] = $payInfo['hasPaidMoney'] + $orderInfo['order_freight'] - $orderInfo['order_discount'];
            }
            //可退款数量
            $ctq = [];
            $total_piece = 0;//总个数
            $total_price = 0;//总金额
            $refund_qty = [];//总退货数量
            $freight = 0;//运费
            $discount = 0;//优惠
            $only_all_able = 0;//全部退款后：再次提交退款的时候，类型只能选择“全部退款”，不能做修改，只有运费，优惠可以编辑
            $able_paid_money = 0;
            if(count($orderInfo['purchaseOrderItems'])>0){
                foreach ($orderInfo['purchaseOrderItems'] as $val){
                    $quantity = PurchaseOrderRefundQuantity::find()
                        ->alias('refund')
                        ->leftJoin(PurchaseOrderReceipt::tableName(), 'pur_purchase_order_receipt.requisition_number=refund.requisition_number')
                        ->select('sum(refund.refund_qty) as total')
                        ->where(['refund.pur_number' => $val['pur_number'],'refund.sku'=>$val['sku']])
                        ->andWhere(['!=','pur_purchase_order_receipt.pay_status', 10])
                        ->scalar();
                    $cancel_num = 0;
                    if(!empty($quantity) && $quantity>0){
                        $refund_qty[$val['sku']] = $quantity;
                        $cancel_num = $quantity;
                    }else{
                        $refund_qty[$val['sku']] = 0;
                    }

                    //获取可退金额 总金额-已退金额
                    $able_paid_money = $payInfo['hasPaidMoney'] - $refundInfo['has_refund_money'];
                    //计算未到货数量  订单数量-入库数量
                    $weidaohuo_num = $val['ctq']-$val['ruku_num'];
                    //未到货数量大于0（或者可退金额大于0） 才可退数量
                    if($weidaohuo_num-$cancel_num > 0){
                        //获取已退货数量
                        $ctq[$val['sku']] = $weidaohuo_num-$cancel_num;
                        $total_piece = bcadd($total_piece , $weidaohuo_num-$cancel_num);
                        $total_price += bcmul($weidaohuo_num-$cancel_num,$val['price'],3);
                    }
                    //如果可取消数一直为0且（运费或者优惠大于0）
                    if($weidaohuo_num-$cancel_num == 0 && $able_paid_money>0){
                        $only_all_able = 1;
                    }
                }
                //获取可退运费和优惠
                $payType = PurchaseOrderPayType::find()->where(['pur_number'=>$pur_number])->one();
                if($payType){
                    $freight = isset($payType->freight)?$payType->freight:0;
                    $discount = isset($payType->discount)?$payType->discount:0;
                }
                $quantity = PurchaseOrderReceipt::find()
                    ->select('sum(discount) as discount,sum(freight) as freight')
                    ->where(['pur_number' => $pur_number])
                    ->andWhere(['!=','pay_status', 10])
                    ->one();
                if($quantity){
                    if(!empty($quantity['freight']) && $freight>0){
                        $freight = bcsub($freight,$quantity['freight'],2);
                    }
                    if(!empty($quantity['discount']) && $discount>0){
                        $discount = bcsub($discount,$quantity['discount'],2);
                    }
                }
            }

            return $this->renderAjax('edit', [
                'orderInfo' => $orderInfo,
                'payInfo' => $payInfo,
                'refundInfo' => $refundInfo,
                'ctq' => $ctq,
                'total_piece' => $total_piece,
                'total_price' => sprintf("%01.3f",$total_price),
                'refund_qty' => $refund_qty,
                'freight' => $freight,//运费
                'discount' => $discount,//优惠
                'able_paid_money' => $able_paid_money,//可退运费
                'only_all_able' => $only_all_able//全部退款后：再次提交退款的时候，类型只能选择“全部退款”，不能做修改，只有运费，优惠可以编辑
            ]);
        }
    }

    /**
     * excel导出
     * @throws \yii\web\HttpException
     */
    public function actionExportCsvs()
    {
        $id1 = Yii::$app->request->get('id1');
        $id2 = Yii::$app->request->get('id2');
        if(!empty($id1) && !empty($id2))
        {
            $model = PurchaseOrder::find()->joinWith(['purchaseOrderItems'])->where(['in','pur_purchase_order.purchase_type',2])->andWhere(['between','pur_purchase_order.id',$id1,$id2])->asArray()->all();
        } else{
            exit('没有参数');
        }


        $table = [
            '采购单号',
            '采购仓库',
            '中转仓库',
            '采购员',
            '采购日期',
            'SKU',
            '货品名称',
            '采购单价',
            '最新报价',
            '采购数量',
            '实际应付货款',
            '供应商名称',
            '付款状态',
            '采购状态',
            '审核时间',
            '订单号',
            '入库数量',
        ];

        $table_head = [];
        foreach($model as $k=>$v)
        {
            foreach ($v['purchaseOrderItems'] as $c=>$vb)
            {
                $table_head[$k][$c][]=$v['pur_number'];
                $table_head[$k][$c][]=BaseServices::getWarehouseCode($v['warehouse_code']);
                $table_head[$k][$c][]=BaseServices::getWarehouseCode($v['transit_warehouse']);
                $table_head[$k][$c][]=$v['buyer'];
                $table_head[$k][$c][]=$v['created_at'];
                $table_head[$k][$c][]=$vb['sku'];
                $table_head[$k][$c][]=$vb['name'];
                $table_head[$k][$c][]=$vb['price'];
                $table_head[$k][$c][]=$vb['price'];
                $table_head[$k][$c][]=$vb['ctq'];
                $table_head[$k][$c][]=round($vb['ctq']*$vb['price'],2);
                $table_head[$k][$c][]=$v['supplier_name'];
                $table_head[$k][$c][]=!empty($v['pay_status'])?strip_tags(PurchaseOrderServices::getPayStatus($v['pay_status'])):'';
                $table_head[$k][$c][]=!empty($v['purchas_status'])?strip_tags(PurchaseOrderServices::getPurchaseStatus($v['purchas_status'])):'';
                $table_head[$k][$c][]=$v['audit_time'];
                $findone=PurchaseOrderOrders::findOne(['pur_number'=>$v['pur_number']]);
                $table_head[$k][$c][] = !empty($findone) ? $findone->order_number : '';

                $results = WarehouseResults::getResults($vb['pur_number'],$vb['sku'],'instock_user,instock_date,arrival_quantity');
                $table_head[$k][$c][]=!empty($results->arrival_quantity)?$results->arrival_quantity:'0';
            }
        }

        theCsv::export([
            'header' =>$table,
            'data' => Vhelper::ThereArrayTwo($table_head),
        ]);

    }
    /**
     * PHPExcel导出
     * @throws \PHPExcel_Exception
     * @throws \PHPExcel_Reader_Exception
     * @throws \PHPExcel_Writer_Exception
     */
    public function actionExportCsv()
    {
        set_time_limit(0);
        ini_set('memory_limit', '1024M');
        $id = Yii::$app->request->get('ids');
        $id = strpos($id,',')?explode(',',$id):$id;
        if (!empty($id))
            $model = PurchaseOrder::find()->joinWith(['purchaseOrderItems','orderShip'])->where(['in','pur_purchase_order.id',$id])->asArray()->all();
        else
        {
            $searchData = \Yii::$app->session->get('PurchaseOrderSearchData');
            $purchaseOrderSearch = new PurchaseOrderSearch();
            $query = $purchaseOrderSearch->search3($searchData, true);
            $model = $query->joinWith(['purchaseOrderItems','orderShip'])->asArray()->all();
        }
        $objectPHPExcel = new \PHPExcel();
        $objectPHPExcel->setActiveSheetIndex(0);
        $n = 0;
        //报表头的输出
        $objectPHPExcel->getActiveSheet()->mergeCells('A1:T1'); //合并单元格
        $objectPHPExcel->getActiveSheet()->mergeCells('A1:A2');
        $objectPHPExcel->getActiveSheet()->setCellValue('A1','采购单表');  //设置表标题
        $objectPHPExcel->setActiveSheetIndex(0)->getStyle('A1')->getFont()->setSize(24); //设置字体大小
        $objectPHPExcel->setActiveSheetIndex(0)->getStyle('A1')
            ->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

        //表格头的输出  采购需求建立时间、财务审核时间、财务付款时间、销售备注
        $cell_value = ['序号','图片','采购单号','采购仓库','采购员','采购日期','SKU','货品名称','采购单价','采购数量','实际应付货款','供应商名称','付款状态','采购状态','审核时间','订单号','物流单号','入库数量','优惠金额','备注','入库单号','良品上架数量','入库时间','订单状态'];
        foreach ($cell_value as $k => $v) {
            $objectPHPExcel->setActiveSheetIndex(0)->setCellValue(chr($k+65) . '3',$v);
        }
        //设置表头居中
        $objectPHPExcel->getActiveSheet()->getStyle('A3:T3')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        //设置数据水平靠左和垂直居中
        $objectPHPExcel->getDefaultStyle()->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
        $objectPHPExcel->getDefaultStyle()->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);

        foreach ( $model as $v )
        {
            foreach ($v['purchaseOrderItems'] as $c=>$vb)
            {
                //明细的输出
                $objectPHPExcel->getActiveSheet()->setCellValue('A'.($n+4) ,$n+1);
                $imgUrl = ProductImgDownload::find()->where(['sku'=>$vb['sku'],'status'=>1])->one();
                $url = !empty($imgUrl) ? $imgUrl->image_url : Vhelper::downloadImg($vb['sku'],$vb['product_img']);
                if(!file_exists($url)){
                    $url = Vhelper::downloadImg($vb['sku'],$vb['product_img']);
                }
                if($url ){
                    $img=new \PHPExcel_Worksheet_Drawing();
                    $img->setPath($url);//写入图片路径
                    $img->setHeight(50);//写入图片高度
                    $img->setWidth(100);//写入图片宽度
                    $img->setOffsetX(2);//写入图片在指定格中的X坐标值
                    $img->setOffsetY(2);//写入图片在指定格中的Y坐标值
                    $img->setRotation(1);//设置旋转角度
                    $img->getShadow()->setDirection(50);//
                    $img->setCoordinates('B'.($n+4));//设置图片所在表格位置
                    $objectPHPExcel->getActiveSheet()->getRowDimension(($n+4))->setRowHeight(80);
                    $img->setWorksheet($objectPHPExcel->getActiveSheet());//把图片写到当前的表格中
                }
                $objectPHPExcel->getActiveSheet()->setCellValue('C'.($n+4) ,$v['pur_number']);
                $objectPHPExcel->getActiveSheet()->setCellValue('D'.($n+4) ,BaseServices::getWarehouseCode($v['warehouse_code']));
                $objectPHPExcel->getActiveSheet()->setCellValue('E'.($n+4) ,$v['buyer']);
                $objectPHPExcel->getActiveSheet()->setCellValue('F'.($n+4) ,$v['created_at']);
                $objectPHPExcel->getActiveSheet()->setCellValue('G'.($n+4) ,$vb['sku']);
                $objectPHPExcel->getActiveSheet()->setCellValue('H'.($n+4) ,$vb['name']);
                $objectPHPExcel->getActiveSheet()->setCellValue('I'.($n+4) ,$vb['price']);
                $objectPHPExcel->getActiveSheet()->setCellValue('J'.($n+4) ,$vb['ctq']);
                $objectPHPExcel->getActiveSheet()->setCellValue('K'.($n+4) ,round($vb['ctq']*$vb['price'],2));
                $objectPHPExcel->getActiveSheet()->setCellValue('L'.($n+4) ,$v['supplier_name']);
                $objectPHPExcel->getActiveSheet()->setCellValue('M'.($n+4) ,!empty($v['pay_status'])?strip_tags(PurchaseOrderServices::getPayStatus($v['pay_status'])):'');
                $objectPHPExcel->getActiveSheet()->setCellValue('N'.($n+4) ,!empty($v['purchas_status'])?strip_tags(PurchaseOrderServices::getPurchaseStatus($v['purchas_status'])):'');
                $objectPHPExcel->getActiveSheet()->setCellValue('O'.($n+4) ,$v['audit_time']);

                $findone=PurchaseOrderOrders::findOne(['pur_number'=>$v['pur_number']]);
                $objectPHPExcel->getActiveSheet()->setCellValue('P'.($n+4) ,!empty($findone) ? $findone->order_number : '');
                $objectPHPExcel->getActiveSheet()->setCellValue('Q'.($n+4) ,!empty($v['orderShip']) ? $v['orderShip']['express_no'] : '');

                $results = WarehouseResults::getResults($vb['pur_number'],$vb['sku'],'instock_qty_count');
                $objectPHPExcel->getActiveSheet()->setCellValue('R'.($n+4) ,!empty($results->instock_qty_count)?$results->instock_qty_count:'0');
                $discount_info = PurchaseOrderPayType::getDiscountPrice($v['pur_number']);//优惠金额
                $objectPHPExcel->getActiveSheet()->setCellValue('S'.($n+4) ,!empty($discount_info['discount'])?$discount_info['discount']:'');
                $order_note_info = PurchaseNote::getNote($v['pur_number']);
                $objectPHPExcel->getActiveSheet()->setCellValue('T'.($n+4) ,!empty($order_note_info)?$order_note_info:'');


                //获取入库信息：入库单号(receipt_number)、良品上架数量(instock_qty_count)、入库时间(instock_date)、订单状态(purchas_status)
                $instock_info = WarehouseResults::getInstockInfo($vb['pur_number'],$vb['sku']);
                $objectPHPExcel->getActiveSheet()->setCellValue('U'.($n+4) ,$instock_info['receipt_number']);
                $objectPHPExcel->getActiveSheet()->setCellValue('V'.($n+4) ,$instock_info['instock_qty_count']);
                $objectPHPExcel->getActiveSheet()->setCellValue('W'.($n+4) ,$instock_info['instock_date']);
                $objectPHPExcel->getActiveSheet()->setCellValue('X'.($n+4) ,$instock_info['purchas_status']);


                $n = $n +1;
            }
        }

        for ($i = 65; $i<83; $i++) {
            $objectPHPExcel->getActiveSheet()->getColumnDimension(chr($i))->setWidth(15);
            $objectPHPExcel->getActiveSheet()->getStyle( chr($i) . "3")->getFont()->setBold(true);
        }
        $objectPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(10);
        $objectPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(10);

        //设置样式
        $objectPHPExcel->getActiveSheet()->getStyle('B2:H'.($n+4))->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
        ob_end_clean();
        ob_start();
        header("Content-type:application/vnd.ms-excel;charset=UTF-8");
        header('Content-Type : application/vnd.ms-excel');
        header('Content-Disposition:attachment;filename="'.'采购单计划表-'.date("Y年m月j日").'.xls"');
        $objWriter= \PHPExcel_IOFactory::createWriter($objectPHPExcel,'Excel5');
        $objWriter->save('php://output');
        die;
    }
    /**
     * 请款页面修改运费
     * @return string|\yii\web\Response
     */
    public function actionChangeFreight(){
        //获取修改内容
        if(Yii::$app->request->isAjax && Yii::$app->request->isPost){
            $form = Yii::$app->request->getBodyParams();
            $model = PurchaseOrderShip::findOne(['pur_number'=>$form['pur_number']]);
            $form['freight'] = !empty($model) ? $model->freight : '';
            //添加运费历史并对采购单运费数据进行验证
            $save = PurchaseFreightHistory::SaveOne($form);
            if($save['status'] == 'success'){
                //运费历史添加成功后修改采购单运费
                $model->freight = $form['new_freight'];

                //表修改日志-更新
                $change_content = TablesChangeLog::updateCompare($model->attributes, $model->oldAttributes);
                $change_data = [
                    'table_name' => 'pur_purchase_order_ship', //变动的表名称
                    'change_type' => '2', //变动类型(1insert，2update，3delete)
                    'change_content' => $change_content, //变更内容
                ];
                TablesChangeLog::addLog($change_data);
                $model->save(false);
            }
            Yii::$app->getSession()->setFlash($save['status'],$save['message']);
            return json_encode($save);
        }
    }



    /**
     * 取消部分到货等待剩余
     * @return \yii\web\Response
     */
    public function actionCancel()
    {
        $id =  Yii::$app->request->get('id');
        $res = PurchaseOrder::cancelPartialArrival($id);
        if (empty($res)) {
            Yii::$app->getSession()->setFlash('error','取消部分到货等待剩余失败',true);
            return $this->redirect(Yii::$app->request->referrer);
        } else {
            Yii::$app->getSession()->setFlash('success','取消部分到货等待剩余成功',true);
            return $this->redirect(Yii::$app->request->referrer);
        }
    }

    // 申请报损
    public function actionApplyBreakage()
    {
        $request = Yii::$app->request;
        if($request->isPost) {
            $post = $request->post();
            $data = $request->post('breakage');
            $record = [];
            foreach($data as $v) {
                if($v['num'] > 0) {
                    $v['apply_notice'] = $post['apply_notice'];
                    $record[] = $v;
                }
            }
            if(empty($record)) {
                Yii::$app->getSession()->setFlash('error','你没有输入报损数量，不能申请',true);
                return $this->redirect(Yii::$app->request->referrer);
            }
            $transaction = Yii::$app->db->beginTransaction();
            try {
                PurchaseOrderBreakage::saveOnes($record);

                PurchaseLog::addLog([
                    'pur_number' => $record[0]['pur_number'],
                    'note' => '申请商品报损'
                ]);
                $transaction->commit();
                Yii::$app->getSession()->setFlash('success','恭喜你，申请成功');
                return $this->redirect(Yii::$app->request->referrer);
            } catch (Exception $e) {
                $transaction->rollBack();
                Yii::$app->getSession()->setFlash('error','对不起，申请失败');
                return $this->redirect(Yii::$app->request->referrer);
            }
        } else {
            $model = new PurchaseOrder();
            $orderInfo = $model->getOrderDetail($request->get('pur_number'));
            return $this->renderAjax('apply-breakage', ['orderInfo' => $orderInfo]);
        }
    }

    // 收款驳回处理
    public function actionRefundHandler()
    {
        $request = Yii::$app->request;
        if($request->isPost) {
            $post = $request->post('RefundHandler');
            $data = Vhelper::changeData($post);
            $transaction = Yii::$app->db->beginTransaction();
            try {
                foreach($data as $v) {
                    $model = PurchaseOrderReceipt::findOne($v['id']);
                    $model->pay_status = 1;
                    $model->pay_price = $v['pay_price'];
                    $model->review_notice = $v['review_notice'];
                    $model->application_time = date('Y-m-d H:i:s', time());

                    //表修改日志-更新
                    $change_content = TablesChangeLog::updateCompare($model->attributes, $model->oldAttributes);
                    $change_data = [
                        'table_name' => 'pur_purchase_order_receipt', //变动的表名称
                        'change_type' => '2', //变动类型(1insert，2update，3delete)
                        'change_content' => $change_content, //变更内容
                    ];
                    TablesChangeLog::addLog($change_data);
                    $model->save(false);
                }
                $order = PurchaseOrder::find()->where(['pur_number' => $data[0]['pur_number']])->one();
                $order->refund_status = 1;

                //表修改日志-更新
                $change_content = TablesChangeLog::updateCompare($order->attributes, $order->oldAttributes);
                $change_data = [
                    'table_name' => 'pur_purchase_order', //变动的表名称
                    'change_type' => '2', //变动类型(1insert，2update，3delete)
                    'change_content' => $change_content, //变更内容
                ];
                TablesChangeLog::addLog($change_data);
                $order->save(false);
                $transaction->commit();
                Yii::$app->getSession()->setFlash('success','恭喜你，处理成功，等待财务收款');
                return $this->redirect(Yii::$app->request->referrer);
            } catch(\Exception $e) {
                $transaction->rollBack();
                Yii::$app->getSession()->setFlash('error','对不起，处理失败');
                return $this->redirect(Yii::$app->request->referrer);
            }
        } else {
            $pur_number = $request->get('pur_number');
            $requisition_number = $request->get('requisition_number');
            $models = PurchaseOrderReceipt::find()->where(['pur_number' => $pur_number,'requisition_number'=>$requisition_number])->all();
            if(empty($models)) {
                return '该订单没有或者没有可编辑的记录';
            }
            return $this->renderAjax('refund-handler', ['models' => $models]);
        }
    }


    // 修改订单的基本信息
    public function actionUpdateOrder()
    {
        $request = Yii::$app->request;
        $pur_number = $request->get('pur_number');
        if($request->isPost) {
            $post = $request->post();
            $result = false;
            $t = Yii::$app->db->beginTransaction();
            $orderModel = PurchaseOrder::find()->where(['pur_number' => $post['pur_number']])->one();
            try {
                $model = PurchaseOrderPayType::find()->where(['pur_number' => $post['pur_number']])->one();
                $new = $post['new'];
                if($model) {
                    $old = [
                        'freight' => $model->freight,
                        'discount' => $model->discount,
                        'purchase_account' => $model->purchase_acccount,
                        'platform_order_number' => $model->platform_order_number,
                        'note' => $model->note
                    ];
                    $logData = ['old' => $old, 'new' => $new];
                } else {
                    $logData = ['new' => $new];
                }
                $a = OperatLog::AddLog([
                    'type' => 30,
                    'content' => json_encode($logData),
                    'module' => '国内采购单：修改订单信息',
                    'pur_number' => $post['pur_number'],
                    'pid' => 1
                ]);
                $orderModel->shipfees_audit_status = 0;

                //表修改日志-更新
                $change_content = TablesChangeLog::updateCompare($orderModel->attributes, $orderModel->oldAttributes);
                $change_data = [
                    'table_name' => 'pur_purchase_order', //变动的表名称
                    'change_type' => '2', //变动类型(1insert，2update，3delete)
                    'change_content' => $change_content, //变更内容
                ];
                TablesChangeLog::addLog($change_data);

                $b = $orderModel->save(false);
                if($a && $b)
                    $result = true;
            } catch (\Exception $e) {
                $result = false;
            }
            if($result) {
                $t->commit();
                Yii::$app->getSession()->setFlash('success','恭喜你，修改成功，请等待审核');
                return $this->redirect(Yii::$app->request->referrer);
            } else {
                $t->rollBack();
                Yii::$app->getSession()->setFlash('error','对不起，修改失败');
                return $this->redirect(Yii::$app->request->referrer);
            }
        } else {
            $shipfees_audit_status = PurchaseOrder::find()->select('shipfees_audit_status')->where(['pur_number' => $pur_number])->scalar();
            if($shipfees_audit_status == 0) {
                exit('这个订单已经修改过信息了，请等待审核结果');
            }
            $model = PurchaseOrderPayType::find()->where(['pur_number' => $pur_number])->one();
            $paylist = PurchaseOrderPay::find()->where(['pur_number' => $pur_number])->All();
            return $this->renderAjax('update-order', ['model' => $model, 'paylist' => $paylist, 'pur_number' => $pur_number]);
        }
    }

    // 审核修改运费
    public function actionAuditShip()
    {
        $request = Yii::$app->request;
        if($request->isPost) {
            $post = $request->post();
            $model = OperatLog::findOne($post['id']);
            if(empty($model)) {
                return json_encode([
                    'error' => 1,
                    'message' => '没有找到数据源'
                ]);
            }
            $orderModel = PurchaseOrder::find()->where(['pur_number' => $model->pur_number])->one();
            $orderPayModel = PurchaseOrderPayType::find()->where(['pur_number' => $model->pur_number])->one();
            if(empty($orderModel)) {
                return json_encode([
                    'error' => 1,
                    'message' => '订单数据不存在'
                ]);
            }
            $t = Yii::$app->db->beginTransaction();
            try {
                $content = $model->content;
                $content = $content ? json_decode($content, 1) : '';
                if(!isset($content['new'])) {
                    return json_encode([
                        'error' => 1,
                        'message' => '操作日志中，没有发现目标数据'
                    ]);
                }
                $new = $content['new'];

                if($post['status'] == 1) { // 通过

                    $h = 'update';
                    if(empty($orderPayModel)) {
                        $orderPayModel = new PurchaseOrderPayType();
                        $orderPayModel->pur_number = $model->pur_number;
                        $h = 'insert';
                    }
                    if(isset($new['freight'])) {
                        $orderPayModel->freight = $new['freight'];
                        $orderPayModel->is_update_freight = 1; // 修改了运费，需要同步到K3
                    }
                    if(isset($new['discount'])) {
                        $orderPayModel->discount = $new['discount'];
                        $orderPayModel->is_update_discount = 1; // 修改了运费，需要同步到K3
                    }
                    if(isset($new['purchase_acccount'])) {
                        $orderPayModel->purchase_acccount = $new['purchase_acccount'];
                    }
                    if(isset($new['platform_order_number'])) {
                        $orderPayModel->platform_order_number = $new['platform_order_number'];
                    }
                    if(isset($new['note'])) {
                        $orderPayModel->note = $new['note'];
                    }
                    if($h == 'insert') {
                        $change_content = "insert:新增id值为 {$orderPayModel->id} 的记录";
                        $change_type = '1';
                    } else {
                        $change_content = TablesChangeLog::updateCompare($orderPayModel->attributes, $orderPayModel->oldAttributes);
                        $change_type = '2';
                    }
                    $change_data = [
                        'table_name' => 'pur_purchase_order_pay_type', // 变动的表名称
                        'change_type' => $change_type, // 变动类型(1insert，2update，3delete)
                        'change_content' => $change_content, // 变更内容
                    ];
                    TablesChangeLog::addLog($change_data);
                    $a = $orderPayModel->save();

                    $orderModel->shipfees_audit_status = 1;
                    $b = $orderModel->save(false);

                    $model->status = 1;

                    //表修改日志-更新
                    $change_content = TablesChangeLog::updateCompare($model->attributes, $model->oldAttributes);
                    $change_data = [
                        'table_name' => 'pur_opera_log', //变动的表名称
                        'change_type' => '2', //变动类型(1insert，2update，3delete)
                        'change_content' => $change_content, //变更内容
                    ];
                    TablesChangeLog::addLog($change_data);
                    $c = $model->save(false);

                    PurchaseLog::addLog(['pur_number' => $model->pur_number, 'note' => '经理审核订单信息修改申请']);

                    if($a && $b && $c) {
                        $is_submit = true;
                    } else {
                        $is_submit = false;
                    }

                } else { // 不通过

                    $orderModel->shipfees_audit_status = 2;

                    //表修改日志-更新
                    $change_content = TablesChangeLog::updateCompare($orderModel->attributes, $orderModel->oldAttributes);
                    $change_data = [
                        'table_name' => 'pur_purchase_order', //变动的表名称
                        'change_type' => '2', //变动类型(1insert，2update，3delete)
                        'change_content' => $change_content, //变更内容
                    ];
                    TablesChangeLog::addLog($change_data);
                    $b = $orderModel->save(false);

                    $model->status = 2;

                    //表修改日志-更新
                    $change_content = TablesChangeLog::updateCompare($model->attributes, $model->oldAttributes);
                    $change_data = [
                        'table_name' => 'pur_operat_log', //变动的表名称
                        'change_type' => '2', //变动类型(1insert，2update，3delete)
                        'change_content' => $change_content, //变更内容
                    ];
                    TablesChangeLog::addLog($change_data);
                    $c = $model->save(false);

                    if($b && $c) {
                        $is_submit = true;
                    } else {
                        $is_submit = false;
                    }
                }



                if($is_submit) {
                    $t->commit();
                    return json_encode([
                        'error' => 0,
                        'message' => '恭喜你，操作成功'
                    ]);
                } else {
                    $t->rollBack();
                    return json_encode([
                        'error' => 1,
                        'message' => '对不起，操作失败'
                    ]);
                }




            } catch(\Exception $e) {
                $t->rollBack();

                return json_encode([
                    'error' => 1,
                    'message' => '对不起，操作失败'
                ]);
            }




        } else {
            $searchModel = new \app\models\OperatLogSearch();
            $args = Yii::$app->request->queryParams;
            $args['type'] = 30;
            $args['pid'] = 1;
            $args['status'] = 0;
            $dataProvider = $searchModel->search1($args);
            return $this->render('audit-ship', [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
            ]);
        }

    }



    /***************合同流程相关的 开始********************/
    // 合同列表页
    public function actionCompactList()
    {
        $args = Yii::$app->request->queryParams;
        $searchModel = new PurchaseCompactSearch();
        $dataProvider = $searchModel->search($args, 1);
        return $this->render('compact-list', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }


    // 合同请款执行
    public function executeCompactPayment($data)
    {
        $cpn = $data['compact_number'];
        $pos = PurchaseCompact::getPurNumbers($cpn);
        // 获取申请金额
        $pay_ratio = isset($data['pay_ratio']) ? $data['pay_ratio'] : '';
        if(!$pay_ratio) {
            Yii::$app->getSession()->setFlash('error', '申请失败，申请金额有误');
            return $this->redirect(Yii::$app->request->referrer);
        }
        $ratio_price = explode('/', $pay_ratio);
        $pay_model = new PurchaseOrderPay();
        $transaction = Yii::$app->db->beginTransaction();
        $order = PurchaseOrder::find()->where(['in', 'pur_number', $pos])->one(); // 只取合同下的一个单作为数据源
        try {
            $models = PurchaseOrder::find()->where(['in', 'pur_number', $pos])->all();
            foreach($models as $model) {
                $model->pay_status = 2; // 待财务审批（不同于以往的待提交，合同跳过这些流程）

                //表修改日志-更新
                $change_content = TablesChangeLog::updateCompare($model->attributes, $model->oldAttributes);
                $change_data = [
                    'table_name' => 'pur_purchase_order', //变动的表名称
                    'change_type' => '2', //变动类型(1insert，2update，3delete)
                    'change_content' => $change_content, //变更内容
                ];
                TablesChangeLog::addLog($change_data);

                $model->update(false);
            }
            $pay_model->pur_number         = $cpn; // 在原有的订单号字段上，写上合同号
            $pay_model->requisition_number = CommonServices::getNumber('PP');
            $pay_model->pay_status         = 2;                          // 待财务审批
            $pay_model->pay_ratio          = $ratio_price[0];            // 请款比例
            $pay_model->pay_price          = $ratio_price[1];            // 请款金额
            $pay_model->js_ratio           = $data['js_ratio'];          // 结算比例（拉取合同的）
            $pay_model->pay_name           = '合同单采购费用';
            $pay_model->settlement_method  = $order->account_type;       // 结算方式
            $pay_model->supplier_code      = $order->supplier_code;      // 供应商编码
            $pay_model->currency           = $order->currency_code;      // 币种
            $pay_model->pay_type           = $order->pay_type;           // 支付方式
            $pay_model->create_notice      = $data['create_notice'];     // 申请备注
            $pay_model->source             = 1;                          // 标记请款单为合同请款
            $pay_model->save(false);

            //表修改日志-新增
            $change_content = "insert:新增id值为{$pay_model->id}的记录";
            $change_data = [
                'table_name' => 'pur_purchase_order_pay', //变动的表名称
                'change_type' => '1', //变动类型(1insert，2update，3delete)
                'change_content' => $change_content, //变更内容
            ];
            TablesChangeLog::addLog($change_data);

            // 写采购日志
            PurchaseLog::addLog([
                'pur_number' => $cpn,
                'note' => "申请付款，付款比例为{$ratio_price[0]}，付款金额为{$ratio_price[1]}"
            ]);

            $transaction->commit();
            Yii::$app->getSession()->setFlash('success','恭喜您，请款信息已经保存成功，请填写付款申请书');
            return $this->redirect(["write-payment?compact_number={$cpn}&pid={$pay_model->id}"]);

        } catch(Exception $e) {
            $transaction->rollBack();
            Yii::$app->getSession()->setFlash('success','对不起，申请付款失败');
            return $this->redirect(['index']);
        }
    }

    // update freight and discount
    public function actionUpdateFreightDiscount()
    {
        $request = Yii::$app->request;
        if($request->isPost) {
            $data = $request->post();

            $cpn = $data['cpn'];
            $opn = $data['opn'];
            $model_compact = PurchaseCompact::find()->where(['compact_number' => $cpn])->one();
            $model_order = PurchaseOrder::find()->where(['pur_number' => $opn])->one();

            if(empty($model_compact) || empty($model_order)) {
                exit('合同信息或订单信息不存在');
            }

            $e = 0;
            $content = [
                'old' => [],
                'new' => []
            ];

            if(isset($data['new_freight'])) {
                $of = round($data['old_freight'], 2);
                $nf = round($data['new_freight'], 2);

                if(bccomp($of, $nf, 2) == 0) {
                    $e += 1;
                }
                $content['old']['freight'] = $of;
                $content['new']['freight'] = $nf;
            }
            if(isset($data['new_discount'])) {

                $od = round($data['old_discount'], 2);
                $nd = round($data['new_discount'], 2);

                if(bccomp($od, $nd, 2) == 0) {
                    $e += 1;
                }
                $content['old']['discount'] = $od;
                $content['new']['discount'] = $nd;
            }

            if($e == 2) {
                exit('什么也不改？');
            }

            $t = Yii::$app->db->beginTransaction();
            try {

                $a = OperatLog::AddLog([
                    'type' => 30,
                    'content' => json_encode($content),
                    'module' => '国内采购单：修改订单信息',
                    'pur_number' => $opn,
                    'pid' => 1 // 国内
                ]);

                $model_order->shipfees_audit_status = 0;
                $model_compact->payment_status = 4; // freeze

                //表修改日志-更新
                $change_content = TablesChangeLog::updateCompare($model_order->attributes, $model_order->oldAttributes);
                $change_data = [
                    'table_name' => 'pur_purchase_order', //变动的表名称
                    'change_type' => '2', //变动类型(1insert，2update，3delete)
                    'change_content' => $change_content, //变更内容
                ];
                TablesChangeLog::addLog($change_data);
                $b = $model_order->save(false);

                //表修改日志-更新
                $change_content = TablesChangeLog::updateCompare($model_compact->attributes, $model_compact->oldAttributes);
                $change_data = [
                    'table_name' => 'pur_purchase_compact', //变动的表名称
                    'change_type' => '2', //变动类型(1insert，2update，3delete)
                    'change_content' => $change_content, //变更内容
                ];
                TablesChangeLog::addLog($change_data);
                $c = $model_compact->save(false);
                if($a && $b && $c) {
                    $t->commit();
                    Yii::$app->getSession()->setFlash('success','恭喜你，已提交申请，等待审核');
                    return $this->redirect(['index']);
                } else {
                    $t->commit();
                    Yii::$app->getSession()->setFlash('error','对不起，提交失败');
                    return $this->redirect(['index']);
                }
            } catch(\Exception $e) {
                $t->rollBack();
                Yii::$app->getSession()->setFlash('error','对不起，提交失败');
                return $this->redirect(['index']);
            }
        } else {
            $cpn = $request->get('cpn');
            $opn = $request->get('opn');

            $order = PurchaseOrder::find()->where(['pur_number' => $opn])->one();
            if($order->shipfees_audit_status == 0) {
                return '你的订单已经修改过了，还没有审核';
            }
            $moneys = PurchaseCompact::find()
                ->select(['settlement_ratio', 'dj_money', 'wk_money', 'wk_total_money'])
                ->where(['compact_number' => $cpn])
                ->asArray()
                ->one();
            $paynum = PurchaseOrderPay::find()->where(['pur_number' => $cpn])->count();
            $s_ratio = $moneys['settlement_ratio'];
            $s_ratio = explode('+', $s_ratio);
            $s_count = count($s_ratio); // payment num
            $payDiscount = true;
            $payFreight = true;
            if($s_count == 1) {
                if($paynum > 0) {
                    return '你的合同选择的是全额一次性付款，并且你已经申请过付款了，为了不影响请款金额，运费和优惠额都不能修改';
                }
            } elseif($s_count == 2) {
                if($paynum == 1) {
                    $payDiscount = false;
                } else if($paynum == 2) {
                    $payFreight = false;
                }
            } elseif($s_count == 3) {
                if($paynum == 1) {
                    $payDiscount = false;
                } elseif($paynum == 2) {
                    $payDiscount = false;
                } elseif($paynum == 3) {
                    $payDiscount = false;
                    $payFreight = false;
                }
            }
            $freight = PurchaseOrderPayType::find()->where(['pur_number' => $opn])->one();
            return $this->renderAjax('update-freight-discount', [
                'freight' => $freight,
                'payDiscount' => $payDiscount,
                'payFreight' => $payFreight,
                'cpn' => $cpn,
                'opn' => $opn
            ]);
        }
    }
    /***************合同流程相关的 结束********************/



    /**
     * 获取退款sku和数量明细
     */
    public  function  actionGetSkuDetail()
    {
        //获取申请单号
        $requisition_number = Yii::$app->request->post('requisition_number');

        //退款明细
        $data = PurchaseOrderReceipt::find()
            ->alias('a')
            ->select(['b.sku','b.refund_qty'])
            ->leftJoin('pur_purchase_order_refund_quantity b', 'b.requisition_number=a.requisition_number')
            ->where(['b.requisition_number' => $requisition_number])
            ->asArray()
            ->all();

        echo Json::encode($data);
    }
    /**
     * 导出付款合同
     */
    public function actionPaymentContract()
    {
        set_time_limit(0);
        ini_set('memory_limit', '1024M');

        $ids = yii::$app->request->get('ids');
        $ids_arr = explode(',', $ids);
        $order_info = PurchaseOrder::find()->where(['in', 'id', $ids_arr])->asArray()->all();
        $supplier_code = array_column($order_info,'supplier_code');
        $res = array_unique($supplier_code);
        if (count($res) != 1) {
            Yii::$app->getSession()->setFlash('error','不是同一供应商不能够导出');
            return $this->redirect(['index']);
        }

        $pur_number = array_column($order_info,'pur_number');
        $items_info = PurchaseOrderItems::find()->where(['in', 'pur_number', $pur_number])->asArray()->all();

        $objectPHPExcel = new \PHPExcel();
        $objectPHPExcel->setActiveSheetIndex(0);
        $n = 0;
        # 报表头的输出
        //合并单元格
        $objectPHPExcel->getActiveSheet()->mergeCells('A1:F1'); 
        //设置标题
        $objectPHPExcel->getActiveSheet()->setCellValue('A1', '采购订单');
        //设置字体大小
        $objectPHPExcel->setActiveSheetIndex(0)->getStyle('A1')->getFont()->setSize(24);
        //设置表头居中
        $objectPHPExcel->setActiveSheetIndex(0)->getStyle('A1')
            ->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);


        //合并单元格he设置标题
        $objectPHPExcel->getActiveSheet()->mergeCells('A2:C2'); 
        $objectPHPExcel->getActiveSheet()->setCellValue('A2', '甲方');
        //合并单元格he设置标题
        $objectPHPExcel->getActiveSheet()->setCellValue('A3' ,'公 司 名');
        $objectPHPExcel->getActiveSheet()->mergeCells('B3:C3'); 
        $objectPHPExcel->getActiveSheet()->setCellValue('B3', '深圳市易佰网络科技有限公司');

        $objectPHPExcel->getActiveSheet()->setCellValue('A4' ,'地  址');
        $objectPHPExcel->getActiveSheet()->mergeCells('B4:C4'); 
        $objectPHPExcel->getActiveSheet()->setCellValue('B4', '深圳市龙华新区清湖社区清祥路清湖科技园二区B栋701');

        $objectPHPExcel->getActiveSheet()->setCellValue('A5' ,'联 系 人');
        $objectPHPExcel->getActiveSheet()->mergeCells('B5:C5'); 
        $objectPHPExcel->getActiveSheet()->setCellValue('B5', '***');

        $objectPHPExcel->getActiveSheet()->setCellValue('A6' ,'电  话');
        $objectPHPExcel->getActiveSheet()->mergeCells('B6:C6'); 
        $objectPHPExcel->getActiveSheet()->setCellValue('B6', '***');





        //合并单元格he设置标题
        $objectPHPExcel->getActiveSheet()->mergeCells('D2:F2'); 
        $objectPHPExcel->getActiveSheet()->setCellValue('D2', '乙方');
        //合并单元格he设置标题
        $objectPHPExcel->getActiveSheet()->setCellValue('D3' ,'公 司 名');
        $objectPHPExcel->getActiveSheet()->mergeCells('E3:F3'); 
        $objectPHPExcel->getActiveSheet()->setCellValue('E3', '深圳市易佰网络科技有限公司');

        $objectPHPExcel->getActiveSheet()->setCellValue('D4' ,'地  址');
        $objectPHPExcel->getActiveSheet()->mergeCells('E4:F4'); 
        $objectPHPExcel->getActiveSheet()->setCellValue('E4', '深圳市宝安区石岩街道浪心社区塘头大道北恒超工业园厂房B栋二栋东');


        $objectPHPExcel->getActiveSheet()->setCellValue('D5' ,'联 系 人');
        $objectPHPExcel->getActiveSheet()->mergeCells('E5:F5'); 
        $objectPHPExcel->getActiveSheet()->setCellValue('E5', '王碧清');
        
        $objectPHPExcel->getActiveSheet()->setCellValue('D6' ,'电   话');
        $objectPHPExcel->getActiveSheet()->mergeCells('E6:F6'); 
        $objectPHPExcel->getActiveSheet()->setCellValue('E6', '13560731953');
        


        //表格头的输出  采购需求建立时间、财务审核时间、财务付款时间、销售备注
        $cell_value = ['采购单号','SKU','货品名称','采购单价','采购数量','金额'];
        foreach ($cell_value as $k => $v) {
            $objectPHPExcel->setActiveSheetIndex(0)->setCellValue(chr($k+65) . '7',$v);
        }
        //设置哪行数据居中
        $objectPHPExcel->getActiveSheet()->getStyle('A2:T2')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objectPHPExcel->getActiveSheet()->getStyle('A3:T3')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objectPHPExcel->getActiveSheet()->getStyle('A7:T7')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        //设置数据水平靠左和垂直居中
        $objectPHPExcel->getDefaultStyle()->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
        $objectPHPExcel->getDefaultStyle()->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);

        $total_piece = 0;
        foreach ($items_info as $key => $v) {

            if ($key != 0) {
                if ($v['pur_number'] == $items_info[$key-1]['pur_number']) {
                    $objectPHPExcel->getActiveSheet()->mergeCells('A'.($n+7) . ':' . 'A' . ($n+8));
                }
            }
            

            $objectPHPExcel->getActiveSheet()->setCellValue('A'.($n+8) ,$v['pur_number']);
            $objectPHPExcel->getActiveSheet()->setCellValue('B'.($n+8) ,$v['sku']);
            $objectPHPExcel->getActiveSheet()->setCellValue('C'.($n+8) ,$v['name']);
            $objectPHPExcel->getActiveSheet()->setCellValue('D'.($n+8) ,$v['price']);
            $objectPHPExcel->getActiveSheet()->setCellValue('E'.($n+8) ,$v['ctq']);
            $objectPHPExcel->getActiveSheet()->setCellValue('F'.($n+8) ,$v['ctq']*$v['price']);

            $n = $n +1;
            $total_piece += ($v['ctq']*$v['price']);
        }

        $bold = $n;

        //--------------------------------------------------------------------
        $objectPHPExcel->getActiveSheet()->setCellValue('A' . ($n+8) ,'合计');
        $objectPHPExcel->getActiveSheet()->setCellValue('F' . ($n+8) ,$total_piece);

        $objectPHPExcel->getActiveSheet()->setCellValue('A' . ($n+9) ,'总金额');
        $objectPHPExcel->getActiveSheet()->setCellValue('B' . ($n+9) ,$total_piece);
        $objectPHPExcel->getActiveSheet()->mergeCells('C' . ($n+9) . ':F' . ($n+9)); 
        //结算方式：SupplierServices::getSettlementMethod($ordersitmes->account_type)
        $objectPHPExcel->getActiveSheet()->setCellValue('C' . ($n+9), '付款方式为*** 以入库货物货款为准');

        $objectPHPExcel->getActiveSheet()->mergeCells('A' . ($n+10) . ':F' . ($n+10)); 
        $objectPHPExcel->getActiveSheet()->setCellValue('A' . ($n+10), '付款说明：按照双方确认后的产品与金额付**年*月*号到*月*号的订单总贷款');


        $objectPHPExcel->getActiveSheet()->setCellValue('A' . ($n+11) ,'合作要求');
        $objectPHPExcel->getActiveSheet()->mergeCells('B' . ($n+11) . ':F' . ($n+11)); 
        $objectPHPExcel->getActiveSheet()->setCellValue('B' . ($n+11), 
            "1.如果有我司工作人员索要回扣，影响正常合作，请致电我司总经理电话：【胡范金15012616166（微信）   庄俊超 13713710103（微信）】\n2 如发现我司工作员和贵司存在行贿受贿行为，会立即停止合作，将扣押公司所有货款，按实际发生行贿金额10倍计罚款，其它未尽事宜，大家友好协商解决。\n"
        );
        $objectPHPExcel->getActiveSheet()->getStyle('B' . ($n+11))->getAlignment()->setWrapText(true);
        $objectPHPExcel->getActiveSheet()->getRowDimension($n+11)->setRowHeight(100);



        $objectPHPExcel->getActiveSheet()->mergeCells('A' . ($n+12) . ':F' . ($n+12)); 
        $objectPHPExcel->getActiveSheet()->setCellValue('A' . ($n+12), '注：盖章合同影印件同样具有法律效力');
        $objectPHPExcel->getActiveSheet()->getStyle('A' . ($n+12) . ':F' . ($n+12))->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);


        $objectPHPExcel->getActiveSheet()->setCellValue('A' . ($n+13) ,'汇款信息');
        $objectPHPExcel->getActiveSheet()->mergeCells('B' . ($n+13) . ':F' . ($n+13)); 
        $objectPHPExcel->getActiveSheet()->setCellValue('B' . ($n+13), '账户名：***  账号：***  开户行：***');

        //--------------------------------------------------------------------


        $objectPHPExcel->getActiveSheet()->setCellValue('A' . ($n+14) ,'订购方签章');
        $objectPHPExcel->getActiveSheet()->mergeCells('B' . ($n+14) . ':C' . ($n+14)); 
        $objectPHPExcel->getActiveSheet()->setCellValue('B' . ($n+14), 
            "经办人签字：\n     
  
            负责人签字：\n

            单位盖章：\n

            日期：2018/06/29"
        );

        $objectPHPExcel->getActiveSheet()->setCellValue('D' . ($n+14) ,'供应商签章');
        $objectPHPExcel->getActiveSheet()->mergeCells('E' . ($n+14) . ':F' . ($n+14)); 


        $objectPHPExcel->getActiveSheet()->setCellValue('E' . ($n+14), 
            "经办人签字：\n
 
            负责人签字：\n

            单位盖章：\n

            日期："
        );
        $objectPHPExcel->getActiveSheet()->getStyle('B' . ($n+14))->getAlignment()->setWrapText(true);
        $objectPHPExcel->getActiveSheet()->getStyle('E' . ($n+14))->getAlignment()->setWrapText(true);
        $objectPHPExcel->getActiveSheet()->getStyle('A'.($n+14).':F'.($n+14))->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

        //默认高度
        //$objPHPExcel->getActiveSheet()->getDefaultRowDimension()->setRowHeight(15);
        $objectPHPExcel->getActiveSheet()->getRowDimension($n+14)->setRowHeight(170);


        for ($i = 65; $i<83; $i++) {
            //字体加粗
            $objectPHPExcel->getActiveSheet()->getStyle( chr($i) . ($bold+8))->getFont()->setBold(true);
            $objectPHPExcel->getActiveSheet()->getStyle( chr($i) . ($bold+9))->getFont()->setBold(true);
            $objectPHPExcel->getActiveSheet()->getStyle( chr($i) . ($bold+10))->getFont()->setBold(true);
            $objectPHPExcel->getActiveSheet()->getStyle( chr($i) . ($bold+11))->getFont()->setBold(true);
            $objectPHPExcel->getActiveSheet()->getStyle( chr($i) . ($bold+12))->getFont()->setBold(true);
            $objectPHPExcel->getActiveSheet()->getStyle( chr($i) . ($bold+13))->getFont()->setBold(true);

            //设置单元格宽度
            $objectPHPExcel->getActiveSheet()->getColumnDimension(chr($i))->setWidth(15);
        }
        // $objectPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(15);

        //设置样式
        //$objectPHPExcel->getActiveSheet()->getStyle('B2:E2')->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);


        ob_end_clean();
        ob_start();
        header("Content-type:application/vnd.ms-excel;charset=UTF-8");
        header('Content-Type : application/vnd.ms-excel');
        header('Content-Disposition:attachment;filename="'.'付款合同-'.date("Y年m月j日").'.xls"');
        $objWriter= \PHPExcel_IOFactory::createWriter($objectPHPExcel,'Excel5');
        $objWriter->save('php://output');
        die;
    }
}
