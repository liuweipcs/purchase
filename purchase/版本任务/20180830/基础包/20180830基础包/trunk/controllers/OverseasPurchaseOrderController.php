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
use app\models\PurchaseOrderCancel;
use app\models\PurchaseOrderCancelSub;
use yii\widgets\ListView;
use yii\data\ActiveDataProvider;

use yii\data\Pagination;
use app\models\Template;
use app\models\SupplierPaymentAccount;
use app\models\PurchasePayForm;
use app\models\PurchaseOrderRefundQuantity;
use app\models\PurchaseEstimatedTime;
class OverseasPurchaseOrderController extends BaseController
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

    // 海外仓-采购单-首页
    public function actionIndex()
    {
        $args = Yii::$app->request->queryParams;
        $searchModel = new PurchaseOrderSearch();
        $searchModel->source = isset($args['source']) ? (int)$args['source'] : 1;
        if($searchModel->source == 1) {
            $data = $searchModel->search10($args);
            return $this->render('index', [
                'searchModel' => $searchModel,
                'dataProvider' => $data,
                'source' => 1
            ]);
        } elseif($searchModel->source == 2) {
            $data = $searchModel->search8($args);
            return $this->render('index', [
                'searchModel' => $searchModel,
                'dataProvider' => $data,
                'source' => 2
            ]);
        }
    }

    /**
     * 查看产品明细
     * Displays a single PurchaseOrder model.
     * @param string $id  //$map['pur_purchase_order.pur_number'] = strpos($id,',') ? explode(',', $id) : $id;
     * @return mixed
     */
    public function actionView($id)
    {
        $model_estimated_time = new PurchaseEstimatedTime();

        $map['pur_purchase_order.pur_number']       = strpos($id,',') ? explode(',',$id):$id;
        $model= PurchaseOrder::find()->joinWith(['purchaseOrderItems'])->where($map)->one();
        if (!$model)
        {
            return '信息不存在';
        } else {
            $where['purchase_order_no']=$map['pur_purchase_order.pur_number'];
            $arriva=ArrivalRecord::find()->select('purchase_order_no,sku,name,delivery_qty,delivery_time,delivery_user,note')->where($where)->all();
            return $this->renderAjax('view', [
                'arriva' => $arriva,
                'model' => $model,
                'model_estimated_time' => $model_estimated_time,
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
        return $this->renderAjax('views', [
            'model' =>$ordersitmes,
        ]);
    }

    public function actionPrintData()
    {
        $ids = Yii::$app->request->get('ids');
        $map['pur_purchase_order.id'] = strpos($ids,',') ? explode(',',$ids):$ids;
        $ordersitmes = PurchaseOrder::find()->joinWith(['purchaseOrderItems','supplier'])->where($map)->all();
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

    public function actionPrint()
    {
        $map['pur_purchase_order.id']       = Yii::$app->request->get('id');
        $ordersitmes     = PurchaseOrder::find()->joinWith(['purchaseOrderItems','supplierContent'])->where($map)->one();
        return $this->renderPartial('prints', ['model'=>$ordersitmes]);
    }

    // 添加跟踪记录
    public function actionAddTracking()
    {
        $model = new PurchaseOrderShip();
        $tran = Yii::$app->db->beginTransaction();
        try {
            if ($model->load(Yii::$app->request->post()) && $model->save()) {
                //表修改日志-新增
                $change_content = "insert:新增id值为{$model->id}的记录";
                $change_data = [
                    'table_name' => 'pur_purchase_order_ship', //变动的表名称
                    'change_type' => '1', //变动类型(1insert，2update，3delete)
                    'change_content' => $change_content, //变更内容
                ];
                TablesChangeLog::addLog($change_data);

                $tran->commit();
                Yii::$app->getSession()->setFlash('success', '恭喜你,新增物流记录成功');
                return $this->redirect(['index']);
            } else {
                $pur_number = Yii::$app->request->get('pur_number');
                return $this->render('tracking', ['model' => $model, 'pur_number' => $pur_number]);
            }
        } catch (Exception $e) {
            $tran->rollBack();
        }
    }


    // 编辑跟踪记录
    public function  actionEditTracking()
    {
        $pur_number = Yii::$app->request->get('pur_number');
        if(Yii::$app->request->isPost) {
            $post = Yii::$app->request->post();
            $tran = Yii::$app->db->beginTransaction();
            try {
                if (isset($post['pur_number'])) {
                    $model = new PurchaseOrderShip();
                    $model->pur_number = $post['pur_number'];
                    $model->express_no = $post['express_no'];
                    $model->cargo_company_id = $post['cargo_company_id'];
                    $model->is_push = 0;
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
                    foreach ($data as $v) {
                        $mos = PurchaseOrderShip::find()->where(['id' => $v['id']])->one();
                        $mos->cargo_company_id = $v['cargo_company_id'];
                        $mos->express_no = $v['express_no'];
                        $mos->is_push = 0;

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

    // 执行请款动作
    private function executePayment($post)
    {
        $payType = (int)$post['payType'];
        $data = [
            'pay_type'      => $payType,
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

            //表修改日志-新增
            $change_content = "insert:新增id值为{$pay_model->id}的记录";
            $change_data = [
                'table_name' => 'pur_purchase_order_pay', //变动的表名称
                'change_type' => '1', //变动类型(1insert，2update，3delete)
                'change_content' => $change_content, //变更内容
            ];
            TablesChangeLog::addLog($change_data);

            $c = PurchaseOrderPayType::setPayType($purNumber, ['request_type' => $payType]); // 请款类型

            // 写采购日志
            PurchaseLog::addLog(['pur_number' => $purNumber, 'note' => '海外申请付款2.0']);
            $submit = $a && $b && $c;

            if($submit) {
                $transaction->commit();
                Yii::$app->getSession()->setFlash('success','恭喜您，申请付款成功');
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

    // 合同请款执行
    public function executeCompactPayment($data)
    {
        $payObj = new PurchaseOrderPay();
        $cpn = $data['compact_number'];
        $compact = PurchaseCompact::find()->where(['compact_number' => $cpn])->one();
        $pos = PurchaseCompact::getPurNumbers($cpn);

        // 获取申请金额
        $pay_ratio = isset($data['pay_ratio']) ? $data['pay_ratio'] : '';
        $pay_price = isset($data['pay_price']) ? $data['pay_price'] : 0;
        if(!$pay_ratio && !$pay_price) {
            Yii::$app->getSession()->setFlash('error', '申请失败，申请金额有误');
            return $this->redirect(Yii::$app->request->referrer);
        }

        if($pay_price) {
            $payinfo['ratio'] = '输入';        // 请款比例
            $payinfo['money'] = $pay_price;   // 请款金额
            $payinfo['pay_category'] = 21;    // 付款种类
        } else {
            $payinfo = Json::decode($pay_ratio);
            $p1 = PurchaseOrderPay::find()
                ->where(['pur_number' => $cpn, 'pay_category' => $payinfo['pay_category']])
                ->andWhere(['not in', 'pay_status', $this->noPayStatus])
                ->one();

            if(!empty($p1)) {
                $msg = '你已经申请过'.PurchaseOrderServices::getPayCategory($payinfo['pay_category']);
                exit($msg);
            }
        }

        if($payinfo['money'] <= 0) {
            exit('请款金额不能小于等于0');
        }

        // 合同已申请金额
        $m1 = $payObj->getCompactApplyMoney($cpn);

        // 合同实际金额
        if($compact->is_drawback == 1) {
            $m2 = $compact->real_money;
        } else {
            $m2 = $compact->real_money + $compact->freight;
        }

        // 本次申请金额 + 已申请金额
        $m3 = $m1 + $payinfo['money'];

        if(bccomp($m3, $m2, 2) == 1) {
            exit('你的申请金额已经超出了合同总金额');
        }

        // 校验
        if($payinfo['pay_category'] == 10) {
            $validate = \yii\base\DynamicModel::validateData($data, [
                ['purchase_account', 'required', 'message' => '运费走私账，账号不能为空'],
                ['pai_number', 'required', 'message' => '运费走私账，拍单号不能为空'],
            ]);
            if($validate->hasErrors()) {
                $errors = $validate->errors;
                echo '错误信息：';
                foreach($errors as $v) {
                    echo "<p>{$v[0]}</p>";
                }
                exit;
            }
        } else {
            $data['purchase_account'] = '';
            $data['pai_number'] = '';
        }

        $pay_model = new PurchaseOrderPay();
        $transaction = Yii::$app->db->beginTransaction();
        $order = PurchaseOrder::find()->where(['in', 'pur_number', $pos])->one(); // 只取合同下的一个单作为数据源

        try {
            $models = PurchaseOrder::find()->where(['in', 'pur_number', $pos])->all();

            // 待经理审核（申请合同运费时，手动输入金额时，请款单需要走业务部门审核流程）
            // 待财务审批（不同于以往的待提交，合同跳过这些流程）待经理审核
            $pay_status = (in_array($payinfo['pay_category'], [10, 21])) ? 10 : 2;

            foreach($models as $model) {
                $model->pay_status = $pay_status;

                // 表修改日志-更新
                $change_content = TablesChangeLog::updateCompare($model->attributes, $model->oldAttributes);
                $change_data = [
                    'table_name' => 'pur_purchase_order', // 变动的表名称
                    'change_type' => '2', // 变动类型(1insert，2update，3delete)
                    'change_content' => $change_content, // 变更内容
                ];
                TablesChangeLog::addLog($change_data);
                $model->save(false);
            }

            $pay_model->pur_number         = $cpn; // 在原有的订单号字段上，写上合同号
            $pay_model->requisition_number = CommonServices::getNumber('PP');
            $pay_model->pay_status         = $pay_status;                // 待财务审批
            $pay_model->pay_ratio          = $payinfo['ratio'];          // 请款比例
            $pay_model->pay_price          = $payinfo['money'];          // 请款金额
            $pay_model->js_ratio           = $data['js_ratio'];          // 结算比例（拉取合同的）
            $pay_model->pay_category       = $payinfo['pay_category'];   // 付款种类
            $pay_model->purchase_account   = isset($data['purchase_account']) ? trim($data['purchase_account']) : ''; // 私账付款账号
            $pay_model->pai_number         = isset($data['pai_number']) ? trim($data['pai_number']) : ''; // 私账付款拍单号

            $pay_model->pay_name = PurchaseOrderServices::getPayCategory($payinfo['pay_category']);

            if($payinfo['pay_category'] == 10) {
                $logMsg = "申请合同运费，运费需要走私账，金额为{$payinfo['money']}";
            } else {
                $logMsg = "申请付款，付款比例为{$payinfo['ratio']}，付款金额为{$payinfo['money']}";
            }

            $pay_model->settlement_method  = $order->account_type;       // 结算方式
            $pay_model->supplier_code      = $order->supplier_code;      // 供应商编码
            $pay_model->currency           = $order->currency_code;      // 币种
            $pay_model->pay_type           = $order->pay_type;           // 支付方式
            $pay_model->create_notice      = $data['create_notice'];     // 申请备注
            $pay_model->source             = 1;                          // 标记请款单为合同请款
            $pay_model->save(false);

            // 表修改日志-新增
            $change_content = "insert:新增id值为{$pay_model->id}的记录";
            $change_data = [
                'table_name' => 'pur_purchase_order_pay', // 变动的表名称
                'change_type' => '1', // 变动类型(1insert，2update，3delete)
                'change_content' => $change_content, // 变更内容
            ];

            \app\models\TablesChangeLog::addLog($change_data);

            // 写采购日志
            PurchaseLog::addLog(['pur_number' => $cpn, 'note' => $logMsg]);

            $transaction->commit();

            if($payinfo['pay_category'] == 10) {
                Yii::$app->getSession()->setFlash('success','恭喜您，申请付款成功');
                return $this->redirect(Yii::$app->request->referrer);
            } else {
                Yii::$app->getSession()->setFlash('success','恭喜您，请款信息已经保存成功，请填写付款申请书');
                return $this->redirect(["write-payment?compact_number={$cpn}&pid={$pay_model->id}"]);
            }

        } catch(Exception $e) {
            $transaction->rollBack();
            Yii::$app->getSession()->setFlash('error','对不起，申请付款失败');
            return $this->redirect(Yii::$app->request->referrer);
        }
    }

    // 采购单和合同申请付款唯一入口
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
            $cpn = $request->get('compact_number');
            $opn = $request->get('pur_number');

            if($cpn) { // 合同付款流程

                // 校验1
                $model = PurchaseCompact::find()->where(['compact_number' => $cpn])->one();
                if(empty($model)) {
                    return $this->render('_error', [
                        'errors' => [$cpn => ['这个合同信息不存在']]
                    ]);
                }

                // 校验2
                if($model->payment_status == 4) {
                    return $this->render('_error', [
                        'errors' => [$cpn => ['这个合同信息下的订单有修改过运费或优惠额，合同需要更新后才能申请付款']]
                    ]);
                }

                // 校验3
                if(empty($model->settlement_ratio)) {
                    return $this->render('_error', [
                        'errors' => [$cpn => ['这个合同没有指定结算比例，不能申请付款']],
                    ]);
                }

                // 校验4
                $pos = PurchaseCompact::getPurNumbers($cpn);
                $orders = PurchaseOrder::find()->where(['in', 'pur_number', $pos])->all();
                $errors = [];
                foreach($orders as $v) {
                    if(in_array($v['shipfees_audit_status'], [0, -1])) {
                        $errors[$v['pur_number']][] = '这个订单修改了信息，还没有通过审核';
                    }
                    if(in_array($v['purchas_status'], $this->disabledOrder)) {
                        $errors[$v['pur_number']][] = '这个订单的状态可能是，待确认、待审核、取消、作废，这些状态的单不能请款';
                    }
                }

                if(!empty($errors)) {
                    return $this->render('_error', [
                        'errors' => $errors,
                    ]);
                }

                // 计算合同请款金额选项
                $select_ratio = PurchaseCompact::PaymentSelect3($model);

                $has_pay = PurchaseOrderPay::find()
                    ->select(['pay_price', 'pay_ratio', 'pay_status'])
                    ->where(['pur_number' => $cpn])
                    ->asArray()
                    ->All();

                if(empty($has_pay)) {
                    $has_pay = [
                        'pay_price' => 0,
                        'pay_ratio' => []
                    ];
                } else {

                    $pay_price = 0;
                    $pay_ratio = [];

                    foreach($has_pay as $v) {

                        if(in_array($v['pay_status'], $this->undonePay)) {
                            exit('你存在没有支付的申请');
                        }

                        $pay_price += $v['pay_price'];
                        $pay_ratio[] = $v['pay_ratio'];

                    }
                    $has_pay = [
                        'pay_price' => $pay_price,
                        'pay_ratio' => $pay_ratio
                    ];
                }

                return $this->render('compact-payment', [
                    'pos' => $pos,
                    'model' => $model,
                    'orders' => $orders,
                    'has_pay' => $has_pay,
                    'select_ratio' => $select_ratio
                ]);

            } else {
                // 订单请款流程
                $order_model = new PurchaseOrder();
                $pay_model   = new PurchaseOrderPay();
                $model       = $order_model->findOne(['pur_number' => $opn]);

                // 校验1
                if(is_null($model)) {
                    return '订单信息不存在';
                }

                // 校验2
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

                    if($model->purchas_status !== 6) {
                        $model->purchas_status = 7;
                    }

                    $model->pay_status = 2;

                    PurchaseLog::addLog([
                        'pur_number' => $model->pur_number,
                        'note' => '海外批量付款3.0',
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
        $id = Yii::$app->request->get('pur_number');
        $model_get= PurchaseNote::findAll(['pur_number'=>$id]);

        $tran = Yii::$app->db->beginTransaction();
        try {
            if ( $model->load(Yii::$app->request->post()) && $model->save(false))
            {
                //表修改日志-新增
                $change_content = "insert:新增id值为{$model->id}的记录";
                $change_data = [
                    'table_name' => 'pur_purchase_note', //变动的表名称
                    'change_type' => '1', //变动类型(1insert，2update，3delete)
                    'change_content' => $change_content, //变更内容
                ];
                TablesChangeLog::addLog($change_data);

                $flag = Yii::$app->request->post()['flag'];
                Yii::$app->getSession()->setFlash('success','恭喜你,新增备注成功');
                $data = [
                    '1' => Yii::$app->request->referrer,
                    '2' => Yii::$app->request->referrer,
                    '3' => Yii::$app->request->referrer,
                    '4' => Yii::$app->request->referrer,
                ];
                $tran->commit();

                return $this->redirect($data[$flag]);
            } else {
                $pur_number = Yii::$app->request->get('pur_number');
                $flag       = Yii::$app->request->get('flag');
                $models= PurchaseNote::findAll(['pur_number'=>$pur_number]);
                if(!empty($flag) && $flag > 1){
                    return $this->render('note',['model' =>$model,'pur_number'=>$pur_number,'flag'=>$flag,'models'=>$models,'model_get' =>$model_get]);
                }else{
                    return $this->renderAjax('note',['model' =>$model,'pur_number'=>$pur_number,'flag'=>$flag,'models'=>$models,'model_get' =>$model_get]);
                }
            }
        } catch (\Exception $e) {
            $tran->rollBack();
            return $this->render(Yii::$app->request->referrer);

        }
    }

    /**
     * 销售反馈
     * @return string|\yii\web\Response
     */
    public function actionAddSaleReply()
    {
        $model = new PurchaseReply();
        $id = Yii::$app->request->get('pur_number');
        $model_get= PurchaseReply::findAll(['pur_number'=>$id]);
        $tran = Yii::$app->db->beginTransaction();
        try {
            if ($model->load(Yii::$app->request->post()) && $model->save(false)) {

                //表修改日志-新增
                $change_content = "insert:新增id值为{$model->id}的记录";
                $change_data = [
                    'table_name' => 'pur_purchase_reply', //变动的表名称
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
                $referrer = \Yii::$app->session->get('referrer');
                \Yii::$app->session->remove('referrer');
                $tran->commit();
                return $this->redirect($referrer);
            } else {
                $referrer = \Yii::$app->session->get('referrer');
                if ($referrer == null) {
                    $absoluteUrl = Yii::$app->request->referrer;
                    \Yii::$app->session->set('referrer', $absoluteUrl);
                }

                $pur_number = Yii::$app->request->get('pur_number');
                $flag = Yii::$app->request->get('flag');
                $models = PurchaseReply::findAll(['pur_number' => $pur_number, 'replay_type' => 2, 'purchase_type' => 2]);

                if (!empty($flag) && $flag > 1) {
                    return $this->render('sale-reply', ['model' => $model, 'pur_number' => $pur_number, 'flag' => $flag, 'models' => $models, 'model_get' => $model_get]);
                } else {
                    return $this->renderAjax('sale-reply', ['model' => $model, 'pur_number' => $pur_number, 'flag' => $flag, 'models' => $models, 'model_get' => $model_get]);
                }
            }
        } catch (\Exception $e) {
            $tran->rollBack();
            return $this->render(Yii::$app->request->referrer);

        }
    }
    /**
     * 采购异常回复
     * @return string|\yii\web\Response
     */
    public function actionAddAbnormalReply()
    {
        $model = new PurchaseReply();
        $id = Yii::$app->request->get('pur_number');
        $model_get= PurchaseReply::findAll(['pur_number'=>$id]);
        $tran = Yii::$app->db->beginTransaction();
        try {
            if ($model->load(Yii::$app->request->post()) && $model->save(false)) {

                //表修改日志-新增
                $change_content = "insert:新增id值为{$model->id}的记录";
                $change_data = [
                    'table_name' => 'pur_purchase_reply', //变动的表名称
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
                $referrer = \Yii::$app->session->get('referrer');
                \Yii::$app->session->remove('referrer');
                $tran->commit();
                return $this->redirect($referrer);
            } else {
                $referrer = \Yii::$app->session->get('referrer');
                if ($referrer == null) {
                    $absoluteUrl = Yii::$app->request->referrer;
                    \Yii::$app->session->set('referrer', $absoluteUrl);
                }

                $pur_number = Yii::$app->request->get('pur_number');
                $flag = Yii::$app->request->get('flag');
                $models = PurchaseReply::findAll(['pur_number' => $pur_number, 'replay_type' => 1, 'purchase_type' => 2]);
                if (!empty($flag) && $flag > 1) {
                    return $this->render('abnormal-reply', ['model' => $model, 'pur_number' => $pur_number, 'flag' => $flag, 'models' => $models, 'model_get' => $model_get]);
                } else {
                    return $this->renderAjax('abnormal-reply', ['model' => $model, 'pur_number' => $pur_number, 'flag' => $flag, 'models' => $models, 'model_get' => $model_get]);
                }
            }
        } catch (\Exception $e) {
            $tran->rollBack();
            return $this->render(Yii::$app->request->referrer);

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
            $tran = Yii::$app->db->beginTransaction();
            try {
                //表修改日志-删除
                $change_content = "delete:删除id值为{$id}的记录";
                $change_data = [
                    'table_name' => 'pur_purchase_note', //变动的表名称
                    'change_type' => '3', //变动类型(1insert，2update，3delete)
                    'change_content' => $change_content, //变更内容
                ];
                TablesChangeLog::addLog($change_data);
                $model->delete();
                Yii::$app->getSession()->setFlash('success', '恭喜你,删除备注成功');
                $tran->commit();
            } catch (\Exception $e) {
                $tran->rollBack();
                return $this->render(Yii::$app->request->referrer);
            }
            return $this->redirect(Yii::$app->request->referrer);
        }else{
            Yii::$app->getSession()->setFlash('error','恭喜你,删除备注失败,你不能删除别人的备注');
            return $this->redirect(Yii::$app->request->referrer);
        }
    }

    /**
     * 编辑采购单
     */
    public  function  actionEdit()
    {

        //model = new PurchaseOrder();
        $model_receipt = new  PurchaseOrderReceipt();
        if (Yii::$app->request->isPost)
        {
            $transaction=\Yii::$app->db->beginTransaction();
            try {
                $order   = Yii::$app->request->post()['PurchaseOrder'];
                $order_s = PurchaseOrder::find()->where(['pur_number'=>$order['pur_number']])->one();

                //部分退款
                if($order['refund_status']==3 && !empty($order['money2']))
                {
                    $order_s->refund_status = $order['refund_status'];

                    $purchaseOrderInfo = [
                        'pur_number'        => $order['pur_number'],
                        'supplier_code'     => $order_s->supplier_code,
                        'settlement_method' => $order_s->account_type,
                        'pay_type'          => $order_s->pay_type,
                        'currency_code'     => $order_s->currency_code,
                        'pay_price'         => $order['money2'],
                        'applicant'         => Yii::$app->user->id,
                        'application_time'  => date('Y-m-d H:i:s'),
                        'review_notice'     => $order['confirm_note'],
                        'pay_name'          => '供应商退款',
                        'step'              => 3,
                    ];

                    $model_receipt->saveOne($purchaseOrderInfo);

                    //表修改日志-更新
                    $change_content = TablesChangeLog::updateCompare($order_s->attributes, $order_s->oldAttributes);
                    $change_data = [
                        'table_name' => 'pur_purchase_order', //变动的表名称
                        'change_type' => '2', //变动类型(1insert，2update，3delete)
                        'change_content' => $change_content, //变更内容
                    ];
                    TablesChangeLog::addLog($change_data);
                    $order_s->save(false);
                } elseif($order['refund_status']==4 && !empty($order['money1'])){
                    //全额退款,需要审核
                    $order_s->refund_status = $order['refund_status'];

                    $purchaseOrderInfo = [
                        'pur_number'        => $order['pur_number'],
                        'supplier_code'     => $order_s->supplier_code,
                        'settlement_method' => $order_s->account_type,
                        'pay_type'          => $order_s->pay_type,
                        'currency_code'     => $order_s->currency_code,
                        'pay_price'         => $order['money1'],
                        'applicant'         => Yii::$app->user->id,
                        'application_time'  => date('Y-m-d H:i:s'),
                        'review_notice'     => $order['confirm_note'],
                        'pay_name'          => '供应商退款',
                        'step'              => 4,
                    ];

                    $model_receipt->saveOne($purchaseOrderInfo);

                    /*$data =[
                        'pur_number'     => $order['pur_number'],
                        'refunds_amount' => $order['money1'],
                    ];

                    $PurchaseRefunds = new PurchaseRefunds();
                    $PurchaseRefunds::SaveOne($data);*/
                    $order_s->refund_status=$order['refund_status'];
                    $order_s->confirm_note=$order['confirm_note'];

                    //表修改日志-更新
                    $change_content = TablesChangeLog::updateCompare($order_s->attributes, $order_s->oldAttributes);
                    $change_data = [
                        'table_name' => 'pur_purchase_order', //变动的表名称
                        'change_type' => '2', //变动类型(1insert，2update，3delete)
                        'change_content' => $change_content, //变更内容
                    ];
                    TablesChangeLog::addLog($change_data);
                    $order_s->save(false);
                } elseif($order['refund_status']==10) {
                    //否则作废,得清除在途库存
                    //更新在途库存
                    //$mods = PurchaseOrderItems::getSKUc($order['pur_number']);
                    //Stock::updateStock($mods);
                    //更新采购单不再去alibaba拉去物流记录了
                    $or = PurchaseOrderOrders::find()->where(['pur_number'=>$order['pur_number']])->one();
                    if($or)
                    {
                        $or->is_request =1;

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
                    $datas =[];
                    $msg             = '在' . date('Y-m-d H:i:s') . '由' . Yii::$app->user->identity->username . '=====' . $order['pur_number'] . '进行了作废';
                    $datas['type']    = 12;
                    $datas['pid']     = '';
                    $datas['module']  = '采购单作废';
                    $datas['content'] = $msg;
                    Vhelper::setOperatLog($datas);
                    //更新付款状态为作废
                    PurchaseOrderPay::saveStatus($order['pur_number']);
                    //删除中间关系驳回采购需求
//                    PurchaseDemand::UpdateOne($order['pur_number']);
                    $demand_numbers   = PurchaseDemand::find()->where(['pur_number'=>$order['pur_number']])->all();
                    if(!empty($demand_numbers)){
                        foreach ($demand_numbers as $v){
                            PlatformSummary::updateAll(['level_audit_status'=>4,'is_push'=>0,'purchase_note'=>'采购单作废需求驳回'.Yii::$app->user->identity->username,'purchase_time'=>date('Y-m-d H:i:s',time()),'is_purchase'=>1],['demand_number'=>$v->demand_number,'level_audit_status'=>1]);
                            $v->delete();
                        }
                    }
                    $order_s->purchas_status=10;
                    //作废采购单重新推送
                    $order_s->is_push=0;
                    $order_s->confirm_note=$order['confirm_note'];

                    //表修改日志-更新
                    $change_content = TablesChangeLog::updateCompare($order_s->attributes, $order_s->oldAttributes);
                    $change_data = [
                        'table_name' => 'pur_purchase_order', //变动的表名称
                        'change_type' => '2', //变动类型(1insert，2update，3delete)
                        'change_content' => $change_content, //变更内容
                    ];
                    TablesChangeLog::addLog($change_data);
                    $order_s->save(false);
                } else {
                    Yii::$app->getSession()->setFlash('error','恭喜你,好像少了什么东西没写哦！');
                    return $this->redirect(['index']);
                }
                $transaction->commit();
                Yii::$app->getSession()->setFlash('success','恭喜你,提交成功');
                return $this->redirect(['index']);
            } catch (Exception $e) {
                $transaction->rollBack();
            }
        } else {
            $pur_number = Yii::$app->request->get('pur_number');
            $PurchaseOrderItems = PurchaseOrderItems::find()->where(['pur_number'=>$pur_number])->all();
            $PurchaseNote       = PurchaseNote::find()->select('note')->where(['pur_number'=>$pur_number])->scalar();
            $model              = PurchaseOrder::find()->where(['pur_number'=>$pur_number])->one();
            return $this->renderAjax('edit',['pur_number' =>$pur_number,'model'=>$model,'PurchaseOrderItems'=>$PurchaseOrderItems,'PurchaseNote'=>$PurchaseNote]);
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

        if (!empty($id)) {
            $model = PurchaseOrder::find()->joinWith(['purchaseOrderItems','orderShip'])->where(['in','pur_purchase_order.id',$id])->asArray()->all();
        } else {
            $searchData = \Yii::$app->session->get('PurchaseOrderSearchData');
            $searchSource = \Yii::$app->session->get('PurchaseOrderSearchSource');

            $searchModel = new PurchaseOrderSearch();
            $source = Yii::$app->request->get('source');
            $searchModel->source = $source;

            if($searchModel->source == 1) {
                $query = $searchModel->search10($searchSource,true);
            } elseif($searchModel->source == 2) {
                $query = $searchModel->search8($searchData,true);

            }
            $model = $query->joinWith(['purchaseOrderItems','orderShip'])->asArray()->all();
        }
        $objectPHPExcel = new \PHPExcel();
        $objectPHPExcel->setActiveSheetIndex(0);
        $n = 0;
        //报表头的输出
        $objectPHPExcel->getActiveSheet()->mergeCells('A1:S1'); //合并单元格
        $objectPHPExcel->getActiveSheet()->mergeCells('A1:A2');
        $objectPHPExcel->getActiveSheet()->setCellValue('A1','采购单表');  //设置表标题
        $objectPHPExcel->setActiveSheetIndex(0)->getStyle('A1')->getFont()->setSize(24); //设置字体大小
        $objectPHPExcel->setActiveSheetIndex(0)->getStyle('A1')
            ->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

        //表格头的输出  采购需求建立时间、财务审核时间、财务付款时间、销售备注
        $cell_value = ['序号','图片','采购单号','采购仓库','采购员','采购日期','SKU','货品名称','采购单价','采购数量','实际应付货款','供应商名称','付款状态','采购状态','审核时间','订单号','物流单号','入库数量', '备注','采购异常回复','销售反馈','预计到货时间','采购开票点','是否退税','实际已付金额','入库单号'];
        foreach ($cell_value as $k => $v) {
            $objectPHPExcel->setActiveSheetIndex(0)->setCellValue(chr($k+65) . '3',$v);
        }

        $objectPHPExcel->getActiveSheet()->getColumnDimension('AA')->setWidth(15);
        $objectPHPExcel->setActiveSheetIndex(0)->setCellValue('AA3','良品上架数量');
        $objectPHPExcel->getActiveSheet()->getColumnDimension('AB')->setWidth(15);
        $objectPHPExcel->setActiveSheetIndex(0)->setCellValue('AB3','入库时间');
        $objectPHPExcel->getActiveSheet()->getColumnDimension('AC')->setWidth(15);
        $objectPHPExcel->setActiveSheetIndex(0)->setCellValue('AC3','订单状态');




        //设置表头居中
        $objectPHPExcel->getActiveSheet()->getStyle('A3:Z3')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        //设置数据水平靠左和垂直居中
        $objectPHPExcel->getDefaultStyle()->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
        $objectPHPExcel->getDefaultStyle()->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);

        foreach ( $model as $v )
        {
            $purchaseNotes = PurchaseNote::getDb()
                ->createCommand("select group_concat(`note`) from " . PurchaseNote::tableName() . " where `pur_number` = :no")
                ->bindParam(':no', $v['pur_number'])
                ->queryScalar();
            foreach ($v['purchaseOrderItems'] as $c=>$vb)
            {
                //明细的输出
                $objectPHPExcel->getActiveSheet()->setCellValue('A'.($n+4) ,$n+1);
                try {
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
                } catch (\Exception $e) {
                    $objectPHPExcel->getActiveSheet()->setCellValue('B'.($n+4) ,'无图片');
                }
                $objectPHPExcel->getActiveSheet()->setCellValue('C'.($n+4) ,$v['pur_number']);
                $objectPHPExcel->getActiveSheet()->setCellValue('D'.($n+4) ,BaseServices::getWarehouseCode($v['warehouse_code']));
                $objectPHPExcel->getActiveSheet()->setCellValue('E'.($n+4) ,$v['buyer']);
                $objectPHPExcel->getActiveSheet()->setCellValue('F'.($n+4) ,$v['created_at']);
                $objectPHPExcel->getActiveSheet()->setCellValue('G'.($n+4) ,$vb['sku']);
                $objectPHPExcel->getActiveSheet()->setCellValue('H'.($n+4) ,$vb['name']);
                $objectPHPExcel->getActiveSheet()->setCellValue('I'.($n+4) ,$vb['price']);
                $objectPHPExcel->getActiveSheet()->setCellValue('J'.($n+4) ,$vb['ctq']);
                if ($v['is_drawback'] == 2) {//税金税金税金
                    //退税
                    $taxes = PurchaseOrderTaxes::getABDTaxes($vb['sku'],$vb['pur_number']);
                    $tax = bcadd(bcdiv($taxes,100,2),1,2);
                    $pay  = round($tax*$vb['price']*$vb['ctq'],2);  //数量*单价*(1+税点)
                } else {
                    $pay = $vb['ctq'] * $vb['price'];
                }
                $objectPHPExcel->getActiveSheet()->setCellValue('K'.($n+4) ,round($pay,2));
                $objectPHPExcel->getActiveSheet()->setCellValue('L'.($n+4) ,$v['supplier_name']);

                $pay_status = !empty($v['pay_status'])?strip_tags(PurchaseOrderServices::getPayStatus($v['pay_status'])):'';
                $objectPHPExcel->getActiveSheet()->setCellValue('M'.($n+4) ,$pay_status);

                $purchas_status = !empty($v['purchas_status'])?strip_tags(PurchaseOrderServices::getPurchaseStatus($v['purchas_status'])):'';
                $objectPHPExcel->getActiveSheet()->setCellValue('N'.($n+4) ,$purchas_status);
                $objectPHPExcel->getActiveSheet()->setCellValue('O'.($n+4) ,$v['audit_time']);

                $findone=PurchaseOrderOrders::findOne(['pur_number'=>$v['pur_number']]);
                $objectPHPExcel->getActiveSheet()->setCellValue('P'.($n+4) ,!empty($findone) ? $findone->order_number : '');//订单号
                $objectPHPExcel->getActiveSheet()->setCellValue('Q'.($n+4) ,!empty($v['orderShip']) ? $v['orderShip']['express_no'] : '');

                $results = WarehouseResults::getResults($vb['pur_number'],$vb['sku'],'instock_user,instock_date,arrival_quantity');
                $objectPHPExcel->getActiveSheet()->setCellValue('R'.($n+4) ,!empty($results->arrival_quantity)?$results->arrival_quantity:'0');
                $objectPHPExcel->getActiveSheet()->setCellValue('S'.($n+4) ,$purchaseNotes); //备注

                $abnomal_data = '';
                $abnomal_info = PurchaseReply::getReplyInfo($v['pur_number'],1);
                if (!empty($abnomal_info)) {
                    foreach ($abnomal_info as $ak => $av) {
                        $abnomal_data .= "回复时间：{$av['create_time']} 回复内容：{$av['note']}\r\n";
                    }
                }
                $objectPHPExcel->getActiveSheet()->setCellValue('T'.($n+4) ,$abnomal_data); //采购异常回复
                $objectPHPExcel->getActiveSheet()->getStyle('T')->getAlignment()->setWrapText(true);

                $fan_data = '';
                $fan_info = PurchaseReply::getReplyInfo($v['pur_number'],2);
                if (!empty($fan_info)) {
                    foreach ($fan_info as $fk => $fv) {
                        $fan_data .= "提交时间：{$fv['create_time']} 反馈内容：{$fv['note']}\r\n";
                    }
                }
                $objectPHPExcel->getActiveSheet()->setCellValue('U'.($n+4) ,$fan_data); //销售反馈
                $objectPHPExcel->getActiveSheet()->setCellValue('V'.($n+4) ,!empty($v['date_eta']) ? $v['date_eta'] : ''); // 预计到货时间

                $point = PurchaseOrderTaxes::getABDTaxes($vb['sku'],$vb['pur_number']) . '%';
                $objectPHPExcel->getActiveSheet()->setCellValue('W'.($n+4) ,$point); // 采购开票点

                $is_drawback = !empty($v['is_drawback']) ? ($v['is_drawback']==2?'是' : '否') : '否';
                $objectPHPExcel->getActiveSheet()->setCellValue('X'.($n+4) ,$is_drawback); // 是否退税

                $money = PurchaseOrderPay::getOrderPaidMoney($v['pur_number']); //财务已付金额（总）
                if ($v['source'] ==1) {
                    //合同
                    if ($money>0) {
                        $compact = PurchaseCompact::find()->joinWith(['purchaseCompactItems']) //purchaseCompactPay
                            ->select(['settlement_ratio']) //pay_price
                            ->where(['pur_purchase_compact_items.pur_number'=>$vb['pur_number']])
    //                        ->andWhere(['in', 'pur_purchase_order_pay.pay_status',[5,6]])
    //                        ->createCommand()->getSql();
                            ->all();

                        //结算比例
                        $settlement_ratio = 0;
                        foreach ($compact as $cv) {
                            $settlement_ratio += $cv->settlement_ratio;
                        }
                        $count = count($compact);
                        $ratio = $settlement_ratio*0.01/$count; //平均比例
                        $pay_money = $vb['ctq'] * $vb['price']*$ratio; //采购数量*单价*平均比例
                    } else {
                        $pay_money = 0;
                    }
                } else {
                    $pay_money = $vb['ctq'] * $vb['price'];
                }
                $objectPHPExcel->getActiveSheet()->setCellValue('Y'.($n+4) ,$pay_money); // 实际已付金额




                //获取入库信息：入库单号(receipt_number)、良品上架数量(instock_qty_count)、入库时间(instock_date)、订单状态(purchas_status)
                $instock_info = WarehouseResults::getInstockInfo($vb['pur_number'],$vb['sku']);
                $objectPHPExcel->getActiveSheet()->setCellValue('Z'. ($n+4) ,$instock_info['receipt_number']);
                $objectPHPExcel->getActiveSheet()->setCellValue('AA'.($n+4) ,$instock_info['instock_qty_count']);
                $objectPHPExcel->getActiveSheet()->setCellValue('AB'.($n+4) ,$instock_info['instock_date']);
                $objectPHPExcel->getActiveSheet()->setCellValue('AC'.($n+4) ,$instock_info['purchas_status']);





                if ($v['is_drawback'] == 2) {
                    //退税
                    $taxes = PurchaseOrderTaxes::getABDTaxes($vb['sku'],$vb['pur_number']);
                    $pay = $vb['ctq'] * ($vb['price'] + $taxes*0.01);   //数量*（单价+税点）
                } else {
                    $pay = $vb['ctq'] * $vb['price'];
                }
                /*$objectPHPExcel->getActiveSheet()->setCellValue('Z'.($n+4) ,$pay); // 实际应付货款*/

                $n = $n +1;
            }
        }

        for ($i = 65; $i<91; $i++) {
            $objectPHPExcel->getActiveSheet()->getColumnDimension(chr($i))->setWidth(15);
            $objectPHPExcel->getActiveSheet()->getStyle( chr($i) . "3")->getFont()->setBold(true);
        }
        $objectPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(10);
        $objectPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(10);

        //设置样式
        $objectPHPExcel->getActiveSheet()->getStyle('B2:Z'.($n+4))->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
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
            return $this->redirect(Yii::$app->request->referrer);
        }
    }

    /**
     * 审核修改运费（海外仓运费变更唯一入口）
     */
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
                    $b = $orderModel->save();

                    $model->status = 1;

                    //表修改日志-更新
                    $change_content = TablesChangeLog::updateCompare($model->attributes, $model->oldAttributes);
                    $change_data = [
                        'table_name' => 'pur_opera_log', //变动的表名称
                        'change_type' => '2', //变动类型(1insert，2update，3delete)
                        'change_content' => $change_content, //变更内容
                    ];
                    TablesChangeLog::addLog($change_data);
                    $c = $model->save();

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
                        'message' => '对不起，操作失败1'
                    ]);
                }




            } catch(\Exception $e) {
                $t->rollBack();
                return $e->getMessage();
                return json_encode([
                    'error' => 1,
                    'message' => '对不起，操作失败2'
                ]);
            }
        } else {
            $searchModel = new \app\models\OperatLogSearch();
            $args = Yii::$app->request->queryParams;
            $args['type'] = 30;
            $args['pid'] = 2;
            $args['status'] = 0;
            $dataProvider = $searchModel->search1($args);
            return $this->render('audit-ship', [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
            ]);
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
            $models = PurchaseOrderReceipt::find()->where(['pur_number' => $pur_number, 'pay_status' => [1, 10]])->all();
            if(empty($models)) {
                return '该订单没有或者没有可编辑的记录';
            }
            return $this->renderAjax('refund-handler', ['models' => $models]);
        }
    }

    /**
     * 修改订单的基本信息（实用于其他采购单）
     * 可修改：运费、优惠额、拍单号、拍单账号
     */
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
                    'module' => '海外仓采购单：修改订单信息',
                    'pur_number' => $post['pur_number'],
                    'pid' => 2
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
            $model = PurchaseOrderPayType::find()->where(['pur_number' => $pur_number])->one();
            $paylist = PurchaseOrderPay::find()->where(['pur_number' => $pur_number])->All();
            return $this->renderAjax('update-order', ['model' => $model, 'paylist' => $paylist, 'pur_number' => $pur_number]);
        }
    }



    // 创建合同
    public function actionCreateCompact()
    {
        $request = Yii::$app->request;
        $platform = Yii::$app->request->get('platform',2);
        if ($platform == 3) {
            $returnUrl = '/fba-purchase-order/index';
        } else {
            $returnUrl = 'index';
        }

        if($request->isPost) {
            $model = new PurchaseCompact();
            $POST = $request->post();
            if ($platform == 3) {
                $model->attributes = $POST['Compact'];
            } else {
                $model->attributes = $POST['Compact'];
                if(!$model->validate()) {
                    $errors = $model->errors;
                    foreach($errors as $v) {
                        echo implode(',', $v)."<br/>";
                    }
                    exit;
                }
            }
            
            $system = $POST['System']; // 系统级参数
            if($system['tid'] > 0) {
                $tran = Yii::$app->db->beginTransaction();
                try {
                    if ($platform == 3) {
                        $model->compact_number = CommonServices::getNumber('FBA-HT');
                        $model->source = 3; // 1 国内 2 海外 3 fba
                        $model->compact_status = 3; // 待审核
                    } else {
                        $model->compact_number = CommonServices::getNumber('ABD-HT');
                        $model->source = 2; // 1 国内 2 海外 3 fba
                        $model->compact_status = 3; // 待审核
                    }
                    $model->create_time = date('Y-m-d H:i:s', time());
                    $model->create_person_name = Yii::$app->user->identity->username;
                    $model->create_person_id = Yii::$app->user->id;
                    $model->tpl_id = $system['tid'];
                    $a = $model->save(false);

                    //表修改日志-新增
                    $change_content = "insert:新增id值为{$model->id}的记录";
                    $change_data = [
                        'table_name' => 'pur_purchase_compact', //变动的表名称
                        'change_type' => '1', //变动类型(1insert，2update，3delete)
                        'change_content' => $change_content, //变更内容
                    ];
                    TablesChangeLog::addLog($change_data);
                    $items = [];
                    $pos = explode(',', $system['pos']);
                    foreach($pos as $pur_number) {
                        $items[] = [
                            'compact_number' => $model->compact_number,
                            'pur_number' => $pur_number
                        ];
                    }
                    $num = Yii::$app->db->createCommand()
                        ->batchInsert('pur_purchase_compact_items', ['compact_number', 'pur_number'], $items)
                        ->execute();

                    if ($platform == 3) {
                        // $a = PurchaseOrder::updateAll(['pay_status'=>1, 'all_status'=>5, 'review_status'=>3, 'source'=>1], ['in', 'pur_number', $pos]);
                        $a = PurchaseOrder::updateAll(['source'=>1], ['in', 'pur_number', $pos]);
                    }

                    // 写合同日志
                    PurchaseLog::addLog([
                        'pur_number' => $model->compact_number,
                        'note' => '创建合同'
                    ]);

                    if($a && $num && $num == count($items)) {
                        $tran->commit();
                        Yii::$app->getSession()->setFlash('success','恭喜你，合同创建成功');
                        return $this->redirect([$returnUrl, 'source' => 2]);
                    } else {
                        $tran->rollBack();
                        Yii::$app->getSession()->setFlash('error','对不起，合同创建失败');
                        return $this->redirect([$returnUrl, 'source' => 2]);
                    }
                } catch (\Exception $e) {
                    $tran->rollBack();
                    Vhelper::dump($e->getMessage());
                    Yii::$app->getSession()->setFlash('error','对不起，程序运行错误');
                    return $this->redirect([$returnUrl, 'source' => 2]);
                }
            } else {
                exit('缺少模板id');
            }
        } else {
            $get = $request->get();
            $ids = $request->get('ids');
            $tid = isset($get['tid']) ? (int)$get['tid'] : 0; //模板id
            $_ratio = isset($get['_ratio']) ? $get['_ratio'] : null;
            if($tid > 0 && $_ratio && $ids) {
                $ids = explode(',', $ids);
                $result = PurchaseOrder::getCompactGeneralData($ids, 1);
                if($result['error'] > 0) {
                    exit($result['message']);
                }
                $data = $result['data'];//数据详情
                $data['settlement_ratio'] = $_ratio;//结算比例
                $pos = implode(',', $data['pos']);//采购单号
                $tpl = Template::findOne($tid);
                if(empty($tpl)) {
                    exit('模板数据不存在');
                }
                $tplPath = $tpl->style_code;//模板类型
                return $this->render("//template/tpls/{$tplPath}", ['data' => $data, 'pos' => $pos, 'tid' => $tid, 'platform'=>$platform]);
            } else {
                $tpls = Template::find()
                    ->where(['platform' => $platform, 'type' => 'DDHT', 'status' => 1])
                    ->asArray()
                    ->all();
                $data = ['tpls' => $tpls, 'ids' => $ids,'platform'=>$platform];

                //生成FBA合同规则
                //供应商、是否退税、支付方式、结算方式、运输方式、结算比例（只做不含税合同）
                //生成合同判断：相同供应商、是否退税、支付方式、结算方式、运输方式、结算比例
                if ($platform == 3) {
                    $result = PurchaseOrder::ContractRules($ids);
                    if($result['error'] > 0) exit($result['message']);
                    $data = array_merge($data, ['settlement_ratio'=>$result['message']]);
                }
                return $this->render('create-compact', $data);
            }
        }
    }

    // compact list
    public function actionCompactList()
    {
        $args = Yii::$app->request->queryParams;
        $searchModel = new PurchaseCompactSearch();
        $dataProvider = $searchModel->search($args, 2);
        return $this->render('compact-list', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    // print compact
    public function actionPrintCompact($id)
    {
        $model = PurchaseCompact::findOne($id);
        $items = $model->purchaseCompactItems;
        $products = [];
        foreach($items as $m) {
            $pur_number = $m->pur_number;
            $skus = PurchaseOrderItems::find()->where(['pur_number' => $pur_number])->asArray()->all();
            if($skus) {
                $products[$pur_number] = $skus;
            }
        }
        return $this->renderPartial('print-compact', ['model' => $model, 'products' => $products]);
    }

    // show compact information
    public function actionCompactView($id)
    {
        $model = PurchaseCompact::findOne($id);
        $pay_list = $model->purchaseCompactPay;
        $pos = PurchaseCompact::getPurNumbers($model->compact_number);
        $orders = PurchaseOrder::find()->where(['in', 'pur_number', $pos])->all();
        return $this->render('compact-view', [
            'model' => $model,
            'orders' => $orders,
            'pay_list' => $pay_list
        ]);
    }

    /**
     * 合同单-修改合同下的某一个采购单的运费
     */
    public function actionUpdateFreightDiscount()
    {
        $request = Yii::$app->request;
        if($request->isPost) {
            $data = $request->post();

            $cpn = $data['cpn'];
            $opn = $data['opn'];
            $model_compact = PurchaseCompact::find()->where(['compact_number' => $cpn])->one();
            $model_order = PurchaseOrder::find()->where(['pur_number' => $opn])->one();

            $content = [
                'old' => [],
                'new' => []
            ];

            $of = round($data['old_freight'], 2);
            $nf = round($data['new_freight'], 2);

            if(bccomp($of, $nf, 2) == 0) {
                exit('什么也不改，系统是不会做任何操作的！');
            }
            $content['old']['freight'] = $of;
            $content['new']['freight'] = $nf;
            $content['new']['note'] = $data['note'];

            $t = Yii::$app->db->beginTransaction();
            try {

                $a = OperatLog::AddLog([
                    'type' => 30,
                    'content' => json_encode($content),
                    'module' => '海外采购单：修改采购单运费',
                    'pur_number' => $opn,
                    'pid' => 2
                ]);

                $model_order->shipfees_audit_status = 0;
                $model_compact->payment_status = 4; // 冻结合同

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

            $compact = PurchaseCompact::find()
                ->select(['settlement_ratio', 'dj_money', 'wk_money', 'wk_total_money', 'is_drawback'])
                ->where(['compact_number' => $cpn])
                ->asArray()
                ->one();

            if($compact['is_drawback'] == 1) {

               $query = PurchaseOrderPay::find()->where(['pur_number' => $cpn])->andWhere(['not in', 'pay_status', $this->noPayStatus]);

               $p1 = $query->andWhere(['pay_category' => 20])->count();

               $p2 = $query->andWhere(['pay_category' => 11])->count();

               if($p1 > 0) {
                   return '不含税的合同，运费是计入在尾款总额的，系统检测到你已经审请了尾款，所以不能直接修改运费';
               }

               if($p2 > 0) {
                   return '你的合同已经全额付款了，不能再改了';
               }

            }

            $freight = PurchaseOrderPayType::find()->where(['pur_number' => $opn])->one();

            return $this->renderAjax('update-freight', [
                'freight' => $freight,
                'cpn' => $cpn,
                'opn' => $opn
            ]);
        }
    }

    /**
     * 刷新合同-所有订单必须在非冻结状态
     * 刷新条件：
     * 1. 海外仓合同单
     * 2. 合同payment_status == 4（冻结中）
     * 3. 合同下必须有采购单
     * 4. 合同下的采购单修改过运费，并且已经通过了审核，有一个未审核则不能刷新合同
     */
    public function actionRefreshComapct($cpn)
    {
        // 校验1
        $r = preg_match('/^ABD-HT/', $cpn);
        if(!$r) {
            exit('参数错误');
        }

        // 校验2
        $compact = PurchaseCompact::find()->where(['compact_number' => $cpn])->one();
        if($compact->payment_status !== 4) {
            exit('合同不在可刷新状态');
        }

        // 校验3
        $pos = PurchaseCompact::getPurNumbers($cpn);
        if(empty($pos)) {
            exit('合同一个采购单都没有');
        }

        // 是否是退税的合同
        $is_drawback = $compact->is_drawback ? $compact->is_drawback : 1;

        // 拉取到合同下采购单的商品总额（可能含税率）
        $orders = PurchaseOrder::find()->where(['in', 'pur_number', $pos])->all();
        $money = 0;
        foreach($orders as $m) {
            if($m->shipfees_audit_status == 0) {
                exit('你有订单修改了信息，还没有通过审核');
                exit;
            }
            $m->is_drawback = $is_drawback;
            $money += PurchaseOrder::getOverseasOrderMoney($m);
        }

        // 拉取采购单运费与优惠额
        $fd = PurchaseOrderPayType::find()
            ->select('sum(freight) as f, sum(discount) as d')
            ->where(['in', 'pur_number', $pos])
            ->asArray()
            ->one();

        $freight = $fd['f'] ? $fd['f'] : 0;
        $discount = $fd['d'] ? $fd['d'] : 0;

        // 重新获取支付计划
        $plan = PurchaseCompact::PaymentPlan3($compact->settlement_ratio, $money, $freight, $discount, $is_drawback);

        $compact->payment_status  = 2;             // 合同解冻
        $compact->product_money   = $money;        // 合同总商品额
        $compact->freight         = $freight;      // 合同总运费
        $compact->discount        = $discount;     // 合同总优惠额

        if($is_drawback == 1) {
            $compact->real_money      = $money + $freight - $discount; // 合同的实际金额
        } else {
            $compact->real_money      = $money - $discount; // 合同的实际金额
        }

        $compact->dj_money        = $plan['dj'];   // 订金
        $compact->wk_money        = $plan['wk'];   // 尾款
        $compact->wk_total_money  = $plan['wwk'];  // 尾款总额
        
        $log = [
            'pur_number' => $cpn,
            'note' => '刷新合同，读取订单金额信息，重新写入合同表'
        ];

        PurchaseLog::addLog($log);

        //表修改日志-更新
        $change_content = TablesChangeLog::updateCompare($compact->attributes, $compact->oldAttributes);
        $change_data = [
            'table_name' => 'pur_purchase_compact', //变动的表名称
            'change_type' => '2', //变动类型(1insert，2update，3delete)
            'change_content' => $change_content, //变更内容
        ];
        TablesChangeLog::addLog($change_data);
        $res = $compact->save(false);

        if($res) {
            Yii::$app->getSession()->setFlash('success','恭喜你，刷新成功，合同已更新');
            return $this->redirect(Yii::$app->request->referrer);
        } else {
            Yii::$app->getSession()->setFlash('error','对不起，刷新失败了');
            return $this->redirect(Yii::$app->request->referrer);
        }
    }

    // 作废合同下的一个采购单
    public function actionRemoveCompactOrder()
    {
        $request = Yii::$app->request;
        if($request->isPost) {
            $data = $request->post('Remove');
            $opn = $data['pur_number'];
            $bindObj = PurchaseCompactItems::find()->where(['pur_number' => $opn])->all();

            // 没有绑定关系或者一个单绑了多个合同，都不能解绑
            if(empty($bindObj) || count($bindObj) > 1) {
                exit('没有找到绑定关系，或者这个采购单同时绑定了多个合同');
            }
            $bind = $bindObj[0];
            $order = PurchaseOrder::find()->where(['pur_number' => $opn])->one();
            $compact = PurchaseCompact::find()->where(['compact_number' => $bind['compact_number']])->one();
            if(empty($order) || empty($compact)) {
                exit('采购单或合同信息不存在');
            }

            $r1 = PurchaseOrderPay::checkIsPayment($opn);
            $r2 = PurchaseOrderPay::checkIsPayment($compact['compact_number']);
            if($r1 || $r2) {
                exit('你有可用交易记录了，无法直接作废采购单');
            }

            $transaction = Yii::$app->db->beginTransaction();

            try {

                // 更新采购单不再去alibaba拉去物流记录了
                $orderType = PurchaseOrderPayType::find()->where(['pur_number' => $opn])->one();
                if($orderType) {
                    $orderType->is_request = 1;

                    //表修改日志-更新
                    $change_content = TablesChangeLog::updateCompare($orderType->attributes, $orderType->oldAttributes);
                    $change_data = [
                        'table_name' => 'pur_purchase_order_pay_type', //变动的表名称
                        'change_type' => '2', //变动类型(1insert，2update，3delete)
                        'change_content' => $change_content, //变更内容
                    ];
                    TablesChangeLog::addLog($change_data);
                    $orderType->save(false);
                }

                // 更新中间表
                PurchaseDemand::UpdateOne($order['pur_number']);

                // 修改采购单状态
                $order->purchas_status = 10;
                $order->is_push = 0;
                $order->confirm_note = $data['confirm_note'];


                //表修改日志-更新
                $change_content = TablesChangeLog::updateCompare($order->attributes, $order->oldAttributes);
                $change_data = [
                    'table_name' => 'pur_purchase_order', //变动的表名称
                    'change_type' => '2', //变动类型(1insert，2update，3delete)
                    'change_content' => $change_content, //变更内容
                ];
                TablesChangeLog::addLog($change_data);
                $order->save(false);

                // 解绑采购单
                $bind->bind = 2;

                //表修改日志-更新
                $change_content = TablesChangeLog::updateCompare($bind->attributes, $bind->oldAttributes);
                $change_data = [
                    'table_name' => 'pur_purchase_compact_items', //变动的表名称
                    'change_type' => '2', //变动类型(1insert，2update，3delete)
                    'change_content' => $change_content, //变更内容
                ];
                TablesChangeLog::addLog($change_data);
                $bind->save(false);

                // 冻结合同
                $compact->payment_status = 4;

                //表修改日志-更新
                $change_content = TablesChangeLog::updateCompare($compact->attributes, $compact->oldAttributes);
                $change_data = [
                    'table_name' => 'pur_purchase_compact', //变动的表名称
                    'change_type' => '2', //变动类型(1insert，2update，3delete)
                    'change_content' => $change_content, //变更内容
                ];
                TablesChangeLog::addLog($change_data);
                $compact->save(false);

                // 写操作日志
                $operatLog = [
                    'type' => 12,
                    'pid' => 2, // 海外
                    'module' => '合同下的采购单作废',
                    'content' => '在' . date('Y-m-d H:i:s') . '由' . Yii::$app->user->identity->username . '对采购单' . $opn . '进行了作废',
                ];
                Vhelper::setOperatLog($operatLog);

                // 写合同日志
                PurchaseLog::addLog([
                    'pur_number' => $compact['compact_number'],
                    'note' => "作废了一个采购单 {$opn}，合同被冻结"
                ]);

                $transaction->commit();
                Yii::$app->getSession()->setFlash('error','恭喜你，操作成功');
                return $this->redirect(Yii::$app->request->referrer);

            } catch (\Exception $e) {
                $transaction->rollBack();
                Yii::$app->getSession()->setFlash('error','对不起，操作失败');
                return $this->redirect(Yii::$app->request->referrer);
            }
        } else {
            $opn = $request->get('opn');
            $order = PurchaseOrder::find()->where(['pur_number' => $opn])->one();
            $data = PurchaseOrder::getOrderSkuInfo($opn);
            return $this->renderAjax('compact-remove-order', ['data' => $data, 'order' => $order]);
        }
    }

    /**
     * 更新采购到货日期
     */
    public function actionUpdateArrivalTime()
    {
        $get_info = yii::$app->request->get();
        $arrival_time = $get_info['arrival_time'];
        $sku = $get_info['sku'];
        $pur_number = $get_info['pur_number'];
        

        $format = "Y-m-d";
        $time = strtotime($arrival_time);  //转换为时间戳
        $checkstr = date($format, $time); //在转换为时间格式
        if($arrival_time == $checkstr || empty($arrival_time)){
            $status = PurchaseEstimatedTime::saveArrivalDate($sku,$pur_number,$arrival_time);
            PurchaseOrder::updateArrivalStatus($pur_number);
            return $status;
        }else{
            return -1;
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
                if ($price_res !== true) {
                    Yii::$app->getSession()->setFlash('error',$price_res);
                    return $this->redirect(Yii::$app->request->referrer);
                }
                $cancel_id = PurchaseOrderCancel::saveCancel($post_info,2,$post_info['is_all_cancel']);

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
