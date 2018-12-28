<?php
namespace app\modules\manage\controllers;

use app\controllers\BaseController;
use app\modules\manage\models\SupplierManageConfigSearch;
use app\modules\manage\models\SupplierPermission;
use app\modules\manage\models\SupplierPermissionItems;
use Yii;

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/6/19
 * Time: 18:15
 */
class SupplierUserController extends BaseController{
    public function actionIndex(){
        $searchModel = new SupplierManageConfigSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams,false);
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
        return $this->render('index');
    }

    public function actionUpdate($supplier_code){
        $oldPerimissionItems = SupplierPermissionItems::find()->select('permission_id')->where(['supplier_code'=>$supplier_code,'status'=>1])->column();
        $permissions = SupplierPermission::getTreeDatas();
        if(Yii::$app->request->isAjax&&Yii::$app->request->isGet){
            return $this->renderAjax('update',['permissions'=>$permissions,'oldPerimissionItems'=>$oldPerimissionItems]);
        }
        if(Yii::$app->request->isPost){
            $newPermissionDatas = Yii::$app->request->getBodyParam('Permission');
            $response = SupplierPermissionItems::savePermission($newPermissionDatas,$oldPerimissionItems,$supplier_code);
            Yii::$app->getSession()->setFlash($response['status'],$response['message']);
            return $this->redirect(Yii::$app->request->referrer);
        }
    }

}