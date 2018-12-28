<?php
namespace app\controllers;
use app\models\AmazonOutofstockOrder;
use app\models\PurchaseOrderCancel;
use app\models\PurchaseOrderCancelSub;
use Yii;
use app\config\Vhelper;
use app\models\CancelQuantityLog;
use app\models\PlatformSummary;
use app\models\ProductSearch;
use app\models\PurchaseCancelQuantity;
use app\models\PurchaseDemand;
use app\models\PurchaseDiscount;
use app\models\PurchaseEstimatedTime;
use app\models\PurchaseOrderAccount;
use app\models\PurchaseOrderItems;
use app\models\PurchaseOrderOrders;
use app\models\PurchaseOrderPay;
use app\models\PurchaseOrderPayDetail;
use app\models\PurchaseOrderPayType;
use app\models\PurchaseOrderReceipt;
use app\models\PurchaseOrderShip;
use app\models\PurchaseOrderTaxes;
use app\models\PurchaseRefunds;
use app\models\PurchaseTemporary;
use app\models\Stock;
use app\models\SupplierQuotes;
use app\models\WarehouseResults;
use app\models\AlibabaAccount;
use app\services\BaseServices;
use app\services\CommonServices;
use app\models\PurchaseOrder;
use app\models\PurchaseOrderSearch;
use yii\helpers\Json;
use yii\web\HttpException;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use app\models\PurchaseLog;
use app\models\PurchaseNote;
use yii\web\UploadedFile;
use app\models\PurchaseOrderBreakage;
use app\models\OperatLog;
use app\models\PurchaseOrderRefundQuantity;
use app\models\PurchaseCompact;
use app\models\PurchaseCompactItems;
use app\services\PurchaseOrderServices;
use app\models\PurchaseCompactSearch;

class FbaPurchaseOrderController extends BaseController
{
    const ININ_PAY_STATUS = -1; // 分批请款时的初始化状态，依据这个值决定请款单流程
    const MAX_PAY_NUM = 50; // 批量付款最大支持数
    public static $not_pay_status = [1, 2, 4, 10]; // 不能请款的订单状态
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
     * Lists all PurchaseOrder models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new PurchaseOrderSearch();
        $params = Yii::$app->request->queryParams;
        $source = isset($params['source']) ? (int)$params['source'] : 2;
        $searchModel->source = $params['source'] = $source;
        if ($source==1) {
            $dataProvider = $searchModel->fbaCompactSearch($params);
        } else {
            $dataProvider = $searchModel->search5($params);
        }
        
        $data = [
            'source' => $source,
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ];
        return $this->render('index', $data);
    }
    /**
     * 查看产品明细
     * Displays a single PurchaseOrder model.
     * @param string $id
     * @return mixed
     */
    public function actionView($id)
    {
        $map['pur_purchase_order.pur_number']       = strpos($id,',') ? explode(',',$id):$id;
        $model= PurchaseOrder::find()->joinWith(['purchaseOrderItems','platformSummary'])->where($map)->one();
        if (!$model)
        {
            return '信息不存在';
        } else {
            $is_exist = $model->purchaseOrderItemsCtq;
            if (empty($is_exist)) return 'sku可能已全部作废';
            return $this->renderAjax('view', [
                'model' => $model,
            ]);
        }
    }
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
        return $this->renderAjax('view', [
            'model' =>$ordersitmes,
        ]);
    }
    /**
     * 撤销采购单  此单当作废
     * @return \yii\web\Response
     */
    public function actionRevokePurchaseOrder()
    {
        $ids = Yii::$app->request->get('ids');

        /*if(strpos($ids,',')){
            Yii::$app->getSession()->setFlash('error', '恭喜你,撤销失败了,一次只能撤销一个', true);
            return $this->redirect(['index']);
        }*/
        if(strpos($ids,',')){
            $ids_arr = explode(',',$ids);
            $model  = PurchaseOrder::find()->where(['in','id',$ids_arr])->andWhere(['purchas_status'=>1])->all();
        }else{
            $model  = PurchaseOrder::find()->where(['id'=>$ids,'purchas_status'=>1])->all();
        }
        $transaction=\Yii::$app->db->beginTransaction();
        try {
            if($model)
            {
                //撤销采购单
                PurchaseOrder::UpdatePurchaseStatus($ids,4);
                foreach ($model as $v)
                {
                    $demand = PurchaseDemand::find()->where(['in','pur_number',$v->pur_number])->all();
                    $number ='';
                    if($demand)
                    {
                        foreach($demand as $b)
                        {
                            $exist = AmazonOutofstockOrder::find()->where(['demand_number'=>$b->demand_number])->exists();
                            if($exist){
                                AmazonOutofstockOrder::updateAll(['demand_number'=>'','is_push'=>0,'status'=>0,'is_show'=>1],['demand_number'=>$b->demand_number]);
                                PlatformSummary::updateAll(['is_purchase'=>1,'level_audit_status'=>5,'is_push'=>0],['demand_number'=>$b->demand_number]);
                            }else{
                                PlatformSummary::updateAll(['is_purchase'=>1,'level_audit_status'=>1,'is_push'=>0],['demand_number'=>$b->demand_number]);
                            }
                            $datas =[];
                            $msg             = '在' . date('Y-m-d H:i:s') . '由' . Yii::$app->user->identity->username . '=====' . $v->pur_number . '进行了撤销';
                            $datas['type']    = 11;
                            $datas['pid']     = $ids;
                            $datas['module']  = '采购单撤销';
                            $datas['content'] = $msg;
                            Vhelper::setOperatLog($datas);
                            $findone=PurchaseDemand::find()->where(['pur_number'=>$v->pur_number])->one();
                            if($findone){
                                $findone->delete();
                            }
                            $number.= $b->demand_number.',';
                        }

                    } else {
                        Yii::$app->getSession()->setFlash('error', '恭喜你,撤销失败了,此单不是通过需求创建,无法回退需求', true);
                    }
                }
                if($number)
                {
                    Yii::$app->getSession()->setFlash('success', '恭喜你,撤销采购单成功,此单作废,需求单'.$number.'回退', true);
                } else{
                    Yii::$app->getSession()->setFlash('success', '恭喜你,撤销采购单成功,此单作废', true);
                }

            } else {
                Yii::$app->getSession()->setFlash('error', '对不起,撤销失败了,只有未确认的才能撤销', true);

            }
            $transaction->commit();
        }catch (Exception $e) {
            $transaction->rollBack();
        }
        return $this->redirect(['index']);
    }
    /**
     * 撤销确认
     * @return \yii\web\Response
     */
    public function actionRevokeConfirmation()
    {
        $ids = Yii::$app->request->get('ids');
        $result = PurchaseOrder::UpdatePurchaseStatus($ids,1);
        if ($result) {
            Yii::$app->getSession()->setFlash('success', '恭喜你,撤销确认成功', true);

        }
        return $this->redirect(['index']);
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
        if ( $model->load(Yii::$app->request->post()) && $model->save())
        {
            Yii::$app->getSession()->setFlash('success','恭喜你,新增物流记录成功');
            return $this->redirect(Yii::$app->request->referrer);
        } else {
            $pur_number = Yii::$app->request->get('pur_number');
            return $this->render('tracking',['model' =>$model,'pur_number'=>$pur_number]);
        }
    }


    /**
     * 编辑跟踪记录
     * @return string
     */
    public function  actionEditTracking()
    {
        $pur_number = Yii::$app->request->get('pur_number');
        if(Yii::$app->request->isPost) {
            $post = Yii::$app->request->post();
            if(isset($post['pur_number'])) {
                $model = new PurchaseOrderShip();
                $model->pur_number = $post['pur_number'];
                $model->express_no       = $post['express_no'];
                $model->cargo_company_id = $post['cargo_company_id'];
                $model->is_push          = 0;
                $model->save(false);
            } else {
                $data = Vhelper::changeData($post['PurchaseOrderShip']);
                foreach($data as $v) {
                    $mos = PurchaseOrderShip::find()->where(['id' => $v['id']])->one();
                    $mos->cargo_company_id = $v['cargo_company_id'];
                    $mos->express_no       = $v['express_no'];
                    $mos->is_push          = 0;
                    $mos->save(false);
                }
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
            if (is_array($order_id))
            {
                foreach($order_id as $v)
                {
                    $models_order               = $model->findOne(['pur_number' => $v]);
                    $models_order->date_eta     = $data['date_eta'];
                    $models_order->is_arrival   = $data['arrivaltype'];
                    $models_order->arrival_note = $data['arrival_note'];
                    $models_order->save(false);

                }
            } else {
                $models_order               = $model->findOne(['pur_number' => $order_id]);
                $models_order->date_eta     = $data['date_eta'];
                $models_order->is_arrival   = $data['arrivaltype'];
                $models_order->arrival_note = $data['arrival_note'];
                $models_order->save(false);
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
    private function executePayment($data)
    {
        $opn = $data['pur_number'];
        $order = PurchaseOrder::find()->where(['pur_number' => $opn])->one();
        $pay_model = new PurchaseOrderPay();
        $pay_detail_model = new PurchaseOrderPayDetail();
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $order->pay_status = self::ININ_PAY_STATUS;

            // 支付主表
            $pay_model->settlement_method  = $order->account_type;
            $pay_model->supplier_code      = $order->supplier_code;
            $pay_model->currency           = $order->currency_code;
            $pay_model->pay_type           = $order->pay_type;
            $pay_model->create_notice      = $data['create_notice'];
            $pay_model->requisition_number = CommonServices::getNumber('PP');
            $pay_model->pur_number         = $opn;
            $pay_model->pay_status         = self::ININ_PAY_STATUS;
            $pay_model->pay_name           = '采购费用';
            $pay_model->pay_price          = $data['pay_price'];

            // 支付子表
            $pay_detail_model->pur_number  = $opn;
            $pay_detail_model->freight     = isset($data['freight']) ? $data['freight'] : '';
            $pay_detail_model->discount    = isset($data['discount']) ? $data['discount'] : '';
            $pay_detail_model->requisition_number = $pay_model->requisition_number;

            //手动请款和现在新增的比例请款
            if(isset($data['sku_list'])) {
                if ($data['payType'] == 4) {
                } else{
                    $pay_detail_model->sku_list = json_encode($data['sku_list']);
                }
            }
            if (isset($data['skus'])) {
                if ($data['payType'] == 4) {
                } else {
                    $pay_detail_model->sku_list = json_encode($data['skus']);
                }
            }

            $a = $order->save(false);
            $b = $pay_model->save(false);
            $c = PurchaseOrderPayType::setPayType($opn, ['request_type' => $data['payType']]); // 请款类型
            $d = $pay_detail_model->save(false);

            // 写采购日志
            PurchaseLog::addLog(['pur_number' => $opn, 'note' => 'FBA单次请款']);

            $submit = $a && $b && $c && $d;
            if($submit) {
                $transaction->commit();
                Yii::$app->getSession()->setFlash('success','恭喜您，申请付款成功');
                return $this->redirect(Yii::$app->request->referrer);
            } else {
                $transaction->rollBack();
                Yii::$app->getSession()->setFlash('success','对不起，申请付款失败');
                return $this->redirect(Yii::$app->request->referrer);
            }
        } catch(Exception $e) {
            $transaction->rollBack();
            Yii::$app->getSession()->setFlash('success','对不起，申请付款失败');
            return $this->redirect(Yii::$app->request->referrer);
        }
    }

    /**
     * 采购单和合同申请付款唯一入口
     * -申请付款
     */
    public function actionPayment()
    {
        $request = Yii::$app->request;
        if($request->isPost) {
            if(!empty($request->post('Payment')['source']) && ($request->post('Payment')['source'] == 1) ) { //合同
                $data =  $this->executeCompactPayment($request->post());
                if (empty($data['error'])) {
                } else {
                    Yii::$app->getSession()->setFlash($data['status'],$data['message']);
                }
                return $this->redirect( ($data['error']==200)? [$data['url']] : (Yii::$app->request->referrer) );
            } else {
                return $this->executePayment($request->post());
            }
        } else {
            $pur_number  = $request->get('pur_number');
            $compact_number  = $request->get('compact_number');
            if($compact_number) { // 合同付款流程
                $data = $this->getCompactPayment($compact_number);
                if (!empty($data['error'])) {
                    Yii::$app->getSession()->setFlash('warning',$data['message']);
                    return $this->redirect(Yii::$app->request->referrer);
                }
                return $this->render('compact-payment', $data);
            }else {// 网采单付款流程
                $data = $this->getMultiplePayment($pur_number);
                if (!empty($data['error'])) {
                    Yii::$app->getSession()->setFlash('warning',$data['message']);
                    return $this->redirect(Yii::$app->request->referrer);
                }
                return $this->renderAjax('multiple-payment', $data);
            }
        }
    }
    /**
     * 获取合同请款的展示数据
     */
    public function getCompactPayment($compact_number=null)
    {
        // 获取合同信息
        $compactModel = PurchaseCompact::find()->where(['compact_number' => $compact_number])->one();
        // 获取订单号
        $pur_numbers = PurchaseCompactItems::find()
            ->select('pur_number')
            ->where(['compact_number'=>$compact_number, 'bind'=>1])
            ->asArray()->all();
        $pur_numbers = array_column($pur_numbers, 'pur_number');
        //校验1：是否存在作废的单
        $res = PurchaseOrderCancel::find()->where(['in', 'pur_number', $pur_numbers])->andWhere(['audit_status'=>1])->all();
        if (!empty($res)) return ['error'=>500,'message'=>'有未审核的未到货申请，不能请款'];

        //获取订单信息
        $order_model = new PurchaseOrder();
        $pay_model   = new PurchaseOrderPay();
        $orderModel  = $order_model->find()->where(['in', 'pur_number', $pur_numbers])->all();

        $cancel_price = PurchaseOrderCancel::getCompactCancelPrice($compact_number);

        $orderInfos = [];
        $payInfos = [];
        foreach ($orderModel as $key => $model) {
            if(is_null($model)) return ['error'=>500,'message'=>'订单信息不存在'];
            if(in_array($model->purchas_status, [1, 2, 4, 10])) return ['error'=>500,'message'=>'你的采购单状态可能是：待确认、采购已确认（但还未通过审核）、 撤销、 作废，这些状态下，暂时不能请款'];
            // 审核不通过的，后期不想再作更改的，可以跳过校验
            if($model->shipfees_audit_status == 0) return ['error'=>500,'message'=>'你有修改过订单信息，还没有审核通过，暂时不能请款'];

            $payInfo     = $pay_model->getPayDetail($compact_number);
            $orderInfo   = $order_model->getOrderDetail($model->pur_number, $payInfo['skuNum'], null, true);
            if (empty($orderInfo)) return ['error'=>500,'message'=>$compact_number . '--合同没有可申请的sku，原因：sku全部作废了'];
            $type        = $model->purchaseOrderPayType;
            $rpt         = $type ? $type->request_type : 0;  // 订单的请款方式
            if($payInfo['countPayMoney'] == 0) $rpt = 0;

            $orderInfos[] = $orderInfo;
            $payInfos[] = $payInfo;
        }
        $data = ['compactModel' => $compactModel,
            'model'       => $model,
            'orderInfos'   => $orderInfos,
            'payInfo'     => $payInfo,
            'rpt'         => $rpt,
            'cancel_price'=> $cancel_price,
            ];
        return $data;
    }
    /**
     * 获取网采单请款的数据
     */
    public function getMultiplePayment($pur_number=null)
    {
        $res = PurchaseOrderCancel::find()->where(['pur_number'=>$pur_number,'audit_status'=>1])->one();
        if (!empty($res)) return ['error'=>500,'message'=>'有未审核的未到货申请，不能请款'];

        $order_model = new PurchaseOrder();
        $pay_model   = new PurchaseOrderPay();
        $model       = $order_model->findOne(['pur_number' => $pur_number]);

        if(is_null($model)) return ['error'=>500,'message'=>'订单信息不存在'];
        if(in_array($model->purchas_status, [1, 2, 4, 10])) return ['error'=>500,'message'=>'你的采购单状态可能是：待确认、采购已确认（但还未通过审核）、 撤销、 作废，这些状态下，暂时不能请款'];
        // 审核不通过的，后期不想再作更改的，可以跳过校验
        if($model->shipfees_audit_status == 0) return ['error'=>500,'message'=>'你有修改过订单信息，还没有审核通过，暂时不能请款'];

        $payInfo     = $pay_model->getPayDetail($pur_number);
        $orderInfo   = $order_model->getOrderDetail($pur_number, $payInfo['skuNum']);
        $type        = $model->purchaseOrderPayType;
        $rpt         = $type ? $type->request_type : 0;  // 订单的请款方式

        if($payInfo['countPayMoney'] == 0) {
            $rpt = 0;
        }
        $data = [
            'model'       => $model,
            'orderInfo'   => $orderInfo,
            'payInfo'     => $payInfo,
            'rpt'         => $rpt,
        ];
        return $data;
    }
    // 合同请款执行
    public function executeCompactPayment($post)
    {
        $request = Yii::$app->request;
        $data = $request->post('Payment');
        $payTypeData = $request->post('payType');
        $payRatioData = $payinfo['ratio'] = $request->post('pay_ratio'); //请款比例
        $payObj = new PurchaseOrderPay();
        $cpn = $data['compact_number'];
        $compact = PurchaseCompact::find()->where(['compact_number' => $cpn])->one();
        $pos = PurchaseCompact::getPurNumbers($cpn);
        $freight = isset($data['freight']) ? $data['freight'] : 0; //运费
        $discount = isset($data['discount']) ? $data['discount'] : 0;//优惠额

        // 获取申请金额
        $pay_ratio = $payRatioData ? $payRatioData : 0; //付款类别 0默认 10对私付运费 11合同全额付款 12合同定金 13合同中款 20合同尾款
        $pay_price = isset($data['pay_price']) ? $data['pay_price'] : 0; //请款金额

        if(!$pay_ratio && !$pay_price) return ['error'=>500, 'status'=>'warning', 'message'=>'申请失败，申请金额有误'];
        $payinfo['money'] = $pay_price;   // 请款金额
        /*if(!$pay_price) {
            $payinfo = Json::decode($pay_ratio);
            $p1 = PurchaseOrderPay::find()
                ->where(['pur_number' => $cpn, 'pay_category' => $payinfo['pay_category']])
                ->andWhere(['not in', 'pay_status', $this->noPayStatus])
                ->one();
            if(!empty($p1)) exit('你已经申请过'.PurchaseOrderServices::getPayCategory($payinfo['pay_category']));
        }*/
        if($payinfo['money'] <= 0) exit('请款金额不能小于等于0');


        // 合同已申请金额
        $m1 = $payObj->getCompactApplyMoney($cpn);
        // 合同实际金额
        if($compact->is_drawback == 1) {
            $m2 = $compact->real_money;
        } else {
            $m2 = $compact->real_money + $compact->freight;
        }


        /**
         * 请款名称：付款单种类明细
         */
        $payCategory = 21; //手动请款
        if ($payTypeData==1) {
            $payCategory=25; //入库数量
        } elseif ($payTypeData==2) {
            $payCategory=24; //到货数量
        }elseif ($payTypeData==3) {
            $payCategory=23; //剩余数量
        }elseif ($payTypeData==4) {
            $payCategory=21; //手动请款
        }elseif ($payTypeData==5) {
            $payCategory=12; //比例请款
        }
        $payCategoryInfo =  PurchaseOrderPay::getPayCategory($cpn,$payCategory,$data['pay_price'],$freight,$payRatioData);
        $payinfo['ratio'] = $payCategoryInfo['ratio'];
        $payinfo['pay_category'] = $payCategoryInfo['pay_category'];

        //合同取消的总金额：付尾款时候判断
        $compactItemsInfo = PurchaseCompactItems::getCancelJudgeCompact($pos[0]);
        if (!empty($compactItemsInfo) && $payinfo['pay_category']!=22) {//'22' => '合同运费',
            $cancel_price = PurchaseOrderCancel::getCompactCancelPrice($cpn,false);
        } else {
            $cancel_price = 0;
        }

        // 支付尾款金额=尾款总金额-取消金额
        // 申请尾款请款金额时需判断请款金额不能大于应请款金额,（应请款金额=原支付尾款金额-取消金额）
        $payinfo['money'] = $pay_price-$cancel_price; //应请款金额
        if ($payinfo['money']<=0) exit("申请尾款请款金额不能大于应请款金额：应请款金额=原支付尾款金额-取消金额");

        // 已申请金额 + 本次申请金额
        $m3 = $m1 + $payinfo['money'];
        if(bccomp($m3, $m2, 2) == 1) exit("你的申请金额已经超出了合同总金额<br/>原请款金额：{$payinfo['money']}<br />取消金额：{$cancel_price}<br />已请款金额：{$m1}<br />合同单总金额：{$m2}<br />");
        /**
         * 校验：合同运费走私账
         * /
        /*if($payinfo['pay_category'] == 10) {
            $validate = \yii\base\DynamicModel::validateData($data, [
                ['purchase_account', 'required', 'message' => '运费走私账，账号不能为空'],
                ['pai_number', 'required', 'message' => '运费走私账，拍单号不能为空'],
            ]);
            if($validate->hasErrors()) {
                $errors = $validate->errors;
                echo '错误信息：';
                foreach($errors as $v) echo "<p>{$v[0]}</p>";
                exit;
            }
        } else {
            $data['purchase_account'] = $data['pai_number'] = '';
        }*/

        

        $pay_model = new PurchaseOrderPay();
        $transaction = Yii::$app->db->beginTransaction();
        $order = PurchaseOrder::find()->where(['in', 'pur_number', $pos])->one(); // 只取合同下的一个单作为数据源
        try {
            // fba合同默认： 待经理审核（申请合同运费时，手动输入金额时，请款单需要走业务部门审核流程）
            $models = PurchaseOrder::updateAll(['pay_status'=>10], ['in', 'pur_number', $pos]);
            $pay_model->pur_number         = $cpn; // 在原有的订单号字段上，写上合同号
            $pay_model->requisition_number = CommonServices::getNumber('PP');
            $pay_model->pay_status         = 10;                        // 付款状态：待经理审核
            $pay_model->pay_ratio          = $payinfo['ratio'];          // 请款比例
            $pay_model->pay_price          = $payinfo['money'];          // 请款金额
            $pay_model->js_ratio           = $data['js_ratio'];          // 结算比例（拉取合同的）
            $pay_model->pay_category       = $payinfo['pay_category'];   // 付款种类
            $pay_model->purchase_account   = isset($data['purchase_account']) ? trim($data['purchase_account']) : ''; // 私账付款账号
            $pay_model->pai_number         = isset($data['pai_number']) ? trim($data['pai_number']) : ''; // 私账付款拍单号
            $pay_model->pay_name           = PurchaseOrderServices::getPayCategory($payinfo['pay_category']);//付款名称
            $pay_model->settlement_method  = $order->account_type;       // 结算方式
            $pay_model->supplier_code      = $order->supplier_code;      // 供应商编码
            $pay_model->currency           = $order->currency_code;      // 币种
            $pay_model->pay_type           = $order->pay_type;           // 支付方式
            $pay_model->create_notice      = $data['create_notice'];     // 申请备注
            $pay_model->source             = 1;                          // 标记请款单为合同请款
            $pay_model->save(false);

            // 支付子表
            $pay_detail_model = new PurchaseOrderPayDetail();
            $pay_detail_model->pur_number  = $cpn;
            $pay_detail_model->freight     = isset($data['freight']) ? $data['freight'] : 0;
            $pay_detail_model->discount    = isset($data['discount']) ? $data['discount'] : 0;
            $pay_detail_model->requisition_number = $pay_model->requisition_number;
            //手动请款和现在新增的比例请款
            if (isset($post['skus'])) {
                if ( ($post['payType'] == 4) || ($post['payType'] == 5) ) {
                } else {
                    $pay_detail_model->sku_list = json_encode($post['skus']);
                }
            }

            $pay_detail_model->save(false);

            if($payinfo['pay_category'] == 10) {
                $logMsg = "申请合同运费，运费需要走私账，金额为{$payinfo['money']}";
            } else {
                $logMsg = "申请付款，付款比例为{$payinfo['ratio']}，付款金额为{$payinfo['money']}";
            }
            // 写采购日志
            PurchaseLog::addLog(['pur_number' => $cpn, 'note' => $logMsg]);
            $transaction->commit();
            if($payinfo['pay_category'] == 10) {
                return ['error'=>'500', 'status'=>'success','message'=> '恭喜您，申请付款成功'];
            } else {
                return ['error'=>'200', 'status'=>'success','message'=> '恭喜您，请款信息已经保存成功，请填写付款申请书','url'=>"/overseas-purchase-order/write-payment?compact_number={$cpn}&pid={$pay_model->id}"];
            }
        } catch(Exception $e) {
            $transaction->rollBack();
            return ['error'=>'500', 'status'=>'warning','message'=> '对不起，申请付款失败'];
        }
    }
    /**
     * 付款申请书
     */
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

            $viewUrl = 'index';
            $purchase_type = strstr($data['compact_number'], 'FBA')?3:2;
            if ($purchase_type == 3) $viewUrl = '/fba-purchase-order/index?source=1';

            if($res) {
                Yii::$app->getSession()->setFlash('success','恭喜你，保存付款申请书成功');
                return $this->redirect([$viewUrl]);
            } else {
                Yii::$app->getSession()->setFlash('success','对不起，保存付款申请书失败');
                return $this->redirect([$viewUrl]);
            }
        } else {
            $cpn = $request->get('compact_number');
            $pid = $request->get('pid');
            $tid = $request->get('tid');
            if(!$cpn || !$pid) {
                exit('参数错误，必须指定正确的合同号与支付单ID');
            }
            if($tid) {
                // 渲染选择的模板
                $tpl = Template::findOne($tid);
                $tplPath = $tpl->style_code;
                // 获取供应商账户信息
                $model = PurchaseOrderPay::findOne($pid);
                $account = SupplierPaymentAccount::find()->where(['supplier_code' => $model->supplier_code])->one();
                $is_drawback = PurchaseCompact::find()->select('is_drawback')->where(['compact_number' => $cpn])->scalar();
                $pos = PurchaseCompact::getPurNumbers($model->pur_number);


                return $this->render("//template/tpls/{$tplPath}", [
                    'model' => $model,
                    'account' => $account,
                    'pos' => implode(' ', $pos),
                    'is_drawback' => $is_drawback,
                    'tpl_id' => $tpl->id
                ]);
            }
            // 加载模板列表页
            $tpls = Template::find()->where(['platform' => 2, 'type' => 'FKSQS', 'status' => 1])->all();
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
    public function actionGetPurchaseAmount()
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
    /**
     * 查看合同列表
     */
    public function actionCompactList()
    {
        $args = Yii::$app->request->queryParams;
        $searchModel = new PurchaseCompactSearch();
        $dataProvider = $searchModel->search($args, 3);
        return $this->render('/overseas-purchase-order/compact-list', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * 批量申请付款
     */
    public function actionAllpayment()
    {
        $request = Yii::$app->request;
        if($request->isPost) {
            $post = Yii::$app->request->post();
            $payData = $post['AllPayment'];
            $t = Yii::$app->db->beginTransaction();
            $result = [];
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
                    $pay->pay_status         = 2;  // 已申请付款(待审批，批量付款直接到财务环节)
                    $pay->pay_category       = 30; // FBA批量请款
                    $pay->pay_name           = 'FBA采购费用';
                    $pay->pay_price          = $v['pay_price'];
                    $pay->create_notice      = $model->confirm_note;
                    $pay->currency           = $model->currency_code;
                    $pay->pay_type           = $model->pay_type;

                    $model->pay_status = 2;
                    if($model->purchas_status !== 6) {
                        $model->purchas_status = 7;
                    }

                    PurchaseLog::addLog([
                        'pur_number' => $model->pur_number,
                        'note' => "FBA批量付款，对 {$v['pur_number']}, 申请了 {$v['pay_price']}",
                    ]);

                    $a = $pay->save(false);
                    $b = $model->save(false);
                    if($a && $b) {
                        $result[] = 'true';
                    } else {
                        $result[] = 'false';
                    }

                }

                if(in_array('false', $result)) {

                    $t->rollBack();
                    Yii::$app->getSession()->setFlash('error','对不起，有一个订单在请款时失败了');
                    return $this->redirect(Yii::$app->request->referrer);

                } else {
                    $t->commit();
                    Yii::$app->getSession()->setFlash('success','恭喜您，申请付款成功，请等待出纳付款');
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

            $max = self::MAX_PAY_NUM;
            $ids = explode(',', $ids);

            if(count($ids) > $max) {
                return "批量付款一次最多支持{$max}条订单";
            }

            $orders = PurchaseOrder::find()
                ->joinWith(['purchaseOrderItems', 'purchaseOrderPayType', 'purchaseOrderPay'])
                ->where(['in', 'pur_purchase_order.id', $ids])
                ->andWhere(['pur_purchase_order.shipfees_audit_status' => 1, 'pur_purchase_order.buyer' => Yii::$app->user->identity->username])
                ->andWhere(['not in', 'pur_purchase_order.purchas_status', [1, 2, 4, 10]])
                ->asArray()
                ->all();

            if(empty($orders)) {
                $msg = '<h5>对不起，不能请款，可能的原因如下：</h5><p>1. 订单处于待确认、已确认（未审核）、撤销或作废状态。</p><p>2. 订单的采购员不是当前登录用户。</p><p>3. 订单修改过信息，但是还没有通过审核。</p>';
                return $msg;
            }

            foreach($orders as $v) {

                if(in_array($v['purchas_status'], self::$not_pay_status)) {
                    return '你勾选的订单中，存在不能直接请款的，请去掉后重试';
                }

                $res = PurchaseOrderCancel::find()->where(['pur_number' => $v['pur_number'], 'audit_status' => 1])->one();
                if(!empty($res)) {
                    Yii::$app->getSession()->setFlash('error',$v['pur_number'] . '有未审核的未到货申请，不能请款');
                    return $this->redirect(Yii::$app->request->referrer);
                }

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
        $model= PurchaseNote::findAll(['pur_number'=>$id]);
        if (empty($model))
        {
            return '暂无备注';
        }
        return $this->renderAjax('get-note',['model' =>$model]);
    }


    /**
     * 增加采购备注
     * @return string|\yii\web\Response
     */
    public function actionAddPurchaseNote()
    {
        $model = new PurchaseNote();
        if ( $model->load(Yii::$app->request->post()) && $model->save(false))
        {
            $flag = Yii::$app->request->post()['flag'];
            Yii::$app->getSession()->setFlash('success','恭喜你,新增备注成功');
            $data = [
                '1' =>'/fba-purchase-order/index',
                '2' =>'/purchase-order-pay-notification/index',
                '3' =>'/purchase-order-cashier-pay/index',
                '4' =>'/purchase-order-receipt-notification/index',
            ];
            return $this->redirect(Yii::$app->request->referrer);
        } else {
            $pur_number = Yii::$app->request->get('pur_number');
            $flag       = Yii::$app->request->get('flag');
            return $this->renderAjax('note',['model' =>$model,'pur_number'=>$pur_number,'flag'=>$flag]);
        }
    }
    /**
     * 批量增加采购备注
     * @return string|\yii\web\Response
     */
    public function actionAddPurchaseNotes()
    {
        $model = new PurchaseNote();
        if ( Yii::$app->request->post())
        {
            $pur_number = \Yii::$app->session->get('pur_number_note');
            $note = Yii::$app->request->post('PurchaseNote')['note'];

            $transaction=\Yii::$app->db->beginTransaction();
            try {
                foreach ($pur_number as $v) {
                    $models = new PurchaseNote();
                    $models->pur_number = $v['pur_number'];
                    $models->note = $note;
                    $models->create_time = date('Y-m-d H:i:s',time());
                    $models->create_id = Yii::$app->user->id;
                    $models->purchase_type = 3;
                    $models->save(false);
                }

                $flag = Yii::$app->request->post()['flag'];
                Yii::$app->getSession()->setFlash('success','恭喜你,新增备注成功');
                $data = [
                    '1' =>'/fba-purchase-order/index',
                    '2' =>'/purchase-order-pay-notification/index',
                    '3' =>'/purchase-order-cashier-pay/index',
                    '4' =>'/purchase-order-receipt-notification/index',
                ];
                $transaction->commit();
                Yii::$app->session->remove('pur_number_note');
            } catch(\Exception $e) {
                $transaction->rollback();
                return json_encode(['error' => 1, 'message' => 'error']);
            }
            return $this->redirect(Yii::$app->request->referrer);
        } else {
            $id = Yii::$app->request->get('id');
            $pur_number = PurchaseOrder::find()->select('pur_number')->where(['in','id',$id])->asArray()->all();
            \Yii::$app->session->set('pur_number_note', $pur_number);
            $flag = 1;
            return $this->renderAjax('note',['model' =>$model,'pur_number'=>$pur_number,'flag'=>$flag]);
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
                $result = $model_receipt->verifyRefundEvent($post);
                if($result['error'] == 1) {
                    Yii::$app->getSession()->setFlash('error',$result['message']);
                    return $this->redirect(Yii::$app->request->referrer);
                }
                $transaction = Yii::$app->db->beginTransaction();
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
                    ];
                    $a = $model_receipt->insertRow($data);
                    $b = $order_s->save(false);
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

                    // 否则作废,得清除在途库存
                    // 更新在途库存
                    // $mods = PurchaseOrderItems::getSKUc($order['pur_number']);
                    // Stock::updateStock($mods);

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

                    // 更新中间表
                    PurchaseDemand::UpdateOne($pur_number);
                    $demand_numbers = PurchaseDemand::find()->select('demand_number')->where(['pur_number'=>$pur_number])->column();
                    //缺货列表更新处理状态
                    
                    AmazonOutofstockOrder::updateAll(['status'=>0,'is_show'=>0,'demand_number'=>''],['in','demand_number',$demand_numbers]);
                    PlatformSummary::updateAll(['level_audit_status'=>5,'is_purchase'=>1,'is_push'=>0],'demand_number in :demand_number and source=:source and purchase_type=:type',
                        [':demand_number'=>$demand_numbers,':source'=>2,':type'=>3]);
                    // 作废采购单重新推送
                    $order_s->is_push        = 0;
                    $order_s->confirm_note   = $post['confirm_note'];
                    $order_s->purchas_status = 10;
                    $b = $order_s->save(false);
                    $is_submit = $a && $b;
                } else {
                    $transaction->rollBack();
                    Yii::$app->getSession()->setFlash('error','对不起，你提交的数据有误');
                    return $this->redirect(Yii::$app->request->referrer);
                }
                if($is_submit) {
                    $transaction->commit();
                    Yii::$app->getSession()->setFlash('success','恭喜你，操作成功了');
                    return $this->redirect(Yii::$app->request->referrer);
                } else {
                    $transaction->rollBack();
                    Yii::$app->getSession()->setFlash('error','对不起，操作失败了');
                    return $this->redirect(Yii::$app->request->referrer);
                }
            } catch (Exception $e) {
                $transaction->rollBack();
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

            return $this->renderAjax('edit', [
                'orderInfo' => $orderInfo,
                'payInfo' => $payInfo,
                'refundInfo' => $refundInfo
            ]);
        }
    }

    /**
     * 修改采购确认
     * @return \yii\web\Response
     */
    public function actionSubmitAudit()
    {
        $model        = new PurchaseOrder();
        $model_note   = new PurchaseNote();
        $model_tax    = new PurchaseOrderTaxes();
        $model_estimated_time = new PurchaseEstimatedTime();

        $model_order_pay_type = new PurchaseOrderPayType();

        if (Yii::$app->request->isPost)
        {
            $PurchaseOrder      = Vhelper::changeData(Yii::$app->request->post()['PurchaseOrder']);
            $purchaseOrderItems = Vhelper::changeData(Yii::$app->request->post()['purchaseOrderItems']);
            $PurchaseNote       = Vhelper::changeData(Yii::$app->request->post()['PurchaseNote']);
            $PurchaseEstimatedTime      = Vhelper::changeData(Yii::$app->request->post()['PurchaseEstimatedTime']);
            
            // 获取订单实际金额：商品总额 + 运费 - 优惠
            foreach($PurchaseOrder as $k => &$v) {
                $skuPrice = 0;
                $v['freight'] = $v['freight'] ? $v['freight'] : 0;
                $v['discount'] = $v['discount'] ? $v['discount'] : 0;
                foreach($purchaseOrderItems as $m => $n) {
                    if($v['pur_number'] == $n['pur_number']) {
                        $skuPrice += $n['totalprice'];
                    }
                }
                $v['real_price'] = $skuPrice + $v['freight'] - $v['discount'];
                if ($v['is_drawback'] == 2 && $v['pay_type'] != 3) {
                    Yii::$app->getSession()->setFlash('error','含税采购 支付方式必须是 【银行卡转账】');
                    return $this->redirect(Yii::$app->request->referrer);
                }
            }

            $transaction=\Yii::$app->db->beginTransaction();
            try {
                $model->PurchaseOrder($PurchaseOrder);                    // 订单主表

                $model_order_pay_type->saveOrderPayType2($PurchaseOrder);  // 订单子表

                $model->FbaPurchaseOrderItems($purchaseOrderItems,$PurchaseOrder,Yii::$app->request->post()['PurchaseOrderTaxes']);          // 订单商品表

                $model_note->saveNotes($PurchaseNote);

                //$model_tax->saveTax(Yii::$app->request->post()['PurchaseOrderTaxes']);

                $model_estimated_time->saveEstimatedTime($PurchaseEstimatedTime);

                $transaction->commit();

                Yii::$app->getSession()->setFlash('success','恭喜你确认成功');

                return $this->redirect(Yii::$app->request->referrer);

            }catch (Exception $e) {
                $transaction->rollBack();
                Yii::$app->getSession()->setFlash('error','数据异常！保存失败');
                return $this->redirect(Yii::$app->request->referrer);
            }

        } else {
            $id = Yii::$app->request->get('id');
            $map['pur_purchase_order.id']       = $id;
            $map['pur_purchase_order.purchas_status'] =1;
            $models     = PurchaseOrder::find()->joinWith(['purchaseOrderItems','orderNote','orderOrders','platformSummary','purchaseOrderAccount','purchaseDiscount'])->where($map)->all();
            if ($models){

                return $this->renderAjax('attributes', [
                    'models' => $models,
                    'model_tax'=>$model_tax,
                    'id' =>$id,
                    'model_estimated_time'=>$model_estimated_time,
                ]);
            } else {
                echo '存在重复进行采购确认的采购单.请勿勾选状态为采购确认的单据';
            }

        }
    }

    /**
     * 批量审核
     */
    public  function actionAllReview()
    {
        if (Yii::$app->request->isAjax)
        {
            $id = Yii::$app->request->get('id');
            $map['pur_purchase_order.id'] = $id;
            $map['pur_purchase_order.purchas_status'] = 2;
            $ordersitmes = PurchaseOrder::find()->joinWith(['purchaseOrderItems','supplier'])->where($map)->all();
            if(!$ordersitmes)
            {
                echo '存在重复进行已审批的采购单.请勿勾选状态为已审批的单据';
            } else{
                return $this->renderAjax('batch-review', [
                    'model' =>$ordersitmes,
                    'name'  =>Yii::$app->request->get('name'),
                ]);
            }
        }else{
            $id                                 = Yii::$app->request->post()['PurchaseOrder']['id'];
            $purchas_status                     = Yii::$app->request->post()['PurchaseOrders']['purchas_status'];
            $b= Vhelper::changeData(Yii::$app->request->post()['PurchaseOrder']);
            $ordersitmes                        = PurchaseOrder::find()->where(['in','id',$id])->all();
            foreach ($ordersitmes as $k=>$ordersitem)
            {
                if ($purchas_status[$k] == 3) {
                    //更新在途库存
                    //$mods = PurchaseOrderItems::getSKUc($ordersitem->pur_number);
                    //Stock::saveStock($mods);
                    //已审核
                    $ordersitem->purchas_status = 7;// User:zwl date:20180907 转提交到等待到货状态
                    $ordersitem->pay_status = 1;
                    $ordersitem->audit_return = 2;
                    $ordersitem->audit_note = $b[$k]['audit_note'];
                    $ordersitem->audit_time = date('Y-m-d H:i:s');
                    $ordersitem->is_check_goods = PurchaseOrder::isCheckGoods($ordersitem); //是否验货

                    /*
                    $order_items_info = PurchaseOrderItems::find()->where(['in','pur_number',$ordersitem['pur_number']])->all();
                    foreach ($order_items_info as $k=>$v) {
                        $supplierQuotes[$k]['sku'] = $v['sku'];
                        $supplierQuotes[$k]['supplier_code'] = $ordersitem['supplier_code'];
                        $supplierQuotes[$k]['price'] = $v['price'];
                        $supplierQuotes[$k]['buyer'] = $ordersitem['buyer'];
                    }
                    */
                    //修改供应商报价表的的单价
                    //$model_supplier = new SupplierQuotes();
                    //$model_supplier->saveSupplierQuotes($supplierQuotes);

                    //采购单日志添加
                    $s = [
                        'pur_number' => $ordersitem->pur_number,
                        'note' => '批量采购审核通过',
                    ];
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
                }
                $ordersitem->save(false);
            }
            Yii::$app->getSession()->setFlash('success','恭喜你,审核成功');
            return $this->redirect(Yii::$app->request->referrer);
        }
    }
    /**
     * 采购审核-审核操作
     * Displays a single PurchaseOrder model.
     * @return mixed
     */
    public  function actionReview()
    {

        if (Yii::$app->request->isAjax)
        {
            $id = Yii::$app->request->get('id');
            $map['pur_purchase_order.id'] = $id;
            //单个审核
            $ordersitmes = PurchaseOrder::find()->joinWith(['purchaseOrderItems','supplier','orderNote'])->where($map)->one();
            // Vhelper::dump($ordersitmes);
            return $this->renderAjax('review', [
                'model' =>$ordersitmes,
                'name'  =>Yii::$app->request->get('name'),
            ]);


        } elseif(Yii::$app->request->isPost) {

            $id                                 = Yii::$app->request->post()['PurchaseOrder']['id'];
            $purchas_status                     = Yii::$app->request->post()['PurchaseOrder']['purchas_status'];
            $audit_note                         = Yii::$app->request->post()['PurchaseOrder']['audit_note'];
            $ordersitmes                        = PurchaseOrder::find()->where(['in','id',$id])->all();

            foreach ($ordersitmes as $ordersitem)
            {
                if ($purchas_status==3)
                {
                    //更新在途库存 暂时关闭
                    //$mods = PurchaseOrderItems::getSKUc($ordersitem->pur_number);
                    //Stock::saveStock($mods,$ordersitem->warehouse_code);
                    //已审核
                    $ordersitem->purchas_status=3;
                    $ordersitem->pay_status    =1;
                    $ordersitem->audit_return  =2;
                    $ordersitem->audit_note    =$audit_note;
                    $ordersitem->audit_time    =date('Y-m-d H:i:s');
                    $ordersitem->is_check_goods = PurchaseOrder::isCheckGoods($ordersitem); //是否验货
                    //采购单日志添加
                    $s = [
                        'pur_number' => $ordersitem->pur_number,
                        'note'       =>'采购审核通过',
                    ];
                    PurchaseLog::addLog($s);
                } elseif ($purchas_status==4) {
                    //审核退回标志
                    $ordersitem->audit_return =1;
                    //回退采购状态到待确认
                    $ordersitem->purchas_status=1;
                    $ordersitem->audit_note    =$audit_note;
                    //采购单日志添加
                    $s = [
                        'pur_number' => $ordersitem->pur_number,
                        'note'       =>'采购审核回退至采购确认',
                    ];
                    PurchaseLog::addLog($s);
                } else {
                    //复审
                    $ordersitem->audit_return  =3;
                    $ordersitem->audit_note    =$audit_note;
                    //采购单日志添加
                    $s = [
                        'pur_number' => $ordersitem->pur_number,
                        'note'       =>'采购复审',
                    ];
                    PurchaseLog::addLog($s);

                }

                $ordersitem->save(false);
            }

            Yii::$app->getSession()->setFlash('success','恭喜你,审核成功');
            return $this->redirect(['index']);
        }

    }

    /**
     * 修改供应商
     * @return bool|string|\yii\web\Response
     */
    public function actionUpdateSupplier()
    {
        $model=new PurchaseOrder;
        if(Yii::$app->request->isPost)
        {
            $post              = Yii::$app->request->post('PurchaseOrder');
            $supplier_name     = BaseServices::getSupplierName($post['supplier_code']);
            $arrid             = $post['id'];
            $mo                = PurchaseOrder::findOne(['id' => $arrid]);
            $mo->supplier_name = $supplier_name;
            $mo->supplier_code = $post['supplier_code'];
            $mo->save(false);
            Yii::$app->getSession()->setFlash('success', '恭喜你,供应商被你修改成功了！', true);
            return $this->redirect(Yii::$app->request->referrer);
        }else{
            $ids= Yii::$app->request->get('id');
            $s= PurchaseOrder::findOne(['id' => $ids,'buyer'=>Yii::$app->user->identity->username]);
            if (empty($s))
            {
                Yii::$app->getSession()->setFlash('error','啊啊！此单的主人并不是你,无法修改！');
                return $this->redirect(['index']);
            }
            return $this->render('update-supplier',[
                'model'=>$model,
                'id'=>$ids,
            ]);
        }
    }

    /**
     * 手动创建采购单
     * @return string
     */
    public function actionAddproduct()
    {
        $ordermodel  = new PurchaseOrder();
        $purchasenote = new PurchaseNote();

        if(!empty($_POST['PurchaseOrder']))
        {

            $purdesc=$_POST['PurchaseOrder'];
            //生成采购订单主表、详情表数据
            $orderdata['purdesc']=$purdesc;
            $transaction=\Yii::$app->db->beginTransaction();
            try {
                $pur_number = $ordermodel::Savepurdata($orderdata);
                //加入备注
                $PurchaseNote=[
                    'pur_number'=>$pur_number,
                    'note'      =>$_POST['PurchaseNote']['note'],
                ];
                $purchasenote->saveNote($PurchaseNote);
                $ordermodel::OrderItems($pur_number,$purdesc['items'],'3');
                $transaction->commit();
                Yii::$app->getSession()->setFlash('success', '恭喜你,手动创建采购单成功', true);
                return $this->redirect(['index']);
            }catch (Exception $e) {
                $transaction->rollBack();
                Yii::$app->getSession()->setFlash('error','数据异常！保存失败,请联系管理员');
                return $this->redirect(['index']);
            }
        }
        $temporay= PurchaseTemporary::find()->where(['create_id'=>Yii::$app->user->id])->groupBy('sku')->all();
        return $this->render('addproduct', [

            'purchasenote' =>$purchasenote,
            'ordermodel'=>$ordermodel,
            'temporay'=>$temporay,
        ]);
    }
    public function actionProductIndex()
    {
        $searchModel  = new ProductSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        return $this->renderAjax('_orderform',[
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * 模板查看
     */
    public function actionTemplate()
    {

        $filename = Yii::$app->request->hostInfo . "/images/purchase.csv";//模板放的位置
        $file_name = "purchase.csv";
        $contents = file_get_contents($filename);
        // $file_size = filesize($filename);
        header("Content-type: application/octet-stream;charset=utf-8");
        header("Accept-Ranges: bytes");
        //header("Accept-Length: $file_size");
        header("Content-Disposition: attachment; filename=".$file_name);
        exit($contents);


    }

    /**
     * 清除产品
     * @return \yii\web\Response
     */
    public function actionEliminate()
    {
        PurchaseTemporary::deleteAll(['create_id'=>Yii::$app->user->id]);
        Yii::$app->getSession()->setFlash('success',"清除成功");
        if(Yii::$app->request->get('flat'))
        {
            return $this->redirect(['platform-summary/create-purchase-order']);
        }
        return $this->redirect(['addproduct']);
    }

    /**
     * 导入cvs
     * @return string|\yii\web\Response
     * @throws \yii\db\Exception
     */
    public function actionImportProduct()
    {

        $model = new PurchaseTemporary();
        if (Yii::$app->request->isPost)
        {
            $model->file_execl = UploadedFile::getInstance($model, 'file_execl');

            $data              = $model->upload();

            if(empty($data))
            {
                Yii::$app->getSession()->setFlash('error',"文件上传失败",true);
                return $this->redirect(['index']);
            }
            $file        = fopen($data, 'r');
            $line_number = 0;
            while ($datas = fgetcsv($file))
            {
                if ($line_number == 0)
                { //跳过表头
                    $line_number++;
                    continue;
                }
                $num = count($datas);
                for ($c = 0; $c < $num; $c++)
                {

                    $Name[$line_number][] = mb_convert_encoding(trim($datas[$c]),'utf-8','gbk');

                }
                $Name[$line_number][] = Yii::$app->user->identity->id;
                $line_number++;
            }
            $statu = Yii::$app->db->createCommand()->batchInsert(PurchaseTemporary::tableName(),['sku','purchase_quantity', 'purchase_price','title','create_id'], $Name)->execute();
            fclose($file);
            if ($statu)
            {
                Yii::$app->getSession()->setFlash('success',"恭喜你，导入成功！",true);
                return $this->redirect(['addproduct']);
            } else {
                Yii::$app->getSession()->setFlash('error','恭喜你，导入失败了！请联系管理员',true);
                return $this->redirect(['addproduct']);
            }

        } else {
            return $this->renderAjax('addfile', ['model' => $model]);
        }
    }

    //采购确认删除sku
    public function actionDeleteSku(){
        if(Yii::$app->request->isGet&&Yii::$app->request->isAjax){
            $tran = Yii::$app->db->beginTransaction();
            try{
                $sku       = Yii::$app->request->getQueryParam('sku');
                $purNumber = Yii::$app->request->getQueryParam('purNumber');
                $orderItems = PurchaseOrderItems::find()->where(['pur_number'=>$purNumber])->all();
                if(count($orderItems) <=1){
                    throw new HttpException(500,'该采购单只剩下一个产品了！');
                }
                $orderItem = PurchaseOrderItems::find()->where(['sku'=>$sku,'pur_number'=>$purNumber])->one();
                if(empty($orderItem)){
                    throw new HttpException(500,'该sku不属于改采购单！');
                }
                //删除采购单产品表
                $orderItem->delete();
                $demands = PurchaseDemand::find()->where(['pur_number'=>$purNumber])->all();
                foreach($demands as $value){
                    if(empty($value->platformSummary)){
                        throw new HttpException(500,'采购需求异常！');
                    }
                    $exist = AmazonOutofstockOrder::find()->where(['demand_number'=>$value->demand_number])->exists();
                    if($exist&&$value->platformSummary->sku == $sku){
                        $value->platformSummary->is_purchase = 1;
                        $value->platformSummary->is_push     = 0;
                        $value->platformSummary->level_audit_status     = 5 ;
                        if($value->platformSummary->save() == false){
                            throw new HttpException(500,'需求状态修改失败！');
                        }
                        AmazonOutofstockOrder::updateAll(['demand_number'=>'','is_push'=>0,'status'=>0,'is_show'=>1],['demand_number'=>$value->demand_number]);
                        //删除中间关联表
                        $value->delete();
                    }elseif(!$exist&&$value->platformSummary->sku == $sku){
                        $value->platformSummary->is_purchase = 1;
                        $value->platformSummary->is_push     = 0;
                        if($value->platformSummary->save() == false){
                            throw new HttpException(500,'需求状态修改失败！');
                        }
                        //删除中间关联表
                        $value->delete();
                    }
                }
                $tran->commit();
                $response = ['status'=>'success','message'=>'sku删除成功！'];
            }catch(HttpException $e){
                $tran->rollBack();
                $response = ['status'=>'error','message'=>$e->getMessage()];
            }
            Yii::$app->getSession()->setFlash($response['status'],$response['message'],true);
            return $this->redirect(['index']);
        }
    }


    /**
     * 验证导出数据时 查询条件是否符合规则
     * @param $searchParams
     * @return bool|string
     */
    public static function checkExportValidity($searchParams){
        $searchParams = $searchParams['PurchaseOrderSearch'];
        $return = true;
        $start_time     = !empty($searchParams['start_time'])?$searchParams['start_time']:'0000-00-00 00:00:00';
        $end_time       = !empty($searchParams['end_time'])?$searchParams['end_time']:date('Y-m-d H:i:s');
        $diff_time      = strtotime($end_time) - strtotime($start_time);

        if($diff_time > 32 * 86400){
            $return = '时间期限过长，请选择一个月内的数据导出【改变查询条件后，先查询再导出】';
        }

        return $return;
    }

    //导出采购单产品信息
    public function actionOrderExport(){
        set_time_limit(0);
        ini_set('memory_limit','2048M');
        $form = Yii::$app->request->queryParams;
        $searchModel  = new PurchaseOrderSearch();

        $purNumber = [];// 要导出的采购单号
        if (empty($form['purNumber'])) {
            if ($form['source'] == 1) {
                $searchParams = Yii::$app->session->get('FBA-purchase_order-compact');
                // 验证导出条件 @user Jolon
                $result = self::checkExportValidity($searchParams);
                if($result !== true){
                    Yii::$app->getSession()->setFlash('error',$result,true);
                    return $this->redirect(Yii::$app->request->referrer);
                }
                $query = $searchModel->fbaCompactSearch($searchParams,true);
            } elseif ($form['source'] == 2) {
                $searchParams   = Yii::$app->session->get('FBA-purchase_order-net');
                // 验证导出条件 @user Jolon
                $result = self::checkExportValidity($searchParams);
                if($result !== true){
                    Yii::$app->getSession()->setFlash('error',$result,true);
                    return $this->redirect(Yii::$app->request->referrer);
                }
                $query = $searchModel->search5($searchParams,true);
            }
            // 重组 查询语句
            $query->select = ['pur_purchase_order.pur_number'];
            $export_sql    = $query->createCommand()->getRawSql();
            $model         = Yii::$app->db->createCommand($export_sql)->queryAll();
            foreach ($model as $value) {
                $purNumber[] = $value['pur_number'];
            }
        } else {
           $purNumber =   explode(',',$form['purNumber']); 
        }


        $objectPHPExcel = new \PHPExcel();
        $objectPHPExcel->setActiveSheetIndex(0);
        //报表头的输出
        $objectPHPExcel->getActiveSheet()->mergeCells('A1:O1'); //合并单元格
        $objectPHPExcel->getActiveSheet()->mergeCells('A1:A2');
        $objectPHPExcel->getActiveSheet()->setCellValue('A1','FBA采购单产品信息表');  //设置表标题
        $objectPHPExcel->setActiveSheetIndex(0)->getStyle('A1')->getFont()->setSize(24); //设置字体大小
        $objectPHPExcel->setActiveSheetIndex(0)->getStyle('A1')
            ->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        //表格头的输出
        $objectPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(15);
        $objectPHPExcel->setActiveSheetIndex(0)->setCellValue('A3','采购员');
        $objectPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(6.5);
        $objectPHPExcel->setActiveSheetIndex(0)->setCellValue('B3','采购单号');
        $objectPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
        $objectPHPExcel->setActiveSheetIndex(0)->setCellValue('C3','sku');
//        $objectPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
//        $objectPHPExcel->setActiveSheetIndex(0)->setCellValue('D3','图片');
        $objectPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
        $objectPHPExcel->setActiveSheetIndex(0)->setCellValue('E3','供应商名称');
        $objectPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
        $objectPHPExcel->setActiveSheetIndex(0)->setCellValue('F3','产品名称');
        $objectPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
        $objectPHPExcel->setActiveSheetIndex(0)->setCellValue('G3','采购单价');
        $objectPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
        $objectPHPExcel->setActiveSheetIndex(0)->setCellValue('H3','采购数量');
        $objectPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(15);
        $objectPHPExcel->setActiveSheetIndex(0)->setCellValue('I3','实际到货数量');
        $objectPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(15);
        $objectPHPExcel->setActiveSheetIndex(0)->setCellValue('J3','运费');
        $objectPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(15);
        $objectPHPExcel->setActiveSheetIndex(0)->setCellValue('K3','总金额');
        $objectPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(15);
        $objectPHPExcel->setActiveSheetIndex(0)->setCellValue('L3','采购时间');
        $objectPHPExcel->getActiveSheet()->getColumnDimension('M')->setWidth(15);
        $objectPHPExcel->setActiveSheetIndex(0)->setCellValue('M3','采购仓库');
        $objectPHPExcel->getActiveSheet()->getColumnDimension('N')->setWidth(15);
        $objectPHPExcel->setActiveSheetIndex(0)->setCellValue('N3','预计到货时间');
        $objectPHPExcel->getActiveSheet()->getColumnDimension('O')->setWidth(15);
        $objectPHPExcel->setActiveSheetIndex(0)->setCellValue('O3','快递单号');
        //入库仓、入库单号、良品上架数量、入库时间、订单状态
        $objectPHPExcel->getActiveSheet()->getColumnDimension('P')->setWidth(15);
        $objectPHPExcel->setActiveSheetIndex(0)->setCellValue('P3','入库单号');
        $objectPHPExcel->getActiveSheet()->getColumnDimension('Q')->setWidth(15);
        $objectPHPExcel->setActiveSheetIndex(0)->setCellValue('Q3','良品上架数量');
        $objectPHPExcel->getActiveSheet()->getColumnDimension('R')->setWidth(15);
        $objectPHPExcel->setActiveSheetIndex(0)->setCellValue('R3','入库时间');
        $objectPHPExcel->getActiveSheet()->getColumnDimension('S')->setWidth(15);
        $objectPHPExcel->setActiveSheetIndex(0)->setCellValue('S3','订单状态');

        $purNumber_list = array_chunk($purNumber,2000);// 分批查询数据导出,每次1000个
        unset($purNumber);
        $warehouse_code_list = BaseServices::getWarehouseCode();// 获取所有仓库的名称

        $n = 0;// 记录行数
        foreach($purNumber_list as $purNumber){
            // 获取当前 批次 采购单号的采购单信息、采购单明细信息
            $PurchaseOrder_list         = PurchaseOrder::find()->andFilterWhere(['in','pur_number',$purNumber])->indexBy('pur_number')->all();
            $PurchaseOrderItems_list    = PurchaseOrderItems::find()->andFilterWhere(['in','pur_number',$purNumber])->all();// 采购单明细
            $warehouse_results_list     = WarehouseResults::find()->where(['in','pur_number',$purNumber])->asArray()->all();// 入库数据
            $PurchaseOrderPayType_list  = PurchaseOrderPayType::find()->andFilterWhere(['in','pur_number',$purNumber])->asArray()->indexBy('pur_number')->all();// 付款记录

            // 转换 采购单明细
            $PurchaseOrderItems_list_tmp = [];
            foreach($PurchaseOrderItems_list as $value){
                $PurchaseOrderItems_list_tmp[$value->pur_number][] = $value;
            }
            $PurchaseOrderItems_list = $PurchaseOrderItems_list_tmp;
            unset($PurchaseOrderItems_list_tmp);
            // 转换入库单明细
            $warehouse_results_list_tmp = [];
            foreach($warehouse_results_list as $value){
                $warehouse_results_list_tmp[$value['pur_number']][$value['sku']] = $value;
            }
            $warehouse_results_list = $warehouse_results_list_tmp;
            unset($warehouse_results_list_tmp);

            foreach ( $purNumber as $num )
            {
                //明细的输出
//                $datas = PurchaseOrderItems::find()->andFilterWhere(['pur_number'=>$num])->all();
//                $pur   = PurchaseOrder::find()->andFilterWhere(['pur_number'=>$num])->one();
                $datas  = isset($PurchaseOrderItems_list[$num])?$PurchaseOrderItems_list[$num]:[];// 当前采购单明细
                $pur    = isset($PurchaseOrder_list[$num])?$PurchaseOrder_list[$num]:[];// 当前 采购单号
                if(!empty($datas)){
                    $totalprice = 0;
                    $b          = $n;
                    foreach($datas as $val){
                        //$url = Vhelper::downloadImg($val->sku,$val->product->uploadimgs);
                        if(false){// 数据导出过慢，不导出图片了
                            $img=new \PHPExcel_Worksheet_Drawing();
                            $img->setPath($url);//写入图片路径
                            $img->setHeight(50);//写入图片高度
                            $img->setWidth(100);//写入图片宽度
                            $img->setOffsetX(2);//写入图片在指定格中的X坐标值
                            $img->setOffsetY(2);//写入图片在指定格中的Y坐标值
                            $img->setRotation(1);//设置旋转角度
                            //$img->getShadow()->setVisible(true);//
                            $img->getShadow()->setDirection(50);//
                            $img->setCoordinates('D'.($n+4));//设置图片所在表格位置
                            $img->setWorksheet($objectPHPExcel->getActiveSheet());//把图片写到当前的表格中
                        }
                        $objectPHPExcel->getActiveSheet()->setCellValue('A'.($n+4) ,$pur->buyer);
                        $objectPHPExcel->getActiveSheet()->setCellValue('B'.($n+4) ,$val->pur_number);
                        $objectPHPExcel->getActiveSheet()->setCellValue('C'.($n+4) ,$val->sku);
                        $objectPHPExcel->getActiveSheet()->setCellValue('E'.($n+4) ,$pur->supplier_name);
                        $objectPHPExcel->getActiveSheet()->setCellValue('F'.($n+4) ,!empty($val->desc) ? $val->desc->title : '无');
                        $objectPHPExcel->getActiveSheet()->setCellValue('G'.($n+4) ,$val->price);
                        $objectPHPExcel->getActiveSheet()->setCellValue('H'.($n+4) ,($pur->purchas_status ==1)?$val->qty : $val->ctq);
                        $objectPHPExcel->getActiveSheet()->setCellValue('I'.($n+4) ,$val->rqy); //实际到货数量

                        //获取入库信息：入库单号(receipt_number)、良品上架数量(instock_qty_count)、入库时间(instock_date)、订单状态(purchas_status)
                        //$instock_info = WarehouseResults::getInstockInfo($val->pur_number,$val->sku);
                        $instock_info = isset($warehouse_results_list[$val->pur_number][$val->sku])?$warehouse_results_list[$val->pur_number][$val->sku]:'';
                        // 根据采购单 类型读取入库数量
                        $type = Vhelper::getNumber($val->pur_number); //1国内，2海外，3fba
                        if($type == 1){
                            $instock_qty_count = !empty($instock_info['instock_qty_count']) ? $instock_info['instock_qty_count']: 0;
                        }elseif($type == 2){
                            $instock_qty_count = !empty($instock_info['arrival_quantity']) ? $instock_info['arrival_quantity']: 0;
                        }else{
                            $instock_qty_count = !empty($instock_info['instock_qty_count']) ? $instock_info['instock_qty_count']: 0;
                            $instock_qty_count = $val->cty?:$instock_qty_count;//良品上架数量
                        }

                        $objectPHPExcel->getActiveSheet()->setCellValue('P'.($n+4) ,isset($instock_info['receipt_number'])?$instock_info['receipt_number']:'');
                        $objectPHPExcel->getActiveSheet()->setCellValue('Q'.($n+4) ,$instock_qty_count);
                        $objectPHPExcel->getActiveSheet()->setCellValue('R'.($n+4) ,isset($instock_info['instock_date'])?$instock_info['instock_date']:'');
                        $objectPHPExcel->getActiveSheet()->setCellValue('S'.($n+4) ,PurchaseOrderServices::getPurchaseStatusText($pur->purchas_status));

                        if ($pur->purchas_status ==1) {
                            $objectPHPExcel->getActiveSheet()->setCellValue('K'.($n+4) ,$val->qty*$val->price);
                        }

                        //$objectPHPExcel->getActiveSheet()->getRowDimension($n+4)->setRowHeight(100);


                        $totalprice += $val->items_totalprice;
                        $n          = $n +1;
                        $c          = $n;
                    }
                    $objectPHPExcel->setActiveSheetIndex(0)->mergeCells('J'. ($b+4) . ':J' . ($c +3));
                    //看看是否是待确认的订单，不是的话，就合并单元格
                    if ($pur->purchas_status !=1) {
                        $objectPHPExcel->setActiveSheetIndex(0)->mergeCells('K'. ($b+4) . ':K' . ($c +3));
                    }
                    $objectPHPExcel->setActiveSheetIndex(0)->mergeCells('L'. ($b+4) . ':L' . ($c +3));
                    $objectPHPExcel->setActiveSheetIndex(0)->mergeCells('M'. ($b+4) . ':M' . ($c +3));
                    $objectPHPExcel->setActiveSheetIndex(0)->mergeCells('N'. ($b+4) . ':N' . ($c +3));
                    $objectPHPExcel->setActiveSheetIndex(0)->mergeCells('O'. ($b+4) . ':O' . ($c +3));

                    $objectPHPExcel->getActiveSheet()->setCellValue('J'.($b+4) ,isset($PurchaseOrderPayType_list[$val->pur_number])?$PurchaseOrderPayType_list[$val->pur_number]['freight']: 0) ;
                    if ($pur->purchas_status !=1) {
                        $objectPHPExcel->getActiveSheet()->setCellValue('K'.($b+4) ,$totalprice);
                    }
                    $objectPHPExcel->getActiveSheet()->setCellValue('L'.($b+4) ,$pur->created_at);
                    $objectPHPExcel->getActiveSheet()->setCellValue('M'.($b+4) ,isset($warehouse_code_list[$pur->warehouse_code])?$warehouse_code_list[$pur->warehouse_code]:'');
                    $objectPHPExcel->getActiveSheet()->setCellValue('N'.($b+4) ,$pur->date_eta);
                    $shipData = [];
                    foreach ($pur->fbaOrderShip as $ship){
                        $shipData[] = $ship->express_no;
                    }
                    $objectPHPExcel->getActiveSheet()->setCellValue('O'.($b+4) ,implode(",",$shipData));
                }
            }
        }
        //设置样式
        $objectPHPExcel->getActiveSheet()->getStyle('A2:O'.($n+4))->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $objectPHPExcel->getActiveSheet()->getColumnDimension('E')->setAutoSize(true);
        ob_end_clean();
        ob_start();
        header("Content-type:application/vnd.ms-excel;charset=UTF-8");
        header('Content-Type : application/vnd.ms-excel');
        header('Content-Disposition:attachment;filename="'.'采购单产品信息表-'.date("Y年m月j日").'.xls"');
        $objWriter= \PHPExcel_IOFactory::createWriter($objectPHPExcel,'Excel5');
        $objWriter->save('php://output');

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
                    $model->save(false);
                }
                $order = PurchaseOrder::find()->where(['pur_number' => $data[0]['pur_number']])->one();
                $order->refund_status = 1;
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
            $models = PurchaseOrderReceipt::find()->where(['pur_number' => $pur_number, 'pay_status' => [1, 10]])->all();
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
                    'module' => 'FBA采购单：修改订单信息',
                    'pur_number' => $post['pur_number'],
                    'pid' => 3
                ]);
                $orderModel->shipfees_audit_status = 0;
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

                    if(empty($orderPayModel)) {
                        $orderPayModel = new PurchaseOrderPayType();
                        $orderPayModel->pur_number = $model->pur_number;
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
                    $a = $orderPayModel->save();

                    $orderModel->shipfees_audit_status = 1;
                    $b = $orderModel->save(false);


                    $model->status = 1;
                    $c = $model->save(false);

                    PurchaseLog::addLog(['pur_number' => $model->pur_number, 'note' => '经理审核订单信息修改申请']);

                    if($a && $b && $c) {
                        $is_submit = true;
                    } else {
                        $is_submit = false;
                    }
                } else { // 不通过

                    $orderModel->shipfees_audit_status = 2;
                    $b = $orderModel->save(false);

                    $model->status = 2;
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
            $args['pid'] = 3;
            $args['status'] = 0;
            $dataProvider = $searchModel->search1($args);
            return $this->render('audit-ship', [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
            ]);
        }
    }
    /**
     * 取消货物数量
     */
    public function actionCancellations()
    {
        if (Yii::$app->request->isPost)
        {
            $post_info    = Yii::$app->request->post();
            $data = Vhelper::changeData($post_info['cancel_ctq']);

            $tran = Yii::$app->db->beginTransaction();
            try{

                //比较金额
                $price_res = PurchaseOrderCancel::comparePrice($post_info);
                $compactItemsInfo = PurchaseCompactItems::getCancelJudgeCompact($post_info['pur_number'], $data); //合同单，只付了订金

                if ($price_res !== true && empty($compactItemsInfo)) {
                    Yii::$app->getSession()->setFlash('error',$price_res);
                    return $this->redirect(Yii::$app->request->referrer);
                }
                $cancel_id = PurchaseOrderCancel::saveCancel($post_info,3,$post_info['is_all_cancel']);

                foreach ($data as $k=>$v) {
                    $data[$k]['cancel_id'] = $cancel_id;
                }
                PurchaseOrderCancelSub::saveCancelSub($data,$post_info['is_all_cancel']);
                $tran->commit();

                Yii::$app->getSession()->setFlash('success','恭喜你，修改成功，请等待审核');
            }catch(HttpException $e){
                $tran->rollBack();
                Yii::$app->getSession()->setFlash('error','恭喜你，修改失败，请重新修改');
            }
            return $this->redirect(Yii::$app->request->referrer);
        } else {
            $pur_number    = Yii::$app->request->get('pur_number');
            $res = PurchaseOrderCancel::find()->where(['pur_number'=>$pur_number,'audit_status'=>1])->one();
            if (!empty($res)) {
                Yii::$app->getSession()->setFlash('error','有未审核的未到货申请，不能再申请取消');
                return $this->redirect(Yii::$app->request->referrer);
            }


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

            return $this->renderAjax('cancellations', [
                'orderInfo' => $orderInfo,
                'payInfo' => $payInfo,
                'refundInfo' => $refundInfo
            ]);
        }
    }



}
