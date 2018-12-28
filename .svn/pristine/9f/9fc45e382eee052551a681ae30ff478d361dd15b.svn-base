<?php

namespace app\controllers;

use app\models\SupplierLog;
use app\models\SupplierSettlement;
use app\models\SupplierSettlementLog;
use Yii;
use yii\filters\VerbFilter;
use app\config\MyExcel;


/**
 * Created by PhpStorm.
 * User: wr
 * Date: 2017/12/27
 * Time: 16:28
 */
class SupplierSettlementController extends BaseController
{

    /**
     * 供应商结算方式列表
     */
    public function  actionIndex()
    {
        $searchModel = new SupplierSettlement();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * 结算方式添加
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        if(Yii::$app->request->isPost){
            $params   = Yii::$app->request->getBodyParam('SupplierSettlement');
            $response = SupplierSettlement::saveSettlement($params,'create');
            SupplierLog::saveSupplierLog('supplier-settlement/create',Yii::$app->user->identity->username.$response['message'].implode(',',$params));
            Yii::$app->getSession()->setFlash($response['status'],$response['message']);
            return $this->redirect(Yii::$app->request->referrer);
        }
        if(Yii::$app->request->isAjax){
            $model = new SupplierSettlement();
            return $this->renderAjax('create',['model'=>$model]);
        }
    }

    /**
     * @param $id
     * @return \yii\web\Response
     * 结算方式禁用
     */
    public function actionDelete($id){
        $response = SupplierSettlement::changeStatus($id);
        SupplierLog::saveSupplierLog('supplier-settlement/delete',Yii::$app->user->identity->username.$response['message'].$id);
        Yii::$app->getSession()->setFlash($response['status'],$response['message']);
        return $this->redirect(Yii::$app->request->referrer);
    }

    /**
     * @param $id
     * @return string|\yii\web\Response
     * 结算方式编辑
     */
    public function actionUpdate($id){
        if(Yii::$app->request->isAjax){
            $model = SupplierSettlement::find()->andWhere(['id'=>$id])->one();
            return $this->renderAjax('update',['model'=>$model]);
        }
        if(Yii::$app->request->isPost){
            $params   = Yii::$app->request->getBodyParam('SupplierSettlement');
            $response = SupplierSettlement::saveSettlement($params,'update');
            SupplierLog::saveSupplierLog('supplier-settlement/update',Yii::$app->user->identity->username.$response['message'].implode(',',$params));
            Yii::$app->getSession()->setFlash($response['status'],$response['message']);
            return $this->redirect(Yii::$app->request->referrer);
        }
    }

    public function actionLog(){
        $searchModel = new SupplierSettlementLog();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('log', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionUpdateLog($ids){
        $id = explode(',',$ids);
        $model = SupplierSettlementLog::find()->where(['in','id',$id])->all();
        if(Yii::$app->request->isPost){
            $formData= Yii::$app->request->getBodyParam('SupplierSettlementLog');
            $response = SupplierSettlementLog::updateLog($formData);
            SupplierLog::saveSupplierLog('supplier-settlement',implode(',',array_keys($formData)).$response['message']);
            Yii::$app->getSession()->setFlash($response['status'],$response['message']);
            return $this->redirect(Yii::$app->request->referrer);
        }
        if(Yii::$app->request->isAjax){
            return $this->renderAjax('log-update',['model'=>$model]);
        }
    }
}
