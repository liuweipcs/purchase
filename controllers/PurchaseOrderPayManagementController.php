<?php

namespace app\controllers;

use app\config\Vhelper;
use app\models\PurchaseOrderPayWater;
use app\models\PurchaseOrderPayWaterSearch;
use Yii;
use yii\filters\VerbFilter;

/**
 * Created by PhpStorm.
 * User: ztt
 * Date: 2017/3/23 0023
 * Time: 18:41
 */
class PurchaseOrderPayManagementController extends BaseController
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
        $searchModel = new PurchaseOrderPayWaterSearch();
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
            'model' => PurchaseOrderPayWater::findone(['id'=>$id]),
        ]);
    }



}
