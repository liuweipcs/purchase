<?php

namespace app\controllers;

use app\config\Vhelper;
use m35\thecsv\theCsv;
use Yii;
use app\models\PurchaseTacticsAbnormal;
use app\models\PurchaseTacticsAbnormalSearch;

/**
 * 仓库MRP异常列表，显示没有正常跑完采购建议的数据
 */
class PurchaseTactics2AbnormalController extends BaseController
{

	public function actionIndex()
	{
		$searchModel = new PurchaseTacticsAbnormalSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        //Vhelper::dump($dataProvider);
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
	}

	/**
	 * excel导出
	 * @throws \yii\web\HttpException
	 */
	public function actionExport()
	{
	   set_time_limit(0);
	   ini_set('memory_limit', '1024M');
	   $id = Yii::$app->request->get('ids');
	   $id = strpos($id,',')?explode(',',$id):$id;
	   if (!empty($id))
	       $model = PurchaseTacticsAbnormal::find()->where(['in','id',$id])->all();
	   else
	   {
	   	   $model = PurchaseTacticsAbnormal::find()->all();
	   }

       $table = [
       	   'id',
       	   '用户类型',
           'sku',
           '产品名称',
           '采购员',
           '供应商',
           '仓库',
           '异常原因',
       ];

       $table_head = [];
       if(!empty($model)){
           foreach($model as $k=>$v)
           {
               $table_head[$k][] = $v->id;
               $table_head[$k][] = ($v->warehouse_type) ? PurchaseTacticsAbnormalSearch::getWarehouseName($v->warehouse_type): " ";
               $table_head[$k][] = $v->sku;
               $table_head[$k][] = $v->name;
               $table_head[$k][] = $v->buyer;
               $table_head[$k][] = $v->supplier_name;
               $table_head[$k][] = $v->warehouse_name;
               $table_head[$k][] = $v->reason;
           }
       }else{
           Yii::$app->session->setFlash('error','导出数据为空');
           return $this->redirect(Yii::$app->request->referrer);
       }
       theCsv::export([
           'header' =>$table,
           'data' => $table_head,
       ]);
	}



}	
