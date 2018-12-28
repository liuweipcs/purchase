<?php

use yii\helpers\Html;
use kartik\grid\GridView;
use yii\bootstrap\Modal;
use yii\helpers\Url;
use app\services\CommonServices;
use app\services\SupplierGoodsServices;
use yii\helpers\ArrayHelper;
use app\models\SkuSalesStatisticsSearch;
use \app\models\Product;
$this->title = 'SKU销售列表';
$this->params['breadcrumbs'][] = $this->title;
//基础数据设置
Modal::begin([
    'id' => 'basic-create',
    'header' => '<h4 class="modal-title">系统信息</h4>',
    'footer' => '<a href="#" class="btn btn-primary" data-dismiss="modal">关闭</a>',
    'size'=>'modal-lg',
]);
Modal::end();
$url_basic = Url::toRoute('basic-create');
$url_basics = Url::toRoute('batch-get-data');
$js_basic = <<<JS
$(function(){
    $('a#basic-create').click(function(){
        $.post('{$url_basic}', {},
            function (data) {
                $('#basic-create').find('.modal-body').html(data);
            }
        );
    });
    $('a.view-log').click(function(){
        var url_log=$(this).attr('href');
        $.post(url_log, {},
            function (data) {
                $('#basic-create').find('.modal-body').html(data);
            }
        );
    });
     $('a#desc').click(function(){
        var url_log=$(this).attr('href');
        $.get(url_log, {},
            function (data) {
                $('#basic-create').find('.modal-body').html(data);
            }
        );
    });
            
  $("#batchgetdata").click(function(){
       var ids = $('#grid').yiiGridView('getSelectedRows');
        

            var url='{$url_basics}';
            url=url+'?ids='+ids;
            $(this).attr('href',url);

  });
            
            
});
JS;
$this->registerJs($js_basic);

?>

<div class="product-index">

    <?php echo $this->render('_search', ['model' => $searchModel]); ?>
    <p class="clearfix"></p>
    <p>
        <?= Html::a('海外仓基础数据设置', '#', ['class' => 'btn btn-success ','id'=>'basic-create','data-toggle' => 'modal','data-target' => '#basic-create']) ?>
        <?= Html::a('运行海外仓补货策略', '#', ['class' => 'btn btn-success', 'id' => 'batchgetdata','target'=>'_blank']) ?>

    </p>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        //'filterModel' => $searchModel,
        'options'=>[
            'id'=>'grid',
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
                'label'=>'产品',
                'attribute'=>'sku',
                'format' => 'raw',
                'value'=>function($data){
                    $res=CommonServices::getDefSupplier($data->sku);
                    $str='';
                    $str.='SKU:'.$data->sku."<br/>";
                    $str.='名称:'.ArrayHelper::getValue($data, 'productChName.title', 'Unknown')."<br/>";
                    //$str.='级别:'.' '."<br/>";
                    //$str.='款式编码:'.' '."<br/>";
                    //$str.='默认供应商:'.$res['oneSupplier']['supplier_code'].'['.$res['oneSupplier']['supplier_name'].']'."<br/>";
                    //$str.=!empty($res['oneSupplier']['buyer'])?'默认采购员:'.\app\services\BaseServices::getEveryOne($res['oneSupplier']['buyer'])."<br/>":'默认采购员:';
                    return $str;
                }
            ],
            // [
            //     'label'=>'捆绑产品',
            //     'attribute'=>'SKU',
            //     'format' => 'raw',
            //     'value'=>function($data){

            //         return ArrayHelper::getValue($data, 'product.product_type', 'Unknown');
            //     }
            // ],
            [
                'label'=>'销售状态',
                'attribute'=>'SKU',
                'format' => 'raw',
                'value'=>function($data){

                    $status = Product::getSkuStatus($data->sku);
                    $rs     = !empty($status)?SupplierGoodsServices::getProductStatus($status):'未知状态';

                    return $rs;
                }
            ],
            [
                'label'=>'分类',
                'attribute'=>'SKU',
                'format' => 'raw',
                'value'=>function($data){
                    $res=CommonServices::getCategory(ArrayHelper::getValue($data, 'product.product_category_id', 'Unknown'));
                    return $res['category_cn_name'];
                }
            ],
            [
                'label'=>'仓库',
                'attribute'=>'SKU',
                'format' => 'raw',
                'value'=>function($data){
                    return ArrayHelper::getValue($data, 'warehouse.warehouse_code', 'Unknown').'['.ArrayHelper::getValue($data, 'warehouse.warehouse_name', 'Unknown').']';
                }
            ],
            [
                'label'=>'补货模式',
                'attribute'=>'SKU',
                'format' => 'raw',
                'value'=>function($data){
                    if(isset($data->warehouse->pattern)){
                        return $data->warehouse->pattern=='def'?'默认':'最小';
                    }else{
                        return '无';
                    }
                }
            ],
            [
                'label'=>'补货策略',
                'attribute'=>'SKU',
                'format' => 'raw',
                'value'=>function($data){
                    if(isset($data->warehouse->warehouse_code)&&isset($data->warehouse->pattern)){
                        $res = CommonServices::getTactics($data->warehouse->warehouse_code, $data->warehouse->pattern);
                        return $res;
                    }else{
                        return '无';
                    }
                }
            ],
            [
                'header' => '操作',
                'class' => 'yii\grid\ActionColumn',
                'template'=> '{update}',
                'headerOptions' => ['width' => '140'],
                'buttons' => [
                    'update' => function ($url, $model, $key) {
                        return Html::a('日志',['view-log', 'sku' => $model->sku,'warehouse_code'=>$model->warehouse_code], ['class' => "btn btn-xs btn-success view-log", 'title' => '补货日志','target'=>'_blank']);
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