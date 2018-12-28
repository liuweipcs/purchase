<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/31
 * Time: 15:37
 */

namespace app\controllers;

use Yii;
use app\models\Stock;
use app\models\StockSearch;

use app\config\Vhelper;
use app\models\Product;
use app\services\BaseServices;
use app\models\PurchaseSuggest;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use app\models\PurchaseOrderItems;
use app\services\CommonServices;
use app\models\User;
use app\models\PurchaseLog;


class OverseasPurchaseOrderRealTimeSkuController extends BaseController
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
     * Lists all PurchaseSuggest models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new StockSearch();
        $dataProvider = $searchModel->search3(Yii::$app->request->queryParams);
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }
    public function actionTest()
    {
        $searchModel = new StockSearch();
        $dataProvider = $searchModel->search2(Yii::$app->request->queryParams);
        return $this->renderAjax('test', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        if ($model->load(Yii::$app->request->post()) && $model->save())
        {

            Yii::$app->getSession()->setFlash('success','恭喜你！更新成功');
            return $this->redirect(['index']);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Finds the PurchaseSuggest model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return PurchaseSuggest the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Stock::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

}