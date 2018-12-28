<?php

namespace app\controllers;

use app\config\Vhelper;
use app\models\PurchaseAbnormals;
use app\models\PurchaseOrder;
use Yii;
use app\models\PurchaseQc;
use app\models\PurchaseQcSearch;
use app\controllers\BaseController;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use app\models\PurchaseOrderReceipt;
/**
 * PurchaseQcController implements the CRUD actions for PurchaseQc model.
 */
class PurchaseQcController extends BaseController
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class'   => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all PurchaseQc models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel  = new PurchaseQcSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel'  => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }


    /**
     * @desc 查看详情
     * @author Jimmy
     * @date 2017-04-24 14:44:11
     */
    public function actionViewDetail()
    {
        $model              = new PurchaseQc();
        $map['express_no']  = Yii::$app->request->get('express_no');
        $map['pur_number']  = Yii::$app->request->get('pur_number');
        $map['handle_type'] = Yii::$app->request->get('handle_type') ? Yii::$app->request->get('handle_type') : null;
        $data               = $model->find()->where($map)->asArray()->all();
        return $this->renderAjax('view-detail', [
            'data' => $data,
        ]);
    }

    /**
     * @desc 处理异常
     * @author Jimmy
     * @date 2017-04-24 17:20:11
     */
    public function actionHandleDetail()
    {
        $model             = new PurchaseQc();
        $map['express_no'] = Yii::$app->request->get('express_no');
        $map['pur_number'] = Yii::$app->request->get('pur_number');
        //$map['handle_type']=Yii::$app->request->get('handle_type')?Yii::$app->request->get('handle_type'):null;
        $data = $model->find()->where($map)->asArray()->all();
        return $this->renderAjax('handle-detail', [
            'data' => $data,
        ]);
    }

    /**
     * @desc 保存异常处理
     * @author Jimmy
     * @date 2017-04-24 17:40:11
     */
    public function actionHandleSave()
    {
        $data = Yii::$app->request->post('PurchaseQc');
        //$Purchase =new PurchaseOrder();
        //$transaction=Yii::$app->db->beginTransaction();
        //$purchaseOrderReceiptModel = new PurchaseOrderReceipt();
        if (is_array($data)) {
            foreach ($data as $key => $val) {
                $model              = new PurchaseQc;
                $map['id']          = $key;
                $val['qc_status']   = '3';//如果是不良品上架，需要进行多审核一步
                $val['handler']     = Yii::$app->user->identity->username;
                $val['note_center'] = $val['note_handle'];
                $val['is_receipt']  = $val['is_receipt'];
                //$val['refund_amount'] = !empty($val['refund_amount']) ? $val['refund_amount'] : '0';
                $val['time_handle'] = date('Y-m-d H:i:s');
                if (false == $model->updateAll($val, $map)) {

                    Yii::$app->getSession()->setFlash('error', '我去！操作失败,请联系管理员:', true);
                    //$transaction->rollBack();
                    return $this->redirect(['index']);
                }
                $purchaseQcInfo = $model->find()->where(['id'=>$key])->asArray()->one();
                PurchaseAbnormals::UpdateOne($purchaseQcInfo['pur_number']);
                $this->savePurchaseStatus($purchaseQcInfo);
                //Vhelper::dump($purchaseQcInfo);
                //处理采购单的qc状态
                //PurchaseOrder::setQcStatus($purchaseQcInfo['pur_number'],$purchaseQcInfo['handle_type']);
                //$arr=['2','3'];
                /* if (in_array($purchaseQcInfo['handle_type'],$arr))
                 {//需要退款 则生成收款通知
                     $purchaseOrderMap = ['pur_number' => $purchaseQcInfo['pur_number']];
                     $purchaseOrderInfo = $Purchase->find()->where($purchaseOrderMap)->asArray()->one();
                     $userInfo = BaseServices::getInfoByCondition(['username' => $purchaseQcInfo['handler']]);
                     $purchaseOrderInfo = array_merge($purchaseOrderInfo, [
                         'pay_price'        => $purchaseQcInfo['refund_amount'],
                         'applicant'        => empty($userInfo['id']) ? 0 : $userInfo['id'],
                         'application_time' => $purchaseQcInfo['time_handle'],
                         'review_notice'    => $purchaseQcInfo['note_handle'],
                         'pay_name'         => $purchaseQcInfo['note_handle'],
                         'step'             => 2,
                     ]);
                     $res = $purchaseOrderReceiptModel->saveOne($purchaseOrderInfo);
                     if($res == false)
                     {

                         Yii::$app->getSession()->setFlash('error','我去！操作失败,请联系管理员2:',true);
                         $transaction->rollBack();
                         return $this->redirect(['index']);
                     }
                 }*/
            }

            //$transaction->commit();
        } else {
            exit('数据出现了异常,请联系管理员');
        }
        Yii::$app->getSession()->setFlash('success', "恭喜你，操作成功！", true);
        return $this->redirect(['index']);
    }

    /**
     * 更新采购状态
     * @param $status
     */
    protected function savePurchaseStatus($status)
    {

        PurchaseOrder::updateAll(['qc_abnormal_status' =>8], 'pur_number = :pur_number', [':pur_number' => $status['pur_number']]);

    }
}
