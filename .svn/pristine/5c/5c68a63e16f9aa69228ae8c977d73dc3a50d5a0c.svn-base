<?php

namespace app\controllers;

use app\config\Vhelper;
use app\models\PurchaseOrderReceiptWater;
use app\models\PurchaseOrderReceiptWaterSearch;
use Yii;
use yii\filters\VerbFilter;

/**
 * Created by PhpStorm.
 * User: ztt
 * Date: 2017/3/23 0023
 * Time: 18:41
 */
class PurchaseOrderReceiptManagementController extends BaseController
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
     * Lists all PurchaseOrderPay models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new PurchaseOrderReceiptWaterSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single PurchaseOrderPay model.
     * @param integer $id
     * @return mixed
     */
    public function actionViews()
    {
        $id = Yii::$app->request->get();
        return $this->renderAjax('view', [
            'model' => PurchaseOrderReceiptWater::findone(['id'=>$id]),
        ]);
    }



}
