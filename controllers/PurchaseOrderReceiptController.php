<?php

namespace app\controllers;

use app\models\PurchaseOrderReceiptLog;
use Yii;
use app\models\PurchaseOrderReceipt;
use app\models\PurchaseOrderReceiptSearch;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * PurchaseOrderReceiptController implements the CRUD actions for PurchaseOrderReceipt model.
 */
class PurchaseOrderReceiptController extends BaseController
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
     * Lists all PurchaseOrderReceipt models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new PurchaseOrderReceiptSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        if(isset(Yii::$app->request->queryParams['PurchaseOrderReceiptSearch']['buyer']) && !empty(Yii::$app->request->queryParams['PurchaseOrderReceiptSearch']['buyer'])){
            $searchModel->buyer = Yii::$app->request->queryParams['PurchaseOrderReceiptSearch']['buyer'];
        }

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single PurchaseOrderReceipt model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        return $this->renderAjax('view', [
            'model' => PurchaseOrderReceiptLog::findAll($id),
        ]);
    }

    /**
     * Creates a new PurchaseOrderReceipt model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new PurchaseOrderReceipt();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing PurchaseOrderReceipt model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing PurchaseOrderReceipt model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the PurchaseOrderReceipt model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return PurchaseOrderReceipt the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = PurchaseOrderReceipt::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
