<?php
namespace app\controllers;
use Yii;
use yii\filters\VerbFilter;
use app\config\Vhelper;
use app\models\PurchaseOrder;
use app\models\PurchaseOrderPay;
use app\models\PurchaseOrderPayDetail;
use app\models\PurchaseOrderPayType;
use app\models\PurchaseOrderPaySearch;
use app\models\PurchaseLog;
use app\models\PurchaseCompact;
use app\models\PurchaseCompactItems;
use yii\data\Pagination;
use app\models\Template;
use app\models\PurchasePayForm;
use app\models\SupplierPaymentAccount;


class OverseasPurchaseOrderPayController extends BaseController
{
    // 行为
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST']
                ]
            ]
        ];
    }

    // 列表页
    public function actionIndex()
    {
        $args = Yii::$app->request->queryParams;
        $searchModel = new PurchaseOrderPaySearch();
        $searchModel->source = isset($args['source']) ? (int)$args['source'] : 1;
        if(in_array($searchModel->source, [1, 2])) {
            $dataProvider = $searchModel->search3($args, 'ABD');
            return $this->render('index', [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
                'source' => $searchModel->source
            ]);
        } else {
            throw new \yii\web\NotFoundHttpException;
        }
    }

    // 采购员提交请款单
    public function actionSubmit()
    {
        $request = Yii::$app->request;
        if($request->isPost) {
            $id = $request->post('id');
            $tran = Yii::$app->db->beginTransaction();
            try {
                $pay = PurchaseOrderPay::findOne($id);
                $order = PurchaseOrder::findOne(['pur_number' => $pay->pur_number]);
                $pay->pay_status = 10; // 待经理审核
                $order->pay_status = 10;
                PurchaseLog::addLog([
                    'pur_number' => $pay->pur_number,
                    'note' => '采购员提交请款单>'.$id
                ]);
                $pay->save(false);
                $order->save(false);
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
            return $this->renderAjax('submit', $data);
        }
    }

    // 经理审核请款单
    public function actionAudit()
    {
        $request = Yii::$app->request;
        if($request->isPost) {
            $post = $request->post();
            $id = $post['id'];
            $tran = Yii::$app->db->beginTransaction();
            try {
                $pay_model = new PurchaseOrderPay();
                $pay = $pay_model->findOne($id);
                $order = PurchaseOrder::findOne(['pur_number' => $pay->pur_number]);
                $notice = ($post['notice'] !== '') ? $post['notice'] : '经理审核';
                $pay->review_notice = $notice;
                $pay->pay_status = $post['pay_status'];
                $pay->auditor = Yii::$app->user->id;
                $pay->review_time = date('Y-m-d H:i:s', time());
                $order->pay_status = $post['pay_status'];
                PurchaseLog::addLog([
                    'pur_number' => $pay->pur_number,
                    'note' => '经理审核请款单>'.$id
                ]);
                $pay->save(false);
                $order->save(false);
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
            return $this->renderAjax('audit', $data);
        }
    }

    // 采购员删除请款单
    public function actionDelete()
    {
        $request = Yii::$app->request;
        if($request->isAjax) {
            $id = $request->post('id');

            $pay = PurchaseOrderPay::findOne($id);

            if(!$pay) {
                return json_encode(['error' => 1, 'message' => '要删除的数据不存在']);
            }

            if(in_array($pay->pay_status, [5, 6])) {
                return json_encode(['error' => 1, 'message' => '请款单已经付款，不能删除']);
            }

            $tran = Yii::$app->db->beginTransaction();
            try {

                if($pay->source == 1) {
                    $pay->pay_status = 0;
                    $log = '采购员作废请款单>'.$id;
                    $pos = PurchaseCompact::getPurNumbers($pay->pur_number);
                    if(!empty($pos)) {
                        PurchaseOrder::updateAll(['pay_status' => 1], ['in', 'pur_number', $pos]);
                    }
                    $pay->save(false);
                } else {
                    $order = PurchaseOrder::findOne(['pur_number' => $pay->pur_number]);
                    $order->pay_status = 1;
                    $order->save(false);
                    $log = '采购员删除请款单>'.$id;
                    $pay->delete();
                }

                PurchaseLog::addLog(['pur_number' => $pay->pur_number, 'note' => $log]);

                $tran->commit();
                return json_encode(['error' => 0, 'message' => 'success']);
            } catch(\Exception $e) {
                $tran->rollback();
                return json_encode(['error' => 1, 'message' => $e->getMessage()]);
            }
        }
    }

    // 查看请款单
    public function actionView()
    {
        $request = Yii::$app->request;
        $id = $request->get('id');
        $model = PurchaseOrderPay::findOne($id);
        if($model->source == 1) {
            $model = PurchaseOrderPay::findOne($id);
            $data = PurchaseOrderPay::getCompactPayDataV2($model);
            $compact = PurchaseCompact::find()
                ->select([
                    'settlement_ratio',
                    'product_money',
                    'freight',
                    'discount',
                    'real_money',
                    'dj_money',
                    'wk_money',
                    'wk_total_money',
                    'is_drawback',
                    'supplier_name'
                ])
                ->where(['compact_number' => $model->pur_number])
                ->one();
            if(!$data || !$compact) {
                return '请款单或合同不存在';
            }
            return $this->renderAjax('c-view', ['model' => $model, 'data' => $data, 'compact' => $compact]);
        } else {
            $data = $this->getPayInfo($id);
        }
        if(!$data) {
            return '数据不存在';
        }
        return $this->renderAjax('view', $data);
    }

    // 获取请款单信息
    public function getPayInfo($id)
    {
        $pay_model = new PurchaseOrderPay();
        $pay_info  = $pay_model->getPayDetailById($id);
        if(!$pay_info)
            return null;
        $skuNum = [];
        if($pay_info['sku_list']) {
            $sku_list = json_decode($pay_info['sku_list'],1);
            foreach($sku_list as $k => $v) {
                $skuNum[$v['sku']] = $v['num'];
            }
        }
        $order_model = new PurchaseOrder();
        $order_info = $order_model->getOrderDetail($pay_info['pur_number'], $skuNum);
        if(!$order_info)
            return null;
        $order_info['rpt'] = PurchaseOrderPayType::getPayType($pay_info['pur_number']);
        return ['payInfo' => $pay_info, 'orderInfo' => $order_info];
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
            PurchaseLog::addLog([
                'pur_number' => $data['compact_number'],
                'note' => "创建付款申请书，对应的请款单id为{$data['pay_id']}"
            ]);
            $res = $model->save(false);
            $go_url = Yii::$app->request->get('new') ? '/overseas-purchase-order2/payment' : 'index';
            if($res) {
                Yii::$app->getSession()->setFlash('success','恭喜你，保存付款申请书成功');
                return $this->redirect([$go_url]);
            } else {
                Yii::$app->getSession()->setFlash('success','对不起，保存付款申请书失败');
                return $this->redirect([$go_url]);
            }
        } else {
            $cpn = $request->get('compact_number');
            $pid = $request->get('pid');
            $tid = $request->get('tid');
            if(!$cpn || !$pid) {
                throw new \yii\web\NotFoundHttpException('参数错误，必须指定正确的合同号与支付单ID');
            }
            $id = PurchasePayForm::getPayForm($pid);
            if($id) {
                return '请款单已经存在可用申请书了';
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
                    'is_drawback' => $is_drawback,
                    'pos' => implode(',', $pos),
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





    // 经理审批请款单-合同请款
    public function actionCompactAudit()
    {
        $request = Yii::$app->request;
        if($request->isPost) {
            $post = $request->post();
            $id = $post['id'];
            $status = (int)$post['status'];
            $tran = Yii::$app->db->beginTransaction();
            try {
                $model = PurchaseOrderPay::findOne($id);
                $model->payment_notice = $post['payment_notice'] ? $post['payment_notice'] : '经理审核';
                $model->pay_status = $status;
                $model->approver = Yii::$app->user->id;
                $model->processing_time = date('Y-m-d H:i:s', time());
                if($model->source == 1 && preg_match('/HT/', $model->pur_number)) { // 合同类请款单，同时更新合同下所有采购的支付状态
                    $pos = PurchaseCompact::getPurNumbers($model->pur_number);
                    if($pos) {
                        PurchaseOrder::updateAll(['pay_status' => $status], ['in', 'pur_number', $pos]);
                    }
                    $logMsg = '经理审核了合同单的请款>'.$id;
                } else {
                    exit('非合同单，不走这个流程');
                }
                PurchaseLog::addLog([
                    'pur_number' => $model->pur_number,
                    'note' => $logMsg
                ]);
                $model->save(false);
                $tran->commit();
                Yii::$app->getSession()->setFlash('success','恭喜你，审核成功');
                return $this->redirect(Yii::$app->request->referrer);
            } catch(\Exception $e) {
                $tran->rollback();
                Yii::$app->getSession()->setFlash('error','对不起，审核失败');
                return $this->redirect(Yii::$app->request->referrer);
            }
        } else {
            $request = Yii::$app->request;
            $id = $request->get('id');
            $model = PurchaseOrderPay::findOne($id);
            $data = PurchaseOrderPay::getCompactPayDataV2($model);
            $compact = PurchaseCompact::find()
                ->select([
                    'settlement_ratio',
                    'product_money',
                    'freight',
                    'discount',
                    'real_money',
                    'dj_money',
                    'wk_money',
                    'wk_total_money',
                    'is_drawback'
                ])
                ->where(['compact_number' => $model->pur_number])
                ->one();
            if(!$data || !$compact) {
                return '请款单或合同不存在';
            }
            return $this->renderAjax('compact-audit', ['model' => $model, 'data' => $data, 'compact' => $compact]);
        }
    }









}


