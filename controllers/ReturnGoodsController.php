<?php

namespace app\controllers;

use app\config\Vhelper;
use Yii;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use app\models\LogisticsCarrier;
use app\models\ReturnGoods;
use app\models\ReturnGoodsSearch;
use app\services\BaseServices;
use app\models\PurchaseOrder;
use app\models\PurchaseQc;
use app\models\PurchaseOrderReceipt;

/**
 * ExchangeGoodsController implements the CRUD actions for ExchangeGoods model.
 */
class ReturnGoodsController extends BaseController
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
     * Lists all ExchangeGoods models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new ReturnGoodsSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * 生成退款单
     * @param int $id
     * @param string $pur_number
     * @return mixed
     */
    public function actionRefund($id, $pur_number,$sku)
    {

        $purchaseQcModel=new PurchaseQc();
        $purchaseOrderModel = new PurchaseOrder();
        $purchaseOrderReceiptModel = new PurchaseOrderReceipt();
        $map = ['pur_number' => $pur_number,'sku'=>$sku];
        $purchaseQcInfo = $purchaseQcModel->find()->where($map)->asArray()->one();
        if(empty($purchaseQcInfo))
        {
            Yii::$app->getSession()->setFlash('error','我去!数据为空,不能继续下一步',true);
            return $this->redirect(['index']);
        }
        $purchaseOrderInfo = $purchaseOrderModel->find()->where(['pur_number' => $pur_number])->asArray()->one();
        $userInfo = BaseServices::getInfoByCondition(['username' => $purchaseQcInfo['handler']]);
        $purchaseOrderReceiptInfo = array_merge($purchaseOrderInfo, [
            'pay_price' => $purchaseQcInfo['refund_amount'],
            'applicant' => empty($userInfo['id']) ? 0 : $userInfo['id'],
            'application_time' => $purchaseQcInfo['time_handle'],
            'review_notice' => $purchaseQcInfo['note_handle']
        ]);
        //退回，供应商退回款项，更新采购单的状态
        PurchaseOrder::setQcStatus($pur_number,3,1);
        $transaction=Yii::$app->db->beginTransaction();
        $model = $this->findModel($id);
        $model->state = 3;
        $model->save();
        $res = $purchaseOrderReceiptModel->saveOne($purchaseOrderReceiptInfo);
        if($res == false){
            $errors = $purchaseOrderReceiptModel->getFirstErrors();
            $str = "</br>";
            foreach ($errors as $error){
                $str .= $error . "</br>";
            }
            Yii::$app->getSession()->setFlash('error','我去!操作失败:'.$str,true);
            $transaction->rollBack();
            return $this->redirect(['index']);
        }
        $transaction->commit();
        Yii::$app->getSession()->setFlash('success',"恭喜你，生成退款单成功,请等待财务收款！",true);
        return $this->redirect(['index']);
    }







    /**
     * Finds the ReturnGoods model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return ReturnGoods the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = ReturnGoods::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

    /**
     *添加物流页面
     * @return string
     */
    public function actionAddlogistic(){
        $model         = new ReturnGoods();
        $map['id']     = Yii::$app->request->get('id');
        $data=$model->find()->where($map)->asArray()->one();
        return $this->renderAjax('addlogistic', [
            'data' => $data,

        ]);
    }

    /**
     * 添加物流
     * @return \yii\web\Response
     */
    public function actionLogisticsave(){

        $data = Yii::$app->request->post();
        $datas =[
            'note'             => $data['note'],
            'state'            => 1,
        ];
        $status = ReturnGoods::updateAll($datas,['pur_number'=>$data['id']]);
        if($status){
            Yii::$app->getSession()->setFlash('success',"恭喜你,物流添加成功！",true);
            return $this->redirect(['index']);
        }else{
            Yii::$app->getSession()->setFlash('error',"我去,物流添加失败啦！请联系管理员",true);
            return $this->redirect(['index']);
        }
    }
}
