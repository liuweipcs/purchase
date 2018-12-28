<?php
use yii\helpers\Html;
use kartik\grid\GridView;
use yii\bootstrap\Modal;
use yii\helpers\Url;
use app\services\PurchaseOrderServices;
use app\services\SupplierServices;
use app\services\BaseServices;
use app\config\Vhelper;
use mdm\admin\components\Helper;
/* @var $this yii\web\View */
/* @var $searchModel app\models\TodayListSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = '采购需求汇总';
$this->params['breadcrumbs'][] = $this->title;
?>
<style>
    .pps{
        font-size: 16px;
        padding: 0 0;
    }
</style>
<div class="purchase-order-index">

    <?= $this->render('_search', ['model' => $searchModel]); ?>
    <p class="clearfix"></p>
    <?php
    if(Helper::checkRoute('create-purchase-order'))
    {
        echo Html::a('创建采购单', '#', ['class' => 'btn btn-info create-purchase pp',/*'data-toggle' => 'modal', 'data-target' => '#create-modal'*/]);
    }
    ?>

    <?php
    if(Helper::checkRoute('create-purchase-order'))
    {
        echo Html::a('一键生成采购单', '#', ['class' => 'btn btn-info create-purchase2 pp']);
    }
    ?>
    <h4><p class="glyphicon glyphicon-heart pps" style="color: red" aria-hidden="true">温馨提示:</p><span style="color: red"><p class="pps">1.撤销的需求不可再还原,请看清楚了再撤销。驳回请重新修改,只有同意了才能创建采购单</p><p class="pps">2.请选择相同的供应商生成采购单</p><p class="pps">3.默认展示未采购的,查看已采购请用上面的搜索</p><p class="pps"></p></h4>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        //'filterModel' => $searchModel,
        'filterSelector' => "select[name='".$dataProvider->getPagination()->pageSizeParam."'],input[name='".$dataProvider->getPagination()->pageParam."']",
        'pager' => [
            'class' => \liyunfang\pager\LinkPager::className(),
            'options'=>['class' => 'pagination','style'=> "display:block;"],
            'template' => '{pageButtons} {customPage} {pageSize}', //分页栏布局
            'pageSizeList' => [50,100,200,300,500,1000], //页大小下拉框值
            'customPageWidth' => 50,            //自定义跳转文本框宽度
            'customPageBefore' => ' 跳转到第 ',
            'customPageAfter' => ' 页 ',
        ],
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
                'label'=>'产品图片',
                'attribute'=>'uploadimgs',
                'format'=>'raw',
                'value' => function ($model) {
                    return \toriphes\lazyload\LazyLoad::widget(['src'=>Vhelper::getSkuImage($model->sku)]);
                    //return Vhelper::toSkuImg($model->sku,$model->uploadimgs);
                }
            ],

            [
                'label'=>'采购信息',
                "format" => "raw",
                'value'=>
                    function($model){
                   //     $firstLine = $model->product&&!empty($model->product->product_linelist_id) ? BaseServices::getProductLineFirst($model->product->product_linelist_id) : '';
                        $supplier = \app\models\Supplier::find()->where(['supplier_code'=>!empty($model->defaultSupplier) ? $model->defaultSupplier->supplier_code : ''])->one();
                        //$supplierLine = \app\models\SupplierProductLine::find()->select('first_product_line')->where(['supplier_code'=>!empty($model->defaultSupplier) ? $model->defaultSupplier->supplier_code : ''])->scalar();
                        $supplierName = !empty($supplier) ? $supplier->supplier_name : '';
                        $productName = !empty($model->desc) ? $model->desc->title : '';
                        $data = '产品名：'.$productName.'<br/>';
                        $data.= $model->defaultSupplierLine? is_array(BaseServices::getProductLine($model->defaultSupplierLine->first_product_line)) ? '供应商产品线异常'.'<br/>':'供应商产品线：'.BaseServices::getProductLine($model->defaultSupplierLine->first_product_line).'<br/>' : '';
                        //$data.= $model->product_category?'产品分类：'.BaseServices::getCategory($model->product_category).'<br/>':'';
                        $data.= $model->defaultSupplierLine?'采购员：'.\app\models\PurchaseCategoryBind::getBuyer($model->defaultSupplierLine->first_product_line).'<br/>':'';
                        $sub_html = !empty($supplier)?\app\models\Supplier::flagCrossBorder(true,$supplier->supplier_code):'';
                        $data.= '<span style="color:#00a65a">供应商:'.$supplierName.$sub_html.'</span></br>';
                        $data.= $model->is_purchase==1?'是否生成采购计划：<span style="color:red">未生成</span>':'是否生成采购计划：<span style="color:#00a65a">已生成</span>';
                        return $data;
                    },

            ],
            [
                'label'=>'产品信息',
                'format'=>'raw',
                'value'=>function($model){
                    $link = !empty($model->defaultQuotes) ? $model->defaultQuotes->supplier_product_address : "https://1688.com";
                    $status  = '<span style="color:red">sku:'.Html::a($model->sku, Yii::$app->params['SKU_ERP_Product_Detail'].$model->sku,['target'=>'blank']).'</span>';
                    $status .= \app\models\ProductRepackageSearch::getPlusWeightInfo($model->sku,true);// 加重SKU搜索
                    $status .= '<br/><a href="'.$link.'" title="采购链接" target=\'_blank\'><i class=\'fa fa-fw fa-internet-explorer\'></i></a>';
                    $status .=Html::a('<span class="glyphicon glyphicon-stats" style="font-size:10px;color:cornflowerblue;" title="销量库存"></span>', ['product/get-stock-sales','sku'=>$model->sku],
                            [
                                'class' => 'btn btn-xs stock-sales-purchase',
                                'data-toggle' => 'modal',
                                'data-target' => '#create-modal',
                            ]);
                    $status .=Html::a('<span class="glyphicon glyphicon-eye-open" style="font-size:10px;color:cornflowerblue;" title="历史采购记录"></span>', ['purchase-suggest/histor-purchase-info','sku'=>$model->sku],[
                        'data-toggle' => 'modal',
                        'data-target' => '#create-modal',
                        'class'=>'btn btn-xs stock-sales-purchase',
                    ]).'<br/>';
                    $status .= !empty($model->product) ? '状态：'.\app\services\SupplierGoodsServices::getProductStatus($model->product->product_status).'<br/>' : '';
                    $status .= !empty($model->product) ? '开发人：'.$model->product->create_id : '';

                    // 查询SKU采购来料包装
                    $purchase_packaging = \app\models\Product::getSkuCode($model->sku);
                    $status .= '<br/><span style="color:#29608b">'.$purchase_packaging.'</span>';

                    return $status;
                }
            ],
            [
                'attribute' => 'platform_number',
                "format" => "raw",
                'value'=>
                    function($model){
                        return  $model->platform_number;
                    },

            ],
            [
                'label'=>'分组',
                'format'=>'raw',
                'value' => function($model){
                    return BaseServices::getAmazonGroupName($model->group_id);
                }
            ],
            [
                'attribute' => 'purchase_quantity',
                "format" => "raw",
                'value'=>
                    function($model){
                        return  '<span style="color:red">'.$model->purchase_quantity.'</span>';
                    },

            ],
            [
                'attribute' => 'purchase_warehouse',
                "format" => "raw",
                'value'=>
                    function($model){
                        return  $model->purchase_warehouse?BaseServices::getWarehouseCode($model->purchase_warehouse):'';
                    },

            ],
            [
                'attribute' => 'is_transit',
                "format" => "raw",
                'value'=>
                    function($model){
                        return  $model->is_transit==1?'<span style="color:red">否</span>':'<span style="color:#00a65a">是</span>';   //主要通过此种方式实现
                    },

            ],
            [
                'attribute' => 'is_back_tax',
                "format" => "raw",
                'value'=>
                    function($model){
                        return  $model->is_back_tax==1 ? '<span style="color:red">是</span>': ($model->is_back_tax==2 ? '<span style="color:#00a65a">否</span>' : '未知');
                    },
            ],
            /* [
                 'attribute' => 'transit_warehouse',
                 "format" => "raw",
                 'value'=>
                     function($model){
                         return  $model->transit_warehouse?BaseServices::getWarehouseCode($model->transit_warehouse):'';
                     },

             ],*/
            [
                'label'=>'需求信息',
                'attribute' => 'create_id',
                "format" => "raw",
                'value'=>
                    function($model){
                        $data = '<span style="color:#00a65a">需求单号:'.$model->demand_number.'</span><br/>';
                        $data .= '需求人:'.$model->create_id.'<br/>';
                        $data .= '销售:'.$model->sales.'<br/>';
                        $data .='需求时间:'.$model->create_time;
                        $data .='销售账号:'.$model->xiaoshou_zhanghao;
                        return  $data;
                    },

            ],

            [
                'attribute' => 'level_audit_status',
                "format" => "raw",
                'value'=>
                    function($model){
                        if($model->level_audit_status==1)
                        {
                            $str ='';
                            $str .= '<span style="color:#00a65a">'.Yii::$app->params['demand'][$model->level_audit_status].'</span>';
                            return $str;

                        } elseif($model->level_audit_status==2){

                            $str = '<span style="color:red">'.Yii::$app->params['demand'][$model->level_audit_status].'</span><br/>';
                            $str .= '原因：'.$model->audit_note;
                            return $str;

                        } elseif($model->level_audit_status==4){

                            $str = '<span style="color:red">'.Yii::$app->params['demand'][$model->level_audit_status].'</span><br/>';
                            $str .= '原因：'.$model->purchase_note;
                            return $str;

                        } else{
                            return  Yii::$app->params['demand'][$model->level_audit_status];
                        }

                    },

            ],
            [
                'label'=>'同意(驳回)信息',
                'attribute' => 'agree_user',
                "format" => "raw",
                'value'=>
                    function($model){
                        if($model->level_audit_status==4)
                        {
                            $data = '采购驳回人:'.$model->buyer.'<br/>';
                            $data .= '采购驳回时间:'.$model->purchase_time;
                            return  $data;
                        } else{

                            $data = '同意(驳回)人:'.$model->agree_user.'<br/>';
                            $data .= '同意(驳回)时间:'.$model->agree_time;
                            return  $data;
                        }

                    },

            ],
            [
                'attribute' => 'sales_note',
                "format" => "raw",
                'value'=>
                    function($model){
                        return  $model->sales_note;
                    },

            ],
            [
                'label'=>'备注',
                'format' => 'raw',
                'value'=> function($model){
                    $create_time = !empty($model->purchaseSuggestNote)?$model->purchaseSuggestNote->create_time:'';
                    $creator = !empty($model->purchaseSuggestNote->creator)?$model->purchaseSuggestNote->creator:'';
                    return Html::input('text','suggest-note',!empty($model->purchaseSuggestNote)?$model->purchaseSuggestNote->suggest_note:'',['readonly'=>'readonly','sku'=>$model->sku,'warehouse_code'=>$model->purchase_warehouse,'group_id'=>$model->group_id,'style'=>'width:200px']) . '<br />' . $create_time . '<br />' . $creator;
                }
            ],
            [
                'class' => 'kartik\grid\ActionColumn',
                'dropdown' => false,
                'width'=>'180px',
                'template' => Helper::filterActionColumn('{purchase-disagree}'),
                'buttons'=>[
                    'purchase-disagree' => function ($url, $model, $key)
                    {
                        $arr= ['1'];
                        if(in_array($model->level_audit_status,$arr) && $model->is_purchase == 1) {
                            $page=Yii::$app->request->get('page') ? Yii::$app->request->get('page') : 1;
                            return Html::a('<i class="fa fa-fw fa-close"></i>采购驳回', ['purchase-disagree', 'id' => $key,'page'=>$page], [
                                'title'       => Yii::t('app', '采购驳回'),
                                'class'       => 'btn btn-xs pdisagree',
                                'data-toggle' => 'modal',
                                'data-target' => '#create-modal',
                            ]);
                        }
                    },
                ]

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
        'floatHeader' => false,
        'showPageSummary' => false,

        'exportConfig' => [
            GridView::EXCEL => [],
        ],
        'panel' => [
            //'heading'=>'<h3 class="panel-title"><i class="glyphicon glyphicon-globe"></i> Countries</h3>',
            'type'=>'success',
            //'after'=>Html::a('<i class="glyphicon glyphicon-repeat"></i> 刷新', ['index'], ['class' => 'btn btn-info']),
        ],
    ]); ?>
</div>
<?php
Modal::begin([
    'id' => 'create-modal',
    'header' => '<h4 class="modal-title">系统信息</h4>',
    'footer' => '<a href="#" class="btn btn-primary closes" data-dismiss="modal">关闭</a>',
    'size'=>'modal-lg',
    'options'=>[
        'data-backdrop'=>'static',//点击空白处不关闭弹窗

    ],
]);
Modal::end();

$requestUrl = Url::toRoute('view');
$createUrl = Url::toRoute('create-fba-purchase-order');
$createUrl2 = Url::toRoute('create-fba-purchase-order2');
$arrival='请选择需要标记到货日期的采购单';
$js = <<<JS
$(function() {
    
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
    var submit_click = 0;
    // 根据勾选的需求一键生成采购单
    $("a.create-purchase2").click(function() {
        if(submit_click>0){
            layer.alert('请勿多次点击');
            return false;
        }
        var ids = $('#grid_purchase_order').yiiGridView('getSelectedRows');
        if(ids == '') {
            layer.alert('请先选择需要生成的数据!');
            return false;
        } else {
            submit_click++;
            var loading = layer.load(6 , {shade : [0.5 , '#BFE0FA']});
            $.ajax({
                url: '{$createUrl2}',
                data: {ids: ids},
                type: 'post',
                dataType: 'json',
                success: function(data) {
                    layer.close(loading);
                    if(data.error == 1) {
                        layer.alert(data.message);
                    } else {
                        layer.alert(data.message, {closeBtn: 0}, function() {
                            location.reload();
                        });
                    }
                }
            });
        }
    });
  
});
    $(document).on('click', '.disagree', function () {

        $.get($(this).attr('href'), {},
            function (data) {
                $('.modal-body').html(data);
            }
        );
    });
    $(document).on('click', '.pdisagree', function () {

        $.get($(this).attr('href'), {},
            function (data) {
                $('.modal-body').html(data);
            }
        );
    });

    $(function(){
            var submit_click_1 = 0;
        //点击生成采购单
        $("a.create-purchase").click(function(){
            if(submit_click_1>0){
                layer.alert('请勿多次点击');
                return false;
            }
            submit_click_1++;
            var loading = layer.load(6 , {shade : [0.5 , '#BFE0FA']});
                $.ajax({
                url: '{$createUrl}',
                type: 'post',
                dataType: 'json',
                success: function(data) {
                    layer.close(loading);
                    if(data.error == 1) {
                        layer.alert(data.message, {closeBtn: 0}, function() {
                            location.reload();
                        });
                    } else {
                        layer.alert(data.message, {closeBtn: 0}, function() {
                            location.reload();
                        });
                    }
                }
            });
        });
        
        //批量同意
        $(".bulk-consent").click(function(){
            var ids = $('#grid_purchase_order').yiiGridView('getSelectedRows');
            if(ids==''){
                alert('请先选择数据!');
                return false;
            }else{
                var url=$(this).attr("href");
                $.post(url, {ids:ids},
                    function (data) {
                        $('.modal-body').html(data);
                    }
                );
               return false; 
            }
        });
        
        //批量驳回
        $(".dismiss-batches").click(function(){
            var ids = $('#grid_purchase_order').yiiGridView('getSelectedRows');
            if(ids==''){
                alert('请先选择数据!');
                return false;
            }else{
                var url=$(this).attr("href");
                $.post(url, {id:ids},
                    function (data) {
                        $('.modal-body').html(data);
                    }
                );
            }
        });
        
        
        //采购需求批量导入
        $(".purchase-sum-import").click(function(){
            $.get($(this).attr('href'), {},
            function (data) {
                $('.modal-body').html(data);
            });
        });
        
        //双击编辑报价
        $("input[name='suggest-note']").dblclick(function(){
                $(this).removeAttr("readonly");
            });
        //失焦添加readonly
        $("input[name='suggest-note']").change(function(){
            $(this).attr("readonly","true");
            var suggest_note = $(this).val();
            var sku   = $(this).attr('sku');
            var warehouse_code   = $(this).attr('warehouse_code');
            var group_id = $(this).attr('group_id');
            $.ajax({
                url:'update-suggest-note',
                data:{suggest_note:suggest_note,sku:sku,warehouse_code:warehouse_code,group_id:group_id},
                type: 'get',
                dataType:'json',
            });
           });
        
        $(document).on('click', '.stock-sales-purchase', function () {
        $('.modal-body').html('正在请求数据....');
        $.get($(this).attr('href'), {},
            function (data) {
                $('.modal-body').html(data);
            }
        );
    });

    });

JS;
$this->registerJs($js);
?>
