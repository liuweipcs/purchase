<?php
namespace app\controllers;
use app\models\TablesChangeLog;
use Yii;
use yii\filters\VerbFilter;
use app\models\PurchaseOrder;
use app\models\PurchaseOrdersV2;
use app\models\PurchaseOrderPayWater;
use app\models\PurchaseOrderPay;
use app\models\PurchaseOrderPayDetail;
use app\models\PurchaseOrderPayType;
use app\models\PurchaseOrderPaySearch;
use app\models\BankCardManagement;
use app\models\PurchaseLog;

class PurchaseOrderPayController extends BaseController
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

    // index
    public function actionIndex()
    {
        $args = Yii::$app->request->queryParams;
        $searchModel = new PurchaseOrderPaySearch();
        $searchModel->source = isset($args['source']) ? (int)$args['source'] : 1;
        Yii::$app->user->identity->grade = \app\models\PurchaseUser::getUserGradeInt(Yii::$app->user->identity->id);
        if(in_array($searchModel->source, [1, 2, 3])) {
            $dataProvider = $searchModel->search3($args, 'PO');
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

                //表修改日志-更新
                $change_content = TablesChangeLog::updateCompare($pay->attributes, $pay->oldAttributes);
                $change_data = [
                    'table_name' => 'pur_purchase_order_pay', //变动的表名称
                    'change_type' => '2', //变动类型(1insert，2update，3delete)
                    'change_content' => $change_content, //变更内容
                ];
                TablesChangeLog::addLog($change_data);
                $pay->save(false);

                //表修改日志-更新
                $change_content = TablesChangeLog::updateCompare($order->attributes, $order->oldAttributes);
                $change_data = [
                    'table_name' => 'pur_purchase_order', //变动的表名称
                    'change_type' => '2', //变动类型(1insert，2update，3delete)
                    'change_content' => $change_content, //变更内容
                ];
                TablesChangeLog::addLog($change_data);
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
                //表修改日志-更新
                $change_content = TablesChangeLog::updateCompare($pay->attributes, $pay->oldAttributes);
                $change_data = [
                    'table_name' => 'pur_purchase_order_pay', //变动的表名称
                    'change_type' => '2', //变动类型(1insert，2update，3delete)
                    'change_content' => $change_content, //变更内容
                ];
                TablesChangeLog::addLog($change_data);
                $pay->save(false);

                //表修改日志-更新
                $change_content = TablesChangeLog::updateCompare($order->attributes, $order->oldAttributes);
                $change_data = [
                    'table_name' => 'pur_purchase_order', //变动的表名称
                    'change_type' => '2', //变动类型(1insert，2update，3delete)
                    'change_content' => $change_content, //变更内容
                ];
                TablesChangeLog::addLog($change_data);
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

    //采购员删除请款单
    public function actionDelete()
    {
        $request = Yii::$app->request;
        if($request->isAjax) {
            $id = $request->post('id');
            $tran = Yii::$app->db->beginTransaction();
            try {
                $pay = PurchaseOrderPay::findOne($id);
                if(!$pay) {
                    return json_encode(['error' => 1, 'message' => '要删除的数据不存在']);
                }
                if(in_array($pay->pay_status, [5, 6])) {
                    return json_encode(['error' => 1, 'message' => '请款单已经付款，不能删除']);
                }
                $order = PurchaseOrder::findOne(['pur_number' => $pay->pur_number]);
                $order->pay_status = 1; // 重置订单的支付状态为 未申请付款
                PurchaseLog::addLog([
                    'pur_number' => $pay->pur_number,
                    'note' => '采购员删除请款单>'.$id
                ]);
                $pt = PurchaseOrderPayDetail::findOne(['pur_number' => $pay->pur_number, 'requisition_number' => $pay->requisition_number]);
                if($pt) {
                    //表修改日志-删除
                    $change_content = "delete:删除id值为{$pt->id}的记录";
                    $change_data = [
                        'table_name' => 'pur_purchase_order_pay_detail', //变动的表名称
                        'change_type' => '3', //变动类型(1insert，2update，3delete)
                        'change_content' => $change_content, //变更内容
                    ];
                    TablesChangeLog::addLog($change_data);
                    $pt->delete();
                }

                //表修改日志-删除
                $change_content = "delete:删除id值为{$pay->id}的记录";
                $change_data = [
                    'table_name' => 'pur_purchase_order_pay', //变动的表名称
                    'change_type' => '3', //变动类型(1insert，2update，3delete)
                    'change_content' => $change_content, //变更内容
                ];
                TablesChangeLog::addLog($change_data);
                $pay->delete();

                //表修改日志-更新
                $change_content = TablesChangeLog::updateCompare($order->attributes, $order->oldAttributes);
                $change_data = [
                    'table_name' => 'pur_purchase_order', //变动的表名称
                    'change_type' => '2', //变动类型(1insert，2update，3delete)
                    'change_content' => $change_content, //变更内容
                ];
                TablesChangeLog::addLog($change_data);
                $order->save(false);
                $tran->commit();
                return json_encode(['error' => 0, 'message' => 'success']);
            } catch(\Exception $e) {
                $tran->rollback();
                return json_encode(['error' => 1, 'message' => 'error']);
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
            $data = PurchaseOrderPay::getCompactSkuList($model->pur_number);
            return $this->renderAjax('c-view', ['model' => $model, 'data' => $data]);
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


}


