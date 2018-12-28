<?php
namespace app\controllers;

use app\models\SupplierAuditResults;
use Yii;
use yii\filters\VerbFilter;

/**
 * 供应商审核列表 控制器
 * Class ProductRepackageController
 * @package app\controllers
 */
class SupplierAuditResultsController extends BaseController
{

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
     * Lists all PurchaseOrder models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel    = new SupplierAuditResults();
        $params         = Yii::$app->request->queryParams;
        $dataProvider   = $searchModel->search($params);
        $data = [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ];
        return $this->render('index', $data);
    }
}
