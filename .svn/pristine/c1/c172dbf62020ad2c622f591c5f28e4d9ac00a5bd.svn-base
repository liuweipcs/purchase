<?php

use yii\helpers\Html;
use kartik\grid\GridView;
use yii\bootstrap\Modal;

/* @var $this yii\web\View */
/* @var $searchModel app\models\PurchaseSuggestSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = '采购建议-供应商';
$this->params['breadcrumbs'][] = $this->title;
Modal::begin([
    'id' => 'create-purchase-modal',
    'header' => '<h4 class="modal-title">采购单产品</h4>',
    
    'footer' => '<a href="#" class="btn btn-primary" data-dismiss="modal">关闭</a>',
    'size'=>'modal-lg',
    'options'=>[
        //'data-backdrop'=>'static',//点击空白处不关闭弹窗

    ],
]);
Modal::end();
?>
<div class="purchase-suggest-index">

    <?php  echo $this->render('_search', ['model' => $searchModel]); ?>
    <p class="clearfix"></p>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'options'=>[
            'id'=>'grid_purchase',
        ],
        'pager'=>[
            //'options'=>['class'=>'hidden']//关闭分页
            'firstPageLabel'=>"首页",
            'prevPageLabel'=>'上一页',
            'nextPageLabel'=>'下一页',
            'lastPageLabel'=>'末页',
        ],
        'columns' => [
            ['class' => 'yii\grid\CheckboxColumn','name'=>'id'],
            'id',
            [
                'label'=>'默认供应商',
                'attribute'=>'supplier_code',
                'value'=>function($data){
                    return "{$data->supplier_name}";
                }
            ],
            [
                'label'=>'补货仓库',
                'attribute'=>'warehouse_code',
                'value'=>function($data){
                    return "{$data->warehouse_name}";
                }
            ],
            [
                'label'=>'建议采购SKU数',
                'attribute'=>'num_sku',
                'value'=>function($data){
                    return "{$data->num_sku}";
                }
            ],
            [
                'label'=>'建议采购数量',
                'attribute'=>'num_qty',
                'value'=>function($data){
                    return "{$data->num_qty}";
                }
            ],
            [
                'label'=>'预计采购金额',
                'attribute'=>'money',
                'value'=>function($data){
                    return "{$data->money}";
                }
            ],
            [
                'label'=>'默认采购员',
                'attribute'=>'buyer',
                'value'=>function($data){
                    return $data->buyer;
                }
            ],
            [
                'label'=>'采购建议更新时间',
                'attribute'=>'created_at',
                'value'=>function($data){
                    return $data->created_at;
                }
            ],
            [
                'header' => '操作',
                'class' => 'yii\grid\ActionColumn',
                'template'=> '{update}',
                'buttons' => [
                    'update' => function ($url, $model, $key) {
                        return Html::a('采购单',['purchase-suggest-supplier/create-purchase-supplier', 'supplier_code' => $model->supplier_code,'warehouse_code'=>$model->warehouse_code,'buyer'=>$model->buyer,'flag'=>'1'], ['class' => "btn btn-xs btn-success create-purchase", 'title' => '生成采购订单','data-toggle' => 'modal','data-target' => '#create-purchase-modal']);
                    },
                ]
            ],
        ],
        'containerOptions' => ["style"=>"overflow:auto"], // only set when $responsive = false
        'toolbar' =>  [
            // '{export}',
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
            //'heading'=>'<h3 class="panel-title"><i class="glyphicon glyphicon-globe"></i> Countries</h3>',
            'type'=>'success',
            //'after'=>Html::a('<i class="glyphicon glyphicon-repeat"></i> 刷新', ['index'], ['class' => 'btn btn-info']),
            //'footer'=>true
        ],
    ]); ?>
</div>
<?php
$js = <<<JS
   $(function(){
        //点击生成采购单
        $("a.create-purchase").click(function(){
            var url=$(this).attr('href');
            $.post(url, {},
                function (data) {
                    $('#create-purchase-modal').find('.modal-body').html(data);
                }
            );
        });
   });
JS;
$this->registerJs($js);
?>
