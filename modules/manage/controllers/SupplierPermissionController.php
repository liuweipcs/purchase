<?php
namespace app\modules\manage\controllers;

use app\controllers\BaseController;
use app\modules\manage\models\SupplierManageConfigSearch;
use app\modules\manage\models\SupplierPermission;
use Yii;

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/6/19
 * Time: 18:15
 */
class SupplierPermissionController extends BaseController{
    public function actionIndex(){
        $searchModel = new SupplierPermission();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams,false);
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
        return $this->render('index');
    }

    public function actionCreate(){
        $model = new SupplierPermission();
        if(Yii::$app->request->isAjax&&Yii::$app->request->isGet){
            return $this->renderAjax('create',['model'=>$model]);
        }
        if(Yii::$app->request->isPost){
            $response = SupplierPermission::savePermission($model,Yii::$app->request->getBodyParam('SupplierPermission'));
            Yii::$app->getSession()->setFlash($response['status'],$response['message']);
            return $this->redirect(Yii::$app->request->referrer);
        }
    }

    public function actionUpdate($id){
        $model = SupplierPermission::find()->where(['id'=>$id])->one();
        if(empty($model)){
            Yii::$app->end('当前权限不存在');
        }
        if(Yii::$app->request->isAjax){
            return $this->renderAjax('update',['model'=>$model]);
        }
        if(Yii::$app->request->isPost){
            $response = SupplierPermission::savePermission($model,Yii::$app->request->getBodyParam('SupplierPermission'));
            Yii::$app->getSession()->setFlash($response['status'],$response['message']);
            return $this->redirect(Yii::$app->request->referrer);
        }
    }

}