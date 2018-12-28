<?php

namespace app\controllers;

use Yii;
use app\models\PurchaseOrderItems;
use app\models\PurchaseOrderItemsSearch;
use app\models\purchaseOrderItemsStock;
use app\models\ArrivalRecord;
use app\models\PurchaseTicketOpen;
use app\models\StockLog;
use app\models\DeclareCustoms;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * TaxTrackingController implements the CRUD actions for PurchaseOrderItems model.
 */
class TaxTrackingController extends Controller
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
     * Lists all PurchaseOrderItems models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new PurchaseOrderItemsSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }
    /**
     * 查看所有记录详情
     */
    public function actionAllDetails()
    {
        $get = Yii::$app->request->get();
        $all_info = StockLog::find()->where(['pur_number'=>$get['pur_number'], 'sku'=>$get['sku']])->asArray()->all();
        if (empty($all_info)) return '信息不存在';
        return $this->renderAjax('all-details', ['all_details_info'=>$all_info]);
    }
    /**
     * 入库详情
     */
    public function actionInstockDetails()
    {
        $get = Yii::$app->request->get();
        $instock_info = StockLog::getInstockInfo($get['pur_number'], $get['sku']);
        if (empty($instock_info)) return '信息不存在';
        return $this->renderAjax('instock-details', ['instock_info'=>$instock_info]);
    }
    /**
     * 发货详情
     */
    public function actionDeliveryDetails()
    {
        $get = Yii::$app->request->get();
        $delivery_info = StockLog::getDeliveryInfo($get['pur_number'], $get['sku']);
        if (empty($delivery_info)) return '信息不存在';
        return $this->renderAjax('delivery-details', ['delivery_info'=>$delivery_info]);
    }
    /**
     * 报关详情
     */
    public function actionCustomsDetails()
    {
        $get = Yii::$app->request->get();
        $customs_info = DeclareCustoms::find()
            ->where(['pur_number'=>$get['pur_number'], 'sku' => $get['sku']])
            ->asArray()->all();
        if (empty($customs_info)) return '信息不存在';
        return $this->renderAjax('customs-details', ['customs_info'=>$customs_info]);
    }
    /**
     * 开票详情
     */
    public function actionTicketDetails()
    {
        $get = Yii::$app->request->get();
        $poen_info = PurchaseTicketOpen::getOpenInfo($get['sku'],$get['pur_number']);
        if (empty($poen_info)) return '信息不存在';
        return $this->renderAjax('ticket-details', ['poen_info'=>$poen_info]);
    }
    /**
     * 库龄详情
     */
    public function actionReservoirDetails()
    {
        $get = Yii::$app->request->get();
        $reservoir_info = StockLog::getInstockInfo($get['pur_number'],$get['sku']);
        if (empty($reservoir_info)) return '信息不存在';
        return $this->renderAjax('reservoir-details', ['reservoir_info'=>$reservoir_info]);
    }

    /**
     * Deletes an existing PurchaseOrderItems model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param string $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the PurchaseOrderItems model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return PurchaseOrderItems the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = PurchaseOrderItems::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
