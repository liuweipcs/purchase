<?php
namespace app\controllers;
use Yii;
use yii\filters\VerbFilter;
use app\models\PurchaseOrder;
use app\models\PurchaseOrderPay;
use app\models\PurchaseOrderPayType;
use app\models\PurchaseOrderPayDetail;
use app\models\PurchaseOrderPaySearch;
use app\models\PurchaseLog;
use app\models\PurchaseCompact;
use app\models\PurchaseUser;
/**
 * Created by PhpStorm.
 * User: WangWei
 * Date: 2018/03/08
 * Time: 10:50
 */
class FbaPurchaseOrderPayController extends BaseController
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
        $searchModel = new PurchaseOrderPaySearch();
        $params = Yii::$app->request->queryParams;
        $source = isset($params['source']) ? (int)$params['source'] : 2;
        $searchModel->source = $params['source'] = $source;
        $dataProvider = $searchModel->search($params, 'FBA');
        Yii::$app->user->identity->grade = PurchaseUser::getUserGradeInt(Yii::$app->user->identity->id);

        $data = [
            'source' => $source,
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ];
        return $this->render('index', $data);

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

    //采购员删除请款单
    public function actionDelete()
    {
        $request = Yii::$app->request;
        if($request->isAjax) {
            $id = $request->post('id');
            $tran = Yii::$app->db->beginTransaction();
            try {
                $pay = PurchaseOrderPay::findOne($id);
                if(!$pay) return json_encode(['error' => 1, 'message' => '要删除的数据不存在']);
                if(in_array($pay->pay_status, [5, 6])) return json_encode(['error' => 1, 'message' => '请款单已经付款，不能删除']);

                if($pay->source == 1) {
                    //删除合同请款
                    $pay->pay_status = 0;
                    $log = '采购员作废请款单>'.$id;
                    $pos = PurchaseCompact::getPurNumbers($pay->pur_number);
                    if(!empty($pos)) {
                        PurchaseOrder::updateAll(['pay_status' => 1], ['in', 'pur_number', $pos]);
                    }
                    $pay->save(false);
                    PurchaseLog::addLog(['pur_number' => $pay->pur_number, 'note' => $log]);
                } else {
                    //删除网采请款
                    $order = PurchaseOrder::findOne(['pur_number' => $pay->pur_number]);
                    $order->pay_status = 1; // 重置订单的支付状态为 未申请付款
                    PurchaseLog::addLog([
                        'pur_number' => $pay->pur_number,
                        'note' => '采购员删除请款单>'.$id
                    ]);
                    $pt = PurchaseOrderPayDetail::findOne(['pur_number' => $pay->pur_number, 'requisition_number' => $pay->requisition_number]);
                    if($pt) $pt->delete();
                    $pay->delete();
                    $order->save(false);
                }
                
                $tran->commit();
                return json_encode(['error' => 0, 'message' => 'success']);
            } catch(\Exception $e) {
                $tran->rollback();
                return json_encode(['error' => 1, 'message' => 'error']);
            }
        }
    }

    //查看请款单
    public function actionView()
    {
        $request = Yii::$app->request;
        $id = $request->get('id');
        $data = $this->getPayInfo($id);
        if(!$data) {
            return '请款单所属的订单数据不存在';
        }
        return $this->renderAjax('view', $data);
    }

    // 编辑请款单
    public function actionEdit()
    {
        $request = Yii::$app->request;
        if($request->isPost) {
            $data = $request->post();
            $tran = Yii::$app->db->beginTransaction();

            // 驳回与反驳回对照表
            $b2b = [
                '-1' => '-1',
                '2'  => '2',
                '3'  => '2',
                '4'  => '2',
                '11' => '10',
                '10' => '10',
                '12' => '2',
            ];

            try {
                $pay_model = PurchaseOrderPay::findOne($data['id']);
                $pay_model->pay_status    = isset($b2b[$pay_model->pay_status]) ? $b2b[$pay_model->pay_status] : -1;
                $pay_model->create_notice = $data['create_notice'];
                $a = $pay_model->save(false);
                $detail_model = PurchaseOrderPayDetail::find()
                    ->where(['pur_number' => $data['pur_number'], 'requisition_number' => $data['requisition_number']])
                    ->one();
                if($detail_model) {
                    $detail_model->freight      = $data['freight'];
                    $detail_model->discount     = $data['discount'];
                    $detail_model->order_number = $data['order_number'];
                    $b = $detail_model->save(false);
                } else {
                    $detail_model = new PurchaseOrderPayDetail();
                    $detail_model->attributes = $data;
                    $b = $detail_model->save(false);
                }
                PurchaseLog::addLog([
                    'pur_number' => $data['pur_number'],
                    'note' => '采购员编辑请款单>'.$data['id']

                ]);
                $is_submit = $a && $b;
                if($is_submit) {
                    $tran->commit();
                    Yii::$app->getSession()->setFlash('success','恭喜你，操作成功');
                    return $this->redirect(Yii::$app->request->referrer);
                } else {
                    $tran->rollBack();
                    Yii::$app->getSession()->setFlash('error','对不起，操作失败');
                    return $this->redirect(Yii::$app->request->referrer);
                }
            } catch(\Exception $e) {
                $tran->rollBack();
                Yii::$app->getSession()->setFlash('error','对不起，程序出错啦');
                return $this->redirect(Yii::$app->request->referrer);
            }
        } else {
            $id = $request->get('id');
            $data = $this->getPayInfo($id);
            if(!$data) {
                return '请款单所属的订单数据不存在';
            }
            return $this->renderAjax('edit', $data);
        }
    }

    // 获取请款单信息
    public function getPayInfo($id)
    {
        $pay_model = new PurchaseOrderPay();
        $pay_info = $pay_model->getPayDetailById($id);
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

        // 获取 请款总金额、运费、优惠
        $model = PurchaseOrderPay::findOne($id);
        $price_list = PurchaseOrderPay::getPrice($model,false,$model->source,true);
        $pay_info['pay_price'] = $price_list['final_money'];
        $pay_info['freight'] = $price_list['freight'];
        $pay_info['discount'] = $price_list['discount'];

        return ['payInfo' => $pay_info, 'orderInfo' => $order_info];
    }


}


