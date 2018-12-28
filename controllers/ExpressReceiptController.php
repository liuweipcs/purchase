<?php
namespace app\controllers;
use Yii;
use yii\helpers\Json;
use yii\web\Controller;

use app\models\UebExpressReceipt;

class ExpressReceiptController extends Controller
{

    public function actionIndex()
    {
        $searchModel = new UebExpressReceipt();
        $args = Yii::$app->request->queryParams;
        $dataProvider = $searchModel->search($args);
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider
        ]);
    }



}
