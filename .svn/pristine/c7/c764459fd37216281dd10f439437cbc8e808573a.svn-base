<?php
namespace app\controllers;

use app\models\CustomerService;
use app\models\CustomerServiceSearch;
use Yii;
use yii\web\Controller;
class CustomerServiceController extends Controller
{

    public function actionIndex()
    {
        $searchModel = new CustomerServiceSearch();
        $args = Yii::$app->request->queryParams;
        $dataProvider = $searchModel->search($args);
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider
        ]);

    }



}