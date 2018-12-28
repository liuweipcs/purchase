<?php

use yii\helpers\Html;
use kartik\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel app\models\SkuSalesStatisticsSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = '销量统计';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="sku-sales-statistics-index">

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?php //echo Html::a('Create Sku Sales Statistics', ['create'], ['class' => 'btn btn-success']) ?>
    </p>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            [
                'label' => 'SKU',
                'value'=> function ($model) {
                   return $model->sku;
                },
            ],
            [
                'label' => '仓库编码',
                'value'=> function ($model) {
                    return \app\services\BaseServices::getWarehouseCode($model->warehouse_code);
                },
            ],
            [
                'label' => '3天销量',
                'value'=> function ($model) {
                    return $model->days_sales_3;
                },
            ],
            [
                'label' => '7天销量',
                'value'=> function ($model) {
                    return $model->days_sales_7;
                },
            ],
            [
                'label' => '15天销量',
                'value'=> function ($model) {
                    return $model->days_sales_15;
                },
            ],
            [
                'label' => '30天销量',
                'value'=> function ($model) {
                    return $model->days_sales_30;
                },
            ],
            [
                'label' => '在途库存',
                'value'=> function ($model) {
                    return $model->on_way_stock;
                },
            ],
            [
                'label' => '可用库存',
                'value'=> function ($model) {
                    return $model->available_stock;
                },
            ],

          /*  [
                'label' => '可用库存',
                'value'=> function ($model) {
                    return $model->stock['available_stock'];
                },
            ],*/
//            [
//                'label' => '60天销量',
//                'value'=> function ($model) {
//                    return $model->days_sales_60;
//                },
//            ],
//            [
//                'label' => '90天销量',
//                'value'=> function ($model) {
//                    return $model->days_sales_90;
//                },
//            ],

             //'statistics_date',

            //['class' => 'yii\grid\ActionColumn'],
        ],

        'containerOptions' => ["style"=>"overflow:auto"], // only set when $responsive = false
        'toolbar' =>  [

            //'{export}',
        ],

        'pjax' => false,
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
            'heading'=>'<h3 class="panel-title"><i class="glyphicon glyphicon-globe"></i> Countries</h3>',
            'type'=>'success',
            // 'after'=>Html::a('<i class="glyphicon glyphicon-repeat"></i> 刷新', ['index'], ['class' => 'btn btn-info']),
            //'footer'=>true
        ],

    ]); ?>
</div>
