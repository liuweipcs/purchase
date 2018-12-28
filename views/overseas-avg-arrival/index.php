<?php

use yii\helpers\Html;

use kartik\grid\GridView;
use Yii\helpers\Url;
use yii\bootstrap\Modal;
use app\services\SupplierGoodsServices;
/* @var $this yii\web\View */
/* @var $searchModel app\models\SupplierGoodsSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', '海外仓权均交期');
$this->params['breadcrumbs'][] = $this->title;
?>

<div >
    <?= $this->render('_search', ['model' => $searchModel]); ?>
    <p class="clearfix"></p>
    <?= Html::a('导出', '#',['class' => 'btn btn-info export','id'=>'export-csv']) ?>
    <?= Html::a(Yii::t('app', '批量修改'), ['ex-arrive'], ["class" => "btn btn-success ",'data-toggle' => 'modal','data-target' => '#create-modal','id' => 'creates']) ?>

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
                    'label' =>'SKU',
                    'format'=>'raw',
                    'value' => function($model, $key, $index, $column){
                        return  $model->sku;
                    },

                ],
                [
                    'label' =>'产品状态',
                    'format'=>'raw',
                    'value' => function($model, $key, $index, $column){
                        return  !empty($model->product) ? SupplierGoodsServices::getProductStatus($model->product->product_status) :'';
                    },

                ],
                [
                    'label'=>'供应商',
                    'format'=>'raw',
                    'value'=>function($model){
                        return !empty($model->defaultSupplierDetail) ? $model->defaultSupplierDetail->supplier_name : '';
                    }
                ],
                [
                    'label'=>'单价',
                    'format'=>'raw',
                    'value'=>function($model){
                        return !empty($model->supplierQuote) ? $model->supplierQuote->supplierprice : '';
                    }
                ],
                [
                    'label'=>'权均交期',
                    'format'=>'raw',
                    'attribute'=>'avg_delivery_time',
                    'value'=>function($model){
                        return sprintf('%.2f',$model->avg_delivery_time/(24*60*60)).'天';
                    }
                ]

            ],
            'containerOptions' => ["style"=>"overflow:auto"], // only set when $responsive = false
            'toolbar' =>  [

                '{export}',
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

$(document).on('click','.export',function() {
        var sku      = $("#hwcavgdeliverytime-sku").val();
        var supplier = $('#hwcavgdeliverytime-supplier_code').val();
        $(this).attr('href',"{$exportUrl}?sku="+sku+'&suppliercode='+supplier);
        $(this).attr('disabled',true);
    });
$(document).on('click', '#creates', function () {

        $.get($(this).attr('href'), {id:$(this).attr('value')},
            function (data) {
                $('.modal-body').html(data);
            }
        );
    });

JS;
$this->registerJs($js);
?>
