<?php

use yii\helpers\Html;

use kartik\grid\GridView;
use Yii\helpers\Url;
use yii\bootstrap\Modal;
use app\config\Vhelper;
use yii\widgets\ActiveForm;
use \app\models\SupplierUpdateApply;
use \app\services\SupplierServices;
use kartik\select2\Select2;
use yii\web\JsExpression;
/* @var $this yii\web\View */
/* @var $searchModel app\models\SupplierGoodsSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', '供应商KPI绩效考核');
$this->params['breadcrumbs'][] = $this->title;
?>

<div >
    <?= $this->render('_search', ['model' => $searchModel]); ?>
    <p class="clearfix"></p>
    <div>
        <?= Html::a('导出', '#',['class' => 'btn btn-info export','id'=>'export'])?>
    </div>
    <div >
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            //'filterModel' => $searchModel,
            "options" => ["class" => "grid-view","style"=>"overflow:auto", "id" => "grid"],
            'filterSelector' => "select[name='".$dataProvider->getPagination()->pageSizeParam."'],input[name='".$dataProvider->getPagination()->pageParam."']",
            'pager'=>[
                'options'=>['class' => 'pagination','style'=> "display:block;"],
                'class'=>\liyunfang\pager\LinkPager::className(),
                'pageSizeList' => [20, 50, 100, 200],
                'firstPageLabel'=>"首页",
                'prevPageLabel'=>'上一页',
                'nextPageLabel'=>'下一页',
                'lastPageLabel'=>'末页',
            ],
            'columns' => [
                [
                    'class' => 'kartik\grid\CheckboxColumn',
                    'name'=>"id" ,
                    'checkboxOptions' => function ($model, $key, $index, $column) {
                        return ['value' => $model->id];
                    }
                ],
                [
                    'label'=>'供应商名称',
                    'format'=>'raw',
                    'value'=> function ($model, $key, $index, $column) {
                        return !empty($model->supplier) ? $model->supplier->supplier_name : '';
                    }
                ],
                [
                    'label'=>'统计月份',
                    'format'=>'raw',
                    'value'=>function($model){
                        return date('Y-m',strtotime($model->month));
                    }

                ],
                [
                    'label'=>'账期',
                    'attribute'=>'settlement',
                    'format'=>'raw',
                    'value'=> function ($model, $key, $index, $column) {
                        return $model->settlement;
                    }
                ],
                [
                    'label'=>'PO数量',
                    'attribute'=>'purchase_times',
                    'format'=>'raw',
                    'value'=> function ($model, $key, $index, $column) {
                        return $model->purchase_times;
                    }
                ],
                [
                    'label'=>'采购金额',
                    'attribute'=>'purchase_total',
                    'format'=>'raw',
                    'value'=> function ($model, $key, $index, $column) {
                        return $model->purchase_total;
                    }
                ],
                [
                    'label'=>'SKU总下单批次',
                    'attribute'=>'sku_purchase_times',
                    'format'=>'raw',
                    'value'=> function ($model, $key, $index, $column) {
                        return $model->sku_purchase_times;
                    }
                ],
                [
                    'label'=>'SKU准交批次',
                    'attribute'=>'sku_punctual_times',
                    'format'=>'raw',
                    'value'=> function ($model, $key, $index, $column) {
                        return $model->sku_punctual_times;
                    }
                ],
                [
                    'label'=>'SKU准交率',
                    'attribute'=>'punctual_rate',
                    'format'=>'raw',
                    'value'=> function ($model, $key, $index, $column) {
                        return $model->punctual_rate == 0 ? 0 : $model->punctual_rate.'%';
                    }
                ],
                [
                    'label'=>'SKU异常批次',
                    'attribute'=>'sku_exception_times',
                    'format'=>'raw',
                    'value'=> function ($model, $key, $index, $column) {
                        return $model->sku_exception_times;
                    }
                ],
                [
                    'label'=>'SKU异常率',
                    'attribute'=>'excep_rate',
                    'format'=>'raw',
                    'value'=> function ($model, $key, $index, $column) {
                        return $model->excep_rate ==0 ? 0 : $model->excep_rate.'%';
                    }
                ],
                [
                    'label'=>'SKU海外异常批次',
                    'attribute'=>'uploadimgss',
                    'format'=>'raw',
                    'value'=> function ($model, $key, $index, $column) {
                        return 0;
                    }
                ],
                [
                    'label'=>'SKU海外异常率',
                    'attribute'=>'uploadimgss',
                    'format'=>'raw',
                    'value'=> function ($model, $key, $index, $column) {
                        return 0;
                    }
                ],
                [
                    'label'=>'SKU降价金额',
                    'attribute'=>'sku_down_total',
                    'format'=>'raw',
                    'value'=> function ($model, $key, $index, $column) {
                        return $model->sku_down_total;
                    }
                ],
                [
                    'label'=>'SKU降价率',
                    'attribute'=>'sku_down_rate',
                    'format'=>'raw',
                    'value'=> function ($model, $key, $index, $column) {
                        return ($model->sku_down_rate*100).'%';
                    }
                ],
                [
                    'label'=>'SKU涨价金额',
                    'attribute'=>'sku_up_total',
                    'format'=>'raw',
                    'value'=> function ($model, $key, $index, $column) {
                        return $model->sku_up_total;
                    }
                ],
                [
                    'label'=>'SKU涨价率',
                    'attribute'=>'sku_up_rate',
                    'format'=>'raw',
                    'value'=> function ($model, $key, $index, $column) {
                        return ($model->sku_up_rate*100).'%';
                    }
                ]
            ],
            'containerOptions' => ["style"=>"overflow:auto"], // only set when $responsive = false
            'toolbar' =>  [

                //'{export}',
            ],


            'pjax' => false,
            'bordered' => true,
            'striped' => false,
            'condensed' => true,
            'responsive' => false,
            'hover' => false,
            'floatHeader' => false,
            'showPageSummary' => false,

            'exportConfig' => [
                GridView::EXCEL => [],
            ],
            'panel' => [
                //'heading'=>'<h3 class="panel-title"><i class="glyphicon glyphicon-globe"></i> Countries</h3>',
                'type'=>'success',
                //'before'=>false,
                //'after'=>false,
                //'after'=>Html::a('<i class="glyphicon glyphicon-repeat"></i> 刷新', ['index'], ['class' => 'btn btn-info']),
                //'footer'=>true
            ],
        ]);?>
    </div>
</div>

<?php
Modal::begin([
    'id' => 'create-modal',
    'header' => '<h4 class="modal-title">系统信息</h4>',
    'footer' => '<a href="#" class="btn btn-primary closes" data-dismiss="modal">Close</a>',
    'size'=>'modal-lg',
    'options'=>[
        //'data-backdrop'=>'static',//点击空白处不关闭弹窗

    ],
]);
Modal::end();

$exportUrl = Url::toRoute('export');
$js = <<<JS
    $(document).on('click', '#export', function () {
        var supplier_code = $("#supplierkpicaculte-supplier_code").val();
        var month = $("#supplierkpicaculte-month").val();
        var url = "{$exportUrl}"+'?supplier_code='+supplier_code+'&month='+month;
        $(this).attr('href',url);
    });
JS;
$this->registerJs($js);
?>
