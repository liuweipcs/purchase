<?php

namespace app\controllers;

use app\config\Vhelper;
use Yii;
use app\models\PurchaseSuggest;
use app\controllers\PurchaseSuggestController;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use app\models\PurchaseSuggestSupplierSearch;
use app\models\User;

/**
 * PurchaseSuggestSupplierController implements the CRUD actions for PurchaseSuggest model.
 */
class PurchaseSuggestSupplierController extends PurchaseSuggestController
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
        $searchModel = new PurchaseSuggestSupplierSearch();
        $map=Yii::$app->request->queryParams;
        $map['PurchaseSuggestSupplierSearch']['is_purchase']='Y';
        $dataProvider = $searchModel->search($map);
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * @desc 根据选中的仓库及供应商信息生成
     * @author Jimmy
     * @date 2017-04-18 19:21:11
     */
    public function actionCreatePurchaseSupplier()
    {
        $model=new PurchaseSuggest();
        $map['supplier_code']=Yii::$app->request->get('supplier_code');
        $map['warehouse_code']=Yii::$app->request->get('warehouse_code');
        $map['buyer']=Yii::$app->request->get('buyer');
        $map['state']='0';
        $map['purchase_type']='1';
        $map['product_status']='4';
        $flag=Yii::$app->request->get('flag');
        $data=$model->find()->where($map)->andWhere(['>','qty',0])->asArray()->all();
        $user=new User();
        $users=$user->find()->all();
        return $this->renderAjax('create-purchase', ['data' => $data,'users'=>$users,'flag'=>$flag]);
    }

}
