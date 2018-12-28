<?php

use yii\helpers\Html;
use kartik\grid\GridView;
use yii\bootstrap\Modal;
use yii\helpers\Url;
use app\services\PurchaseOrderServices;
use app\services\SupplierServices;
use app\services\BaseServices;

/* @var $this yii\web\View */
/* @var $searchModel app\models\TodayListSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = '今日清单';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="purchase-order-index">

    <?= $this->render('_search', ['model' => $searchModel]); ?>
    <p class="clearfix"></p>
    <p>
        <a id="submit-audit" class="btn btn-success print" href="print-data" target="_blank">打印货品清单</a>
        <a id="submit-audit" class="btn btn-success" href="javascript:void(0);">导出excel</a>
    </p>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'options'=>[
            'id'=>'grid_purchase_order',
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
                'label'=>'缩略图',
                'attribute'=>'thumb_img',
                'format'=>'raw',
                'value'=> function ($model, $key, $index, $column) {
                    $html = '<img class="img-rounded thumb_img" src="' . $model->thumb_img . '" width="110px" alt="产品图片">';
                    $html .= '<div class="big_img" style="display: none; position: absolute; top: 40%; left: 400px; z-index: 999">';
                    $html .= '<img src="' . $model->big_img . '" width="500px" alt="产品图片">';
                    $html .= '</div>';
                    return $html;
                }
            ],
            [
                'label'=>'货品名称',
                'attribute' => 'product_name',
                "format" => "raw",
                'value'=>
                    function($model){
                        return  $model->product_name;
                    },

            ],
            [
                'label'=>'sku',
                'attribute' => 'sku',
                "format" => "raw",
                'value'=>
                    function($model){
                        return  $model->sku;
                    },

            ],
            [
                'label'=>'在途库存',
                'attribute' => 'in_transit_inventory',
                "format" => "raw",
                'value'=>
                    function($model){
                        return  $model->in_transit_inventory;
                    },

            ],
            [
                'label'=>'可用库存',
                'attribute' => 'usable_inventory',
                "format" => "raw",
                'value'=>
                    function($model){
                        return  $model->usable_inventory;
                    },

            ],
            [
                'label'=>'待发库存',
                'attribute' => 'on_order_inventory',
                "format" => "raw",
                'value'=>
                    function($model){
                        return  $model->on_order_inventory;
                    },

            ],
            [
                'label'=>'缺货数量',
                'attribute' => 'stockout_qty',
                "format" => "raw",
                'value'=>
                    function($model){
                        return  $model->stockout_qty;
                    },

            ],
            [
                'label'=>'开发员',
                'attribute' => 'developer',
                "format" => "raw",
                'value'=>
                    function($model){
                        return  BaseServices::getEveryOne($model->developer_id);
                    },

            ],
            [
                'label'=>'采购员',
                'attribute' => 'buyer',
                "format" => "raw",
                'value'=>
                    function($model){
                        return  BaseServices::getEveryOne($model->buyer_id);
                    },

            ],
            [
                'label'=>'创建时间',
                'attribute' => 'create_time',
                "format" => "raw",
                'value'=>
                    function($model){
                        return  $model->create_time;
                    },

            ],
            [
                'class' => 'kartik\grid\ActionColumn',
                'dropdown' =>true,
                'width'=>'180px',
                'template' => '{view}',
                'buttons'=>[
                    'view' => function ($url, $model, $key) {
                        return Html::a('<i class="glyphicon glyphicon-ok"></i> 强制完成采购单', ['complete','id'=>$key,'pur_number'=>$model->id], [
                            'title' => Yii::t('app', '强制完成'),
                            'class' => 'btn btn-xs red',
                            'data-toggle' => 'modal',
                            'data-target' => '#create-modal',
                        ]);
                    },

                ],

            ],
        ],
        'containerOptions' => ["style"=>"overflow:auto"], // only set when $responsive = false
        'toolbar' =>  [],


        'pjax' => false,
        'bordered' => true,
        'striped' => false,
        'condensed' => true,
        'responsive' => true,
        'hover' => true,
        'floatHeader' => true,
        'showPageSummary' => false,

        'exportConfig' => [
            GridView::EXCEL => [],
        ],
        'panel' => [
            'heading'=>'<h3 class="panel-title"><i class="glyphicon glyphicon-globe"></i> Countries</h3>',
            'type'=>'success',
            'after'=>Html::a('<i class="glyphicon glyphicon-repeat"></i> 刷新', ['index'], ['class' => 'btn btn-info']),
        ],
    ]); ?>
</div>
<?php
Modal::begin([
    'id' => 'create-modal',
    'header' => '<h4 class="modal-title">系统信息</h4>',
    'footer' => '<a href="#" class="btn btn-primary closes" data-dismiss="modal">Close</a>',
    'size'=>'modal-lg',
    'options'=>[
        'data-backdrop'=>'static',//点击空白处不关闭弹窗

    ],
]);
Modal::end();

$requestUrl = Url::toRoute('view');
$arrival='请选择需要标记到货日期的采购单';
$js = <<<JS
    $(function(){
            $("a#submit-audit").click(function(){
                var ids = $('#grid_purchase_order').yiiGridView('getSelectedRows');
                if(ids==''){
                    alert('请先选择!');
                    return false;
                }else{
                    var url = $(this).attr("href");
                    if($(this).hasClass("print"))
                    {
                        url = '/purchase-order/print-data';
                    }
                    url     = url+'?ids='+ids;
                    $(this).attr('href',url);
                }
            });
        });
    var img_flag = null;
    $(document).on('mouseenter', '.thumb_img', function () {
        clearTimeout(img_flag);
        $(this).next().slideDown();
    }).on('mouseleave', '.thumb_img', function () {
        var _this = $(this);
        clearTimeout(img_flag);
        img_flag = setTimeout(function() {
            _this.next().slideUp();
        }, 300);
    });
    $(document).on('click', '#views', function () {


        $.get('{$requestUrl}', {id:$(this).attr('value')},
            function (data) {
                $('.modal-body').html(data);
            }
        );
    });
     $(document).on('click', '.tracking', function () {
        $.get($(this).attr('href'), {},
            function (data) {
                $('.modal-body').html(data);
            }
        );
    });
    $(document).on('click', '#logistics', function () {

        $.get($(this).attr('href'), {},
            function (data) {
                $('.modal-body').html(data);
            }
        );
    });
     $(document).on('click', '.payment', function () {
        $.get($(this).attr('href'), {},
            function (data) {
                $('.modal-body').html(data);
            }
        );
    });
     $(document).on('click', '#arrival', function () {
            var str='';
            //获取所有的值
            $("input[name='id[]']:checked").each(function(){
                str+=','+$(this).val();
                //alert(str);

            })
            str=str.substr(1);

         if (str == ''){

            $('.modal-body').html('$arrival');
         }else{

            $.get($(this).attr('href'), {id:str},
                function (data) {
                    $('.modal-body').html(data);
                }
            );

         }

    });



JS;
$this->registerJs($js);
?>
