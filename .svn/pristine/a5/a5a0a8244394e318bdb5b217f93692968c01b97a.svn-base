<?php

use yii\helpers\Html;
use kartik\grid\GridView;
use yii\bootstrap\Modal;
use app\models\SupplierBuyer;

/* @var $this yii\web\View */
/* @var $searchModel app\models\PurchaseSuggestSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = '采购建议-供应商';
$this->params['breadcrumbs'][] = $this->title;
Modal::begin([
    'id' => 'create-purchase-modal',
    'header' => '<h4 class="modal-title">系统信息</h4>',

    'footer' => '<a href="#" class="btn btn-primary" data-dismiss="modal">关闭</a>',
    'size'=>'modal-lg',
    'options'=>[
        'data-backdrop'=>'static',//点击空白处不关闭弹窗

    ],
]);
Modal::end();
?>
<div class="purchase-suggest-index">

    <?php  echo $this->render('_search', ['model' => $searchModel]); ?>
    <p class="clearfix"></p>
    <?=Html::a(Yii::t('app', '变更采购员'), '#', [
        'id' => 'change-buyer',
        'data-toggle' => 'modal',
        'data-target' => '#create-purchase-modal',
        'class' => 'btn btn-success',
    ]);?>
    <p class="clearfix"></p>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'options'=>[
            'id'=>'grid_purchase',
        ],
        'filterSelector' => "select[name='".$dataProvider->getPagination()->pageSizeParam."'],input[name='".$dataProvider->getPagination()->pageParam."']",
        'pager'=>[
            'options'=>['class' => 'pagination','style'=> "display:block;"],
            'class'=>\liyunfang\pager\LinkPager::className(),
            'pageSizeList' => [20, 100, 200, 500,1000],
            //'options'=>['class'=>'hidden']//关闭分页
            'firstPageLabel'=>"首页",
            'prevPageLabel'=>'上一页',
            'nextPageLabel'=>'下一页',
            'lastPageLabel'=>'末页',
        ],
        'columns' => [
            ['class' => 'yii\grid\CheckboxColumn','name'=>'id',
                'checkboxOptions' => function($searchModel, $key, $index, $column) {
                    return ['supplier'=>$searchModel->supplier_code,'num'=>$searchModel->num_sku,'warehouse_code'=>$searchModel->warehouse_code,'buyer'=>$searchModel->buyer];
                }],
//            'id',
            [
                'label'=>'默认供应商',
                'attribute'=>'supplier_code',
                'value'=>function($data){
                    return "{$data->supplier_name}";
//                    return "{$data->supplier_code}[{$data->supplier_name}]";
                }
            ],
            [
                'label'=>'补货仓库',
                'attribute'=>'warehouse_code',
                'value'=>function($data){
                    return "{$data->warehouse_name}";
//                    return "{$data->warehouse_code}[{$data->warehouse_name}]";
                }
            ],
            [
                'label'=>'采购SKU数',
                'attribute'=>'num_sku',
                'value'=>function($data){
                    return "{$data->num_sku}";
                }
            ],
            [
                'label'=>'采购数量',
                'attribute'=>'num_qty',
                'value'=>function($data){
                    return "{$data->num_qty}";
                }
            ],
            [
                'label'=>'采购金额',
                'attribute'=>'money',
                'value'=>function($data){
                    return "{$data->money}";
                }
            ],
            [
                'label'=>'默认采购员',
                'attribute'=>'buyer',
                'value'=>function($data){
//                    if (!empty($data->supplier_code)) {
//                        $name = SupplierBuyer::getBuyer($data->supplier_code,2);
//                        return !empty($name) ? $name : 'admin';
//                    } else {
//                        return 'admin';
//                    }
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
                        return Html::a('采购单',['create-purchase-supplier', 'supplier_code' => $model->supplier_code,'warehouse_code'=>$model->warehouse_code,'buyer'=>$model->buyer,'flag'=>'1'], ['class' => "btn btn-xs btn-success create-purchase", 'title' => '生成采购订单','data-toggle' => 'modal','data-target' => '#create-purchase-modal']);
                    },
                ]
            ],
            /*[
                'attribute'=>'state',
                'format' => 'raw',
                'value'=> function($data){
                    if(isset($data->state)){
                        return \app\services\PurchaseOrderServices::getProcesStatus()[$data->state];
                    }else{
                        return '';
                    }
                }
            ],*/

            [
                'label'=>'未处理原因',
                'format' => 'raw',
                'value'=> function($data){
                    return Html::input('text','suggest-note',!empty($data->purchaseSuggestNote)?$data->purchaseSuggestNote->suggest_note:'',['readonly'=>'readonly','sku'=>$data->sku,'warehouse_code'=>$data->warehouse_code,'style'=>'width:200px']);
                }
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
$suggest_note_url = \yii\helpers\Url::toRoute('overseas-purchase-suggest-supplier/update-suggest-note');
$changeUrl = \yii\helpers\Url::toRoute('overseas-purchase-suggest-supplier/change-buyer');

$js = <<<JS

   $(function(){
        //点击生成采购单
        $("a.create-purchase").click(function(){
            var url=$(this).attr('href');
            $('#create-purchase-modal').find('.modal-body').html('正在请求数据。。。。');
            $.post(url, {},
                function (data) {
                    $('#create-purchase-modal').find('.modal-body').html(data);
                }
            );
        });
   });
//双击编辑未处理原因
    $("input[name='suggest-note']").dblclick(function(){
            $(this).removeAttr("readonly");
        });
    //失焦添加readonly
    $("input[name='suggest-note']").change(function(){
        $(this).attr("readonly","true");
        var suggest_note = $(this).val();
        var sku   = $(this).attr('sku');
        var warehouse_code   = $(this).attr('warehouse_code');
        $.ajax({
            url:'{$suggest_note_url}',
            data:{suggest_note:suggest_note,sku:sku,warehouse_code:warehouse_code},
            type: 'get',
            dataType:'json',
        });
       });
    //点击生成采购单
        $("a#create-purchase").click(function(){
            var ids = $('#grid_purchase').yiiGridView('getSelectedRows');
            if(ids==''){
                alert('请先选择需要生成的数据!');
                return false;
            }else{
                var url=$(this).attr("href");
                 if($(this).hasClass("pp"))
                    {
                        url = '/overseas-purchase-suggest-supplier/create-purchase';
                    }
                url=url+'?ids='+ids;
                $(this).attr('href',url);
                $.post(url, {},
                    function (data) {
                        $('#create-purchase-modal').find('.modal-body').html(data);
                    }
                );
            }
        });
        
        //修改采购员
        $("#change-buyer").click(function(){
            var dataArray = new Array();
            $('[name="id[]"]').each(function() {
                if($(this).is(":checked")){
                    dataArray.push([$(this).attr('supplier'),$(this).attr('warehouse_code'),$(this).attr('num'),$(this).attr('buyer')]);
                }
            });
            if(dataArray.length==0){
               alert('请选择要修改的采购建议');
               return false;
            }else{
               $.post('{$changeUrl}', {data: dataArray},
                    function (data) {
                        $('.modal-body').html(data);
                    }
                 );           
            }
        });
JS;
$this->registerJs($js);
?>
