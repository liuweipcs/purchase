<?php

namespace app\controllers;

use app\config\Vhelper;
use app\models\PurchaseNote;
use app\models\PurchaseOrderItems;
use app\models\PurchaseOrderReceipt;
use app\models\PurchaseRefunds;
use app\models\Stock;
use Yii;
use app\models\PurchaseOrder;
use app\models\PurchaseOrderSearch;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use app\models\PurchaseLog;
/**
 * Created by PhpStorm.
 * User: ztt
 * Date: 2017/3/23 0023
 * Time: 18:41
 */
class PurchaseOrderAbnormalAuditController extends BaseController
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
        $searchModel = new PurchaseOrderSearch();
        $dataProvider = $searchModel->search4(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
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
            $ordersitmes = PurchaseOrder::find()->joinWith(['purchaseOrderItems','supplier'])->where($map)->asArray()->all();
            foreach ($ordersitmes as $k => $v) {
                if($v['refund_status']==6){
                    Yii::$app->getSession()->setFlash('error','取消货物数量退款的订单：未提交 不能审核');
                    return $this->redirect(['index']);
                }
            }

            return $this->renderAjax('batch-review', [
                'model' =>$ordersitmes,
                'name'  =>Yii::$app->request->get('name'),
            ]);
        }else{
            $id                                 = Yii::$app->request->post()['PurchaseOrder']['id'];
            $purchas_status                     = Yii::$app->request->post()['PurchaseOrders']['purchas_status'];
            $ordersitmes                        = PurchaseOrder::find()->where(['in','id',$id])->all();
            $model_receipt = new  PurchaseOrderReceipt();
            $transaction=\Yii::$app->db->beginTransaction();
            try {
                foreach ($ordersitmes as $k => $ordersitem) {
                    if ($purchas_status == 3) {
                        $ordersitem->refund_status = 1;
                        $purchaseOrderInfo         = [
                            'pay_price'         => PurchaseRefunds::find()->select('refunds_amount')->where(['pur_number' => $ordersitem->pur_number])->scalar(),
                            'pur_number'        => $ordersitem->pur_number,
                            'applicant'         => Yii::$app->user->id,
                            'application_time'  => date('Y-m-d H:i:s'),
                            'review_notice'     => $ordersitem->confirm_note,
                            'pay_name'          => '供应商退款',
                            'supplier_code'     => $ordersitem->supplier_code,
                            'settlement_method' => $ordersitem->account_type,
                            'pay_type'          => $ordersitem->pay_type,
                            'currency_code'     => $ordersitem->currency_code,
                            'step'              => 4,
                        ];
                        $model_receipt->saveOne($purchaseOrderInfo);
                    } elseif ($purchas_status == 4) {
                        if ($ordersitem->refund_status == 7) {
                            //取消货物数量，退回至上一个状态
                            $ordersitem->refund_status = 6;
                        } else {
                            //审核退回标志
                            $ordersitem->refund_status = 5;
                        }
                    }
                    $ordersitem->save(false);
                }
                $transaction->commit();
            }catch (Exception $e) {
                //回滚
                $transaction->rollBack();
            }
            Yii::$app->getSession()->setFlash('success','恭喜你,审核成功');
            return $this->redirect(['index']);
        }
    }

    /**编辑 取消货物数量的订单
     * @param $id
     * @return string|\yii\web\Response
     * @throws \yii\db\Exception
     */
    public function actionEdit($id){
        if (Yii::$app->request->isPost)
        {
            $transaction=\Yii::$app->db->beginTransaction();
            try {
                $order = Yii::$app->request->post()['PurchaseOrder'];
                $order_s = PurchaseOrder::find()->where(['pur_number' => $order['pur_number']])->one();

                //取消部分数量,需要审核
                $data =[
                    'pur_number'     => $order['pur_number'],
                    'refunds_amount' => $order['money1'],
                ];
                //修改状态
                $order_s->confirm_note=$order['confirm_note'];
                if ($order['refund_status'] == 7) {
                    $order_s->refund_status=7;
                }
                $order_s->save(false);

                $purchase_refunds = PurchaseRefunds::find()->where(['pur_number'=>$order['pur_number']])->one();
                if (!empty($purchase_refunds)) {
                    $purchase_refunds->refunds_amount = $order['money1'];
                    $purchase_refunds->save(false);
                } else {
                    $PurchaseRefunds = new PurchaseRefunds();
                    $PurchaseRefunds::SaveOne($data);
                }

                Yii::$app->getSession()->setFlash('success','恭喜你,提交成功');
                $transaction->commit();
            } catch (Exception $e) {
                Yii::$app->getSession()->setFlash('error','恭喜你,提交失败');
                $transaction->rollBack();
            }
            return $this->redirect(Yii::$app->request->referrer);
        } else{
            $pur_number = Yii::$app->request->get('pur_number');
            $PurchaseOrderItems = PurchaseOrderItems::find()->where(['pur_number'=>$pur_number])->all();
            $PurchaseNote       = PurchaseNote::find()->select('note')->where(['pur_number'=>$pur_number])->scalar();
            $model              = PurchaseOrder::find()->where(['pur_number'=>$pur_number])->one();
            return $this->renderAjax('edit',['pur_number' =>$pur_number,'model'=>$model,'PurchaseOrderItems'=>$PurchaseOrderItems,'PurchaseNote'=>$PurchaseNote]);
        }
    }

}
