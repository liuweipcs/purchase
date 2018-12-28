<?php
namespace app\controllers;
use app\models\Supplier;
use app\models\SupplierDeliverAdress;
use YII;

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/6/9
 * Time: 12:59
 */
class SupplierDeliverController extends BaseController{
    //
    public function actionIndex(){
        $searchModel = new SupplierDeliverAdress();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionAdressDetail(){
        if(Yii::$app->request->isAjax){
            $supplier_code = Yii::$app->request->getQueryParam('supplier_code');
            $supplier_name = Supplier::find()->select('supplier_name')->where(['supplier_code'=>$supplier_code])->scalar();
            $datas = SupplierDeliverAdress::find()->where(['supplier_code'=>$supplier_code,'is_visible'=>1])->all();
            return $this->renderAjax('adress-detail',['data'=>$datas,'supplier_name'=>$supplier_name,'supplier_code'=>$supplier_code]);
        }
    }

    public function actionCheckAdress(){
        if(Yii::$app->request->isAjax){
            $supplier_code = Yii::$app->request->getQueryParam('supplier_code');
            $model = SupplierDeliverAdress::find()->where(['supplier_code'=>$supplier_code])->one();
            return $this->renderAjax('adress-check',['model'=>$model]);
        }
        if(Yii::$app->request->isPost){
            $formData = Yii::$app->request->getBodyParam('SupplierAdress');
            SupplierDeliverAdress::updateAll(['is_visible'=>0,'change_reason'=>$formData['change_reason']],['supplier_code'=>$formData['supplier_code'],'is_visible'=>1,'is_check'=>1]);
            Yii::$app->getSession()->setFlash('success','处理成功');
            return $this->redirect(Yii::$app->request->referrer);
        }
    }
}