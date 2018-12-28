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
use mdm\admin\components\Helper;

$this->title = '已付款未到货列表';
$this->params['breadcrumbs'][] = $this->title;

//基础数据设置
Modal::begin([
    'id'        => 'basic-create',
    'header'    => '<h4 class="modal-title">未到货详情</h4>',
    'footer'    => '<a href="#" class="btn btn-primary" data-dismiss="modal">关闭</a>',
    'size'      => 'modal-lg',
]);
Modal::end();
$url_basic      = Url::toRoute('basic-create');
$url_basics     = Url::toRoute('batch-get-data');
$url_viewsku    = Url::toRoute('view-sku');
$url_export     = Url::toRoute('export-sku-paid-wait');

$js_basic = <<<JS
$(function(){
    $('a#basic-create').click(function(){
        $.post('{$url_basic}', {},
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
  
  $(".basic-create").click(function(){
        var url=$(this).attr("href");
        // alert(url);
        $.get(url, {},
            function (tmp) {
                $('#basic-create').find('.modal-body').html(tmp);
            }
        );
   });
  
 
  $("#export-csv").click(function(){
        var url = '{$url_export}';
        var ids = $('#paid-wait-grid').yiiGridView('getSelectedRows');
        if(ids == '' || ids == 'undefined'){
            if(confirm('您未选中任何记录，将按筛选条件导出，确定继续？')){
                window.open(url);
            }
        }else{
            url     = url+'?ids='+ids;
            window.open(url);
        }
   });
            
});
JS;
$this->registerJs($js_basic);

?>
<div class="product-index">

    <?php echo $this->render('_search', ['model' => $searchModel]); ?>
    <p class="clearfix"></p>

    <?php
    if(Helper::checkRoute(Url::toRoute('export-sku-paid-wait'))) {
        echo Html::button('导出Excel',['class' => 'btn btn-success btn-xs','id'=>'export-csv','style' => 'font-size:15px;']);
    }
    ?>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        //'filterModel' => $searchModel,
        'options' => [
            'id' => 'paid-wait-grid',
        ],
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
            ['class' => 'yii\grid\CheckboxColumn','name' => 'id'],
            'id',
            [
                'label' => '供应商代码',
                'attribute' => 'supplier_code',
                'format' => 'raw',
                'value' => function ($data) {
                    return $data->supplier_code;
                }
            ],
            [
                'label' => '供应商名称',
                'attribute' => 'supplier_name',
                'width' => '30%',
                'format' => 'raw',
                'value' => function ($data) {
                    return $data->supplier_name;
                }
            ],
            [
                'attribute'=>'totalCount',
                'label' => '未到货总数量',
                'format' => 'raw',
                'visible'=> true,
                'value' => function ($data) {
                    return empty($data->totalCount)?0:$data->totalCount;
                }
            ],
            [
                'attribute'=>'totalAmount',
                'label' => '未到货总金额',
                'format' => 'raw',
                'value' => function ($data) {
                    if($data->totalAmount){
                        return Html::a($data->totalAmount, ['view-sku', 'su_id' => $data->id],
                            ['class' => "basic-create",'data-toggle' => 'modal','data-target' => '#basic-create','title' => '查看详情']);
                    }else{
                        $html = 0;
                    }

                    return $html;
                }
            ]
        ],
        'containerOptions' => ["style" => "overflow:auto"],
        'toolbar' => [
//             '{export}',
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
            'type' => 'success',
        ],
    ]); ?>
</div>
<style>
    #paid-wait-grid-container table thead th{
        text-align: center;

    }
    #paid-wait-grid-container table tbody td{
        text-align: center;

    }
    </style>