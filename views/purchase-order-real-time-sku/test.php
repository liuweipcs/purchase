<?php

use yii\helpers\Html;
use kartik\grid\GridView;
use yii\widgets\Pjax;
use app\services\PurchaseOrderServices;
use app\models\PurchaseOrderItems;
use app\services\BaseServices;

/* @var $this yii\web\View */
/* @var $searchModel app\models\OverseasWarehouseGoodsTaxRebateSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', '实时SKU查询');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="overseas-warehouse-goods-tax-rebate-index">

    <h1><?php // Html::encode($this->title) ?></h1>
    <?php  echo $this->render('_test_search', ['model' => $searchModel]); ?>
    <p class="clearfix"></p>
    <p>
        <?php Html::a(Yii::t('app', 'Create Tax Rebate'), ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        //'filterModel' => $searchModel,
        'pager'=>[
            //'options'=>['class'=>'hidden']//关闭分页
            'firstPageLabel'=>"首页",
            'prevPageLabel'=>'上一页',
            'nextPageLabel'=>'下一页',
            'lastPageLabel'=>'末页',
        ],
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'sku',
//            'stock',
            'on_way_stock',
            /*[
                'attribute' => 'on_way_stock',
                "format" => "raw",
                'value'=>
                    function($model){
                        return PurchaseOrderItems::getAvailableStock($model->sku,$model->warehouse_code);
                    },
            ],*/
            'available_stock',
            /*[
                'attribute' => 'warehouse_code',
                "format" => "raw",
                'value'=>
                    function($model){
                        $data = !empty($model->warehouse_code)?BaseServices::getWarehouseCode($model->warehouse_code):"";
                        return $data;
                    },
            ],*/
            [
                'attribute' => 'warehouse_code',
                "format" => "raw",
                'value'=>
                    function($model){
                        if (!empty(BaseServices::getWarehouseCode($model->warehouse_code)) && is_string(BaseServices::getWarehouseCode($model->warehouse_code))) {
                            $data = BaseServices::getWarehouseCode($model->warehouse_code);
                        } else {
                            $data = $model->warehouse_code;
                        }
                        return  $data;
                    },
            ],
//                'left_stock',

            [
                'label'=>'采购单状态',
                'attribute' => 'ids',
                "format" => "raw",
                'value'=>
                    function($model){
                        return PurchaseOrderItems::getOrderInfo($model->sku,$model->warehouse_code);
                    },
            ],
//            'created_at',
        ],

        'containerOptions' => ["style"=>"overflow:auto"], // only set when $responsive = false
        'toolbar' =>  [

            //'{export}',
        ],

        'pjax' => true,
        'bordered' => true,
        'striped' => false,
        'condensed' => true,
        'responsive' => true,
        'hover' => true,
        'floatHeader' => false,
        'showPageSummary' => false,

        'exportConfig' => [
            GridView::EXCEL => [],
        ],
        'panel' => [
            //'heading'=>'<h3 class="panel-title"><i class="glyphicon glyphicon-globe"></i> Countries</h3>',
            'type'=>'success',
            // 'after'=>Html::a('<i class="glyphicon glyphicon-repeat"></i> 刷新', ['index'], ['class' => 'btn btn-info']),
            //'footer'=>true
        ],

    ]); ?>
</div>