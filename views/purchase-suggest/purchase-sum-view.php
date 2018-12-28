<?php

use yii\helpers\Html;
use kartik\grid\GridView;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use app\models\ProductCategory;
use app\services\BaseServices;

$this->title = '查看导入需求';
$this->params['breadcrumbs'][] = ['label' => '采购建议', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="purchase-suggest-quantity-index">
    <div class="row">
        <div class="form-group field-purchaseorder-pur_number required col-md-12">
            <?php  echo $this->render('_ssearch', ['model' => $searchModel]); ?>
        </div>
    </div>
    <p class="clearfix"></p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
//        'filterModel'  => $searchModel,
        'options'=>[
            'id'=>'grid_purchase_addproduct',
        ],
        'pager'=>[
//            'options'=>['class'=>'hidden'],//关闭自带分页
            'firstPageLabel'=>"First",
            'prevPageLabel'=>'Prev',
            'nextPageLabel'=>'Next',
            'lastPageLabel'=>'Last',
        ],
        'columns' => [
            /*[
                'class' => 'kartik\grid\CheckboxColumn',
                'name'=>"id" ,
                'checkboxOptions' => function ($model, $key, $index, $column) {
                    return ['value' => $model->id];
                }

            ],*/
            'sku', //sku
            'platform_number', //平台号
            [
                'label'=>'采购仓',
                'attribute'=>'purchase_warehouse',
                'format'=>'raw',
                'value'=> function ($model) {
                    return !empty($model->purchase_warehouse)?BaseServices::getWarehouseCode($model->purchase_warehouse):'';
                },
            ],
            [
                'label'=>'采购数量',
                'attribute'=>'purchase_quantity',
                'value'=> function ($model) {
                    return $model->purchase_quantity;
                },
            ],
            [
                'label'=>'创建人',
                'attribute'=>'create_id',
                'value'=> function ($model) {
                    return $model->create_id;
                },
            ],
            [
                'label'=>'采购时间',
                'attribute'=>'create_time',
                'format'=>'raw',
                'value'=> function ($model) {
                    return $model->create_time;
                },
            ],
            [
                'label'=>'采购备注',
                'attribute'=>'sales_note',
                'format'=>'raw',
                'value'=> function ($model) {
                    return $model->sales_note;
                },
            ],
            [
                'label'=>'采购建议状态',
                'attribute'=>'suggest_status',
                'format'=>'raw',
                'value'=> function ($model) {
                    if ($model->suggest_status === 1) {
                        return '未使用过';
                    } elseif ($model->suggest_status === 2) {
                        return '使用过';
                    }
                },
            ],
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
            'heading'=>'<h3 class="panel-title"><i class="glyphicon glyphicon-globe"></i> Countries</h3>',
            'type'=>'success',
            // 'after'=>Html::a('<i class="glyphicon glyphicon-repeat"></i> 刷新', ['index'], ['class' => 'btn btn-info']),
            //'footer'=>true
        ],
    ]); ?>
</div>