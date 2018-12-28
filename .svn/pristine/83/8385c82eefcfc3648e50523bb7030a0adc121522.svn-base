<?php

namespace app\controllers;

use app\api\v1\models\InlandAvgDelieryTime;
use app\models\FbaAvgDelieryTime;
use m35\thecsv\theCsv;
use Yii;


class AvgDeliveryTimeController extends BaseController
{
    //fba权均交期页面
    public function actionFbaIndex()
    {
        $searchModel = new FbaAvgDelieryTime();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'title'=>'FBA SKU权均交期',
            'search_url'=>'fba-index'
        ]);
    }

    public function actionFbaExport(){
        set_time_limit(0);
        $searchParams = Yii::$app->session->get('FBA_avg_deliver_time_search');
        $searchModel = new  FbaAvgDelieryTime();
        $query = $searchModel->search($searchParams,true);
        $model =$query->all();
        $table = [
            'sku',
            '产品状态',
            '供应商',
            '权均交期(天)',
        ];

        $table_head = [];
        if(!empty($model)){
            foreach($model as $k=>$v)
            {
                $table_head[$k][]=$v->sku;
                $table_head[$k][]=is_array(\app\services\SupplierGoodsServices::getProductStatus($v->product_status)) ?'':\app\services\SupplierGoodsServices::getProductStatus($v->product_status);
                $table_head[$k][]=!empty($v->supplier_name) ? $v->supplier_name : '';
                $table_head[$k][]=$v->avg_delivery_time ==0 ? 0 : sprintf('%.2f',($v->avg_delivery_time)/(24*60*60));
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

    //国内仓权均交期页面
    public function actionInlandIndex()
    {
        $searchModel = new InlandAvgDelieryTime();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'title'=>'国内仓 SKU权均交期',
            'search_url'=>'inland-index'
        ]);
    }

    public function actionInlandExport(){
        set_time_limit(0);
        $searchParams = Yii::$app->session->get('Inland_avg_deliver_time_search');
        $searchModel = new  InlandAvgDelieryTime();
        $query = $searchModel->search($searchParams,true);
        $model =$query->all();
        $table = [
            'sku',
            '产品状态',
            '供应商',
            '权均交期(天)',
        ];

        $table_head = [];
        if(!empty($model)) {
            foreach ($model as $k => $v) {
                $table_head[$k][] = $v->sku;
                $table_head[$k][] = is_array(\app\services\SupplierGoodsServices::getProductStatus($v->product_status)) ? '' : \app\services\SupplierGoodsServices::getProductStatus($v->product_status);
                $table_head[$k][] = !empty($v->supplier_name) ? $v->supplier_name : '';
                $table_head[$k][] = $v->avg_delivery_time == 0 ? 0 : sprintf('%.2f', ($v->avg_delivery_time) / (24 * 60 * 60));
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








