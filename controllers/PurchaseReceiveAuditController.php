<?php

namespace app\controllers;

use app\config\Vhelper;
use Yii;
use app\services\BaseServices;
use app\models\PurchaseOrder;
use app\models\PurchaseReceive;
use app\models\PurchaseReceiveSearch;
use app\models\PurchaseOrderReceipt;
use app\controllers\BaseController;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use app\models\ReturnGoods;
use app\models\PurchaseQc;

/**
 * PurchaseReceiveAuditController implements the CRUD actions for PurchaseReceive model.
 */
class PurchaseReceiveAuditController extends BaseController
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
     * Lists all PurchaseReceive models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new PurchaseReceiveSearch();
        $map=Yii::$app->request->queryParams;
        $map['PurchaseReceiveSearch']['receive_status']=ArrayHelper::getValue($map, 'PurchaseReceiveSearch.receive_status', '2');
        $dataProvider = $searchModel->search($map);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * @desc 查看详情
     * @author Jimmy
     * @date 2017-04-22 12:30:11
     */
    public function actionViewDetail(){
        $model=new PurchaseReceive();
        $map['pur_number']=Yii::$app->request->get('pur_number');
        $map['handle_type']=Yii::$app->request->get('handle_type');
        $map['receive_status']=['2','3'];
        $data=$model->find()->where($map)->asArray()->all();
        return $this->renderAjax('view-detail', [
            'data' => $data,
        ]);
    }
    /**
     * @desc 审核异常
     * @author Jimmy
     * @date 2017-04-22 12:34:11
     */
    public function actionAuditDetail(){
        $model=new PurchaseReceive();
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
     * @date 2017-04-22 12:45:11
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
        $data=Yii::$app->request->post('PurchaseReceiveAudit');
        $model=new PurchaseReceive();
        $purchaseModel = new PurchaseOrder();
        $purchaseOrderReceiptModel = new PurchaseOrderReceipt();

        $transaction=Yii::$app->db->beginTransaction();
        foreach ($data as $key=>$val){
            $map['id']=$key;
            $val['receive_status']='3';//已审核
            $val['auditor']=Yii::$app->user->identity->username;
            $val['time_audit']=date('Y-m-d h:i:s');
            if(false==$model->updateAll($val, $map)){
                $errors=$model->getFirstErrors();
                $str="</br>";
                foreach ($errors as $error){
                    $str.=$error."</br>";
                }
                Yii::$app->getSession()->setFlash('error','我去！操作失败,请联系管理员:'.$str,true);
                $transaction->rollBack();
                return $this->redirect(['index']);
            }
            //审核通过而且是终止来货并退款 则生成收款通知
            $purchaseReceiveInfo = $model->find()->where($map)->asArray()->one();
            $this->savePurchaseStatus($purchaseReceiveInfo,$purchaseModel);

            if (($purchaseReceiveInfo['handle_type'] == '2' ) && $purchaseReceiveInfo['is_return'] == 1) {
                $purchaseOrderMap = ['pur_number' => $purchaseReceiveInfo['pur_number']];
                $purchaseOrderInfo = $purchaseModel->find()->where($purchaseOrderMap)->asArray()->one();
                $userInfo = BaseServices::getInfoByCondition(['username' => $purchaseReceiveInfo['handler']]);
                $purchaseOrderInfo = array_merge($purchaseOrderInfo, [
                    'pay_price' => $purchaseReceiveInfo['refund_amount'],
                    'applicant' => empty($userInfo['id']) ? 0 : $userInfo['id'],
                    'application_time' => $purchaseReceiveInfo['time_handle'],
                    'review_notice' => $purchaseReceiveInfo['note_handle']
                ]);
                $res = $purchaseOrderReceiptModel->saveOne($purchaseOrderInfo);
                if($res == false){
                    $errors = $purchaseOrderReceiptModel->getFirstErrors();
                    $str = "</br>";
                    foreach ($errors as $error){
                        $str .= $error . "</br>";
                    }
                    Yii::$app->getSession()->setFlash('error','我去！操作失败,请联系管理员:'.$str,true);
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
     * 更新采购状态
     * @param $status
     */
    protected  function  savePurchaseStatus($status,$purchaseModel)
    {
        //审核通过-下次来货完结本次采购单
        if ($status['handle_type']=='2')
        {     //部分到货不等待剩余
            $purchaseModel::updateAll(['receiving_exception_status'=>4,'qc_abnormal_status'=>1,'complete_type'=>1], 'pur_number = :pur_number', [':pur_number' => $status['pur_number']]);
        } elseif($status['handle_type']=='3'){
            //部分到货等待剩余
            $purchaseModel::updateAll(['receiving_exception_status'=>5,'qc_abnormal_status'=>1,'complete_type'=>1], 'pur_number = :pur_number', [':pur_number' => $status['pur_number']]);
        } elseif($status['handle_type']=='1'){
            //全额退款 修改采购单的收货异常状态
            $purchaseModel::updateAll(['receiving_exception_status'=>3,'qc_abnormal_status'=>1,'complete_type'=>1], 'pur_number = :pur_number', [':pur_number' => $status['pur_number']]);
            $returnGoodsModel = new ReturnGoods();
            $returnGoodsModel->returnSave($status);
            //删除qc里面的有关的采购单
            //PurchaseQc::deleteAll(['pur_number'=>$status['pur_number']]);
        } elseif($status['handle_type']=='4'){
            //入库
            $purchaseModel::updateAll(['receiving_exception_status'=>7,'qc_abnormal_status'=>1,'complete_type'=>1], 'pur_number = :pur_number', [':pur_number' => $status['pur_number']]);
        } elseif($status['handle_type']=='5'){
            //退货
            $purchaseModel::updateAll(['receiving_exception_status'=>8,'qc_abnormal_status'=>1,'complete_type'=>1], 'pur_number = :pur_number', [':pur_number' => $status['pur_number']]);
        }
    }
    /**
     * @desc 审核不通过
     * @author Jimmy
     * @date 2017-04-26 
     */
    protected function nopass()
    {
        $data=Yii::$app->request->post('PurchaseReceiveAudit');

        foreach ($data as $key=>$vals)
        {

            PurchaseReceive::updateAll(['receive_status'=>4,'note_audit'=>$vals['note_audit']], 'pur_number = :pur_number', [':pur_number' => $vals['pur_number']]);

        }
        Yii::$app->getSession()->setFlash('success',"恭喜你，操作成功！",true);
        return $this->redirect(['index']);
    }


}
