<?php

namespace app\controllers;

use app\config\Vhelper;
use Yii;
use app\services\BaseServices;
use app\models\PurchaseOrder;
use app\models\PurchaseQc;
use app\models\PurchaseQcSearch;
use app\models\PurchaseOrderReceipt;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use app\models\ReturnGoods;
use app\models\ExchangeGoods;

/**
 * PurchaseQcAuditController implements the CRUD actions for PurchaseQc model.
 */
class PurchaseQcAuditController extends BaseController
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
     * Lists all PurchaseQc models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new PurchaseQcSearch();
        $dataProvider = $searchModel->search1(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }


    /**
     * @desc 查看详情
     * @author Jimmy
     * @date 2017-04-24 20:15:11
     */
    public function actionViewDetail(){
        $model=new PurchaseQc();
        $map['express_no']=Yii::$app->request->get('express_no');
        $map['pur_number']=Yii::$app->request->get('pur_number');
        $map['handle_type']=Yii::$app->request->get('handle_type')?Yii::$app->request->get('handle_type'):null;
        $data=$model->find()->where($map)->asArray()->all();
        return $this->renderAjax('view-detail', [
            'data' => $data,
        ]);
    }
    /**
     * @desc 审核异常
     * @author Jimmy
     * @date 2017-04-24 20:42:11
     */
    public function actionAuditDetail(){
        $model=new PurchaseQc();
        $map['express_no']=Yii::$app->request->get('express_no');
        $map['pur_number']=Yii::$app->request->get('pur_number');
        $map['handle_type']=Yii::$app->request->get('handle_type');
        $data=$model->find()->where($map)->asArray()->all();
        return $this->renderAjax('audit-detail', [
            'data' => $data,
        ]);
    }
    /**
     * @desc 保存异常审核
     * @author Jimmy
     * @date 2017-04-24 20:51:11
     */
    public function actionAuditSave(){
        $is_pass=Yii::$app->request->post('is_pass');
        if($is_pass==1){
            $this->pass();
        }else{
            $this->nopass();
        }
    }
    /**
     * @desc 审核通过
     * @return string 成功的信息提示
     * @author Jimmy
     * @date 2017-04-26
     */
    protected function pass(){
        $data=Yii::$app->request->post('PurchaseQcAudit');
        $model=new PurchaseQc();
        $purchaseModel = new PurchaseOrder();
        $purchaseOrderReceiptModel = new PurchaseOrderReceipt();
        $transaction=Yii::$app->db->beginTransaction();

        foreach ($data as $key=>$val)
        {
            $returnGoodsModel = new ReturnGoods();
            $exchangeGoodsModel = new ExchangeGoods();
            $map['id']=$key;
            $vals['qc_status']='4';//已审核
            $vals['auditor']=Yii::$app->user->identity->username;
            $vals['time_audit']=date('Y-m-d h:i:s');
            if(false==$model->updateAll($vals,$map))
            {
                Yii::$app->getSession()->setFlash('error','我去！操作失败,请联系管理员1:',true);
                $transaction->rollBack();
                return $this->redirect(['index']);
            }
            $purchaseQcInfo = $model->find()->where(['id'=>$key])->asArray()->one();
            //Vhelper::dump($purchaseQcInfo);
            //处理采购单的qc状态
             PurchaseOrder::setQcStatus($purchaseQcInfo['pur_number'],$purchaseQcInfo['handle_type']);

            if ($purchaseQcInfo['handle_type'] == 2) {//审核通过而且需要退款 则生成收款通知
                $purchaseOrderMap = ['pur_number' => $purchaseQcInfo['pur_number']];
                $purchaseOrderInfo = $purchaseModel->find()->where($purchaseOrderMap)->asArray()->one();
                $userInfo = BaseServices::getInfoByCondition(['username' => $purchaseQcInfo['handler']]);
                $purchaseOrderInfo = array_merge($purchaseOrderInfo, [
                    'pay_price' => $purchaseQcInfo['refund_amount'],
                    'applicant' => empty($userInfo['id']) ? 0 : $userInfo['id'],
                    'application_time' => $purchaseQcInfo['time_handle'],
                    'review_notice' => $purchaseQcInfo['note_handle']
                ]);
                $res = $purchaseOrderReceiptModel->saveOne($purchaseOrderInfo);
                if($res == false){
                    $errors = $purchaseOrderReceiptModel->getFirstErrors();
                    $str = "</br>";
                    foreach ($errors as $error){
                        $str .= $error . "</br>";
                    }
                    Yii::$app->getSession()->setFlash('error','我去！操作失败,请联系管理员2:'.$str,true);
                    $transaction->rollBack();
                    return $this->redirect(['index']);
                }
            } elseif ($purchaseQcInfo['handle_type'] == 3) {//审核通过而且需要退货 则生成收货通知
                $res = $returnGoodsModel->returnSave($purchaseQcInfo);
                if($res == false){
                    $errors = $returnGoodsModel->getFirstErrors();
                    $str = "</br>";
                    foreach ($errors as $error){
                        $str .= $error . "</br>";
                    }
                    Yii::$app->getSession()->setFlash('error','我去！操作失败,请联系管理员3:'.$str,true);
                    $transaction->rollBack();
                    return $this->redirect(['index']);
                }
            } elseif ($purchaseQcInfo['handle_type'] == 4) {//审核通过而且需要换货 则生成收货通知
                $res = $exchangeGoodsModel->exchangeSave($purchaseQcInfo);
                if($res == false){
                    $errors = $exchangeGoodsModel->getFirstErrors();
                    $str = "</br>";
                    foreach ($errors as $error){
                        $str .= $error . "</br>";
                    }
                    Yii::$app->getSession()->setFlash('error','我去！操作失败,请联系管理员4:'.$str,true);
                    $transaction->rollBack();
                    return $this->redirect(['index']);
                }
            }
        }
        $transaction->commit();
        Yii::$app->getSession()->setFlash('success',"恭喜你，操作成功！",true);
        return $this->redirect(['index']);
    }
    /**
     * @desc 审核不通过
     * @author Jimmy
     * @date 2017-04-26 
     */
    protected function nopass(){
        $data=Yii::$app->request->post('PurchaseQcAudit');

        foreach ($data as $key=>$val){
            PurchaseQc::updateAll(['qc_status'=>5,'note_audit'=>$val['note_audit']], 'pur_number = :pur_number', [':pur_number' => $val['pur_number']]);

        }

        Yii::$app->getSession()->setFlash('success',"恭喜你，操作成功！",true);
        return $this->redirect(['index']);
    }
}
