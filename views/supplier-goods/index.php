<?php

use yii\helpers\Html;

use kartik\grid\GridView;
use Yii\helpers\Url;
use yii\bootstrap\Modal;
use app\config\Vhelper;
use kartik\select2\Select2;
use yii\web\JsExpression;
use app\services\BaseServices;
use mdm\admin\components\Helper;
/* @var $this yii\web\View */
/* @var $searchModel app\models\SupplierGoodsSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', '供应商产品管理');
$this->params['breadcrumbs'][] = $this->title;
?>
<style>
    img {width: 80px}
    .button-a{margin-right: 20px}
</style>
<div class="stockin-index">


    <?php $this->render('_search', ['model' => $searchModel]); ?>
    <?= $this->render('_search1', ['model' => $searchModel]); ?>
    <p class="clearfix"></p>
    <?php  Html::button('添加备用供应商',['class'=>'btn btn-info apply','type'=>'add']) ?>
    <?php
    if(Helper::checkRoute('batch-change-source-status')) {
         echo Html::a(Yii::t('app', '变更货源状态'),['batch-change-source-status'], ["class" => "btn btn-info button-a",'data-toggle' => 'modal','data-target' => '#create-modal','id' => 'source_status']);
    }
    if(Helper::checkRoute('ex-quotes')){
        echo Html::a(Yii::t('app', '批量导入报价'), ['ex-quotes'], ["class" => "btn btn-success button-a ",'data-toggle' => 'modal','data-target' => '#create-modal','id' => 'creates']);
    }
    if(Helper::checkRoute('export')){
        echo   Html::a('导出', '#',['class' => 'btn btn-warning export button-a','id'=>'export-csv']);
    }
    if(Helper::checkRoute('apply')){
        echo Html::button('申请修改',['class'=>'btn btn-info apply button-a','type'=>'update']);
    }
    ?>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        "options" => ["class" => "grid-view","style"=>"overflow:auto", "id" => "grid"],
        'filterSelector' => "select[name='".$dataProvider->getPagination()->pageSizeParam."'],input[name='".$dataProvider->getPagination()->pageParam."']",
        'pager' => [
            'class' => \liyunfang\pager\LinkPager::className(),
            'options'=>['class' => 'pagination','style'=> "display:block;"],
            'template' => '{pageButtons} {customPage} {pageSize}', //分页栏布局
            'pageSizeList' => [20,100,200,300,500,1000], //页大小下拉框值
            'customPageWidth' => 50,            //自定义跳转文本框宽度
            'customPageBefore' => ' 跳转到第 ',
            'customPageAfter' => ' 页 ',
        ],
        'columns' => [
            //['class' => 'kartik\grid\SerialColumn'],
            [
                'class' => 'kartik\grid\CheckboxColumn',
                'name'=>"id" ,
                'checkboxOptions' => function ($model, $key, $index, $column) {
                    $quotesId =  !empty($model->supplierQuote) ? $model->supplierQuote->id : 0;
                    $supplier =  !empty($model->defaultSupplierDetail) ? $model->defaultSupplierDetail->supplier_code : '';
                    $price =  !empty($model->supplierQuote) ? $model->supplierQuote->supplierprice : '';
                    $pur_ticketed_point =  !empty($model->supplierQuote) ? $model->supplierQuote->pur_ticketed_point : 0;
                    $link  = !empty($model->supplierQuote) ? $model->supplierQuote->supplier_product_address : '';
                    return ['value' => $model->sku,'quotesId'=>$quotesId,'supplierCode'=>$supplier,'price'=>$price,'link'=>$link,'pur_ticketed_point'=>$pur_ticketed_point];
                }

            ],
            [
                'label'=>'审核状态',
                'format'=>'raw',
                'value'=>function($model){
                    return !empty($model->updateApply) ? '有' : '无';
                }
            ],
            [
                'label'=>'产品状态',
                'attribute'=>'uploadimgss',
                'format'=>'raw',
                'value'=> function ($model, $key, $index, $column) {


                    $product= app\services\SupplierGoodsServices::getProductStatus($model->product_status)."<br/>";

                    return $product;
                }
            ],
            [
                'label'=>'SKU创建时间',
                'format'=>'raw',
                'value'=>function($model){
                    return $model->create_time;
                }
            ],
            [
                'label'=>'产品线',
                'attribute'=>'uploadimgss',
                'format'=>'raw',
                'value'=> function ($model, $key, $index, $column) {
                    $product= !empty($model->product_linelist_id) ? BaseServices::getProductLine($model->product_linelist_id): '';
                    return $product;
                }
            ],
            [
                'label'=>'产品图片',
                'attribute'=>'uploadimgss',
                'format'=>'raw',
                'value'=> function ($model, $key, $index, $column) {
                    
                   // return \toriphes\lazyload\LazyLoad::widget(['src' => Vhelper::downloadImg($model->sku,$model->uploadimgs,2)]);
                   //return \toriphes\lazyload\LazyLoad::widget(['src' => Vhelper::getSkuImage($model->sku,$model->uploadimgs),'width'=>'100px','height'=>'80px']);
//                    $product = Html::img(Vhelper::downloadImg($model->sku,$model->uploadimgs,2),['width'=>'80px']);
//                    return $product;
                    return \toriphes\lazyload\LazyLoad::widget(['src'=>Yii::$app->params['ERP_URL'].'/services/api/system/index/method/getimage/sku/' . $model->sku]);
                }
            ],
            [
                'label'=>'sku',
                //'attribute'=>'uploadimgss',
                'format'=>'raw',
                'value'=> function ($model, $key, $index, $column)
                {
                    $html = Html::a($model->sku, Yii::$app->params['SKU_ERP_Product_Detail'].$model->sku,['target'=>'blank']);
                    $html .='<br>'.Html::a('<span class="glyphicon glyphicon-stats" style="font-size:10px;color:cornflowerblue;" title="销量库存"></span>', ['product/get-stock-sales','sku'=>$model->sku],
                        [
                            'class' => 'btn btn-xs stock-sales-purchase',
                            'data-toggle' => 'modal',
                            'data-target' => '#create-modal',
                        ]);
                    $html .=Html::a('<span class="glyphicon glyphicon-eye-open" style="font-size:10px;color:cornflowerblue;" title="历史采购记录"></span>', ['purchase-suggest/histor-purchase-info','sku'=>$model->sku],[
                        'data-toggle' => 'modal',
                        'data-target' => '#create-modal',
                        'class'=>'btn btn-xs stock-sales-purchase',
                    ]);
                   return $html;

                }
            ],
            [
                'label'=>'产品名称',
                //'attribute'=>'uploadimgss',
                'format'=>'raw',
                'value'=> function ($model, $key, $index, $column) {
                    $product = !empty($model->desc->title)?$model->desc->title."<br/>":''."<br/>";
                    $product .= empty($model->supplierQuote) || $model->supplierQuote->is_back_tax == 0 ? '[未知是否可退税]' : 
                        ($model->supplierQuote->is_back_tax == 1 ? '[可退税]' : '[不可退税]');
                    $product .= '&nbsp;[出口退税率:'.$model->tax_rate.'%]';
                    $product .= '<br>[申报中文名:'.$model->export_cname.']&nbsp;[单位:'.$model->declare_unit.']';
                    $product .= '<br>[出口申报型号:'.$model->product_model_out.']';
                    return $product;
                }
            ],
            [
                'label'=>'单价',
                'format'=>'raw',
                'value'=> function($model){
                    $price =  !empty($model->supplierQuote) ? $model->supplierQuote->supplierprice : '';
                    $dbclick = empty($model->updateApply) ? ' dbclick' : '';
                    return Html::input('text',"SupplierQuotes[$model->id][supplierprice]",$price,['readonly'=>true,'class'=>'price'.$dbclick,'style'=>'width:70px']);
                }
            ],
            [
                'label'=>'税点',
                'format'=>'raw',
                'value'=> function($model){
                    // 默认为NULL
                    $pur_ticketed_point = !empty($model->supplierQuote) ? $model->supplierQuote->pur_ticketed_point : NULL;
                    $pur_ticketed_point = ($pur_ticketed_point === NULL)?NULL:$pur_ticketed_point;
                    $dbclick = empty($model->updateApply) ? ' dbclick' : '';
                    return Html::input('text',"SupplierQuotes[$model->id][pur_ticketed_point]",$pur_ticketed_point,['readonly'=>true,'class'=>'pur_ticketed_point'.$dbclick,'style'=>'width:40px']).'%';
                }
            ],
            [
                'label'=>'默认供应商编码',
                'format'=>'raw',
                'visible' => $is_visible,
                'value'=>function($model){
                    $url = \yii\helpers\Url::to(['/supplier/search-supplier']);
                    return Select2::widget([ 'name' => 'title',
                        'options' => ['placeholder' => '请输入供应商 ...'],
                        'value'    =>!empty($model->defaultSupplierDetail) ? $model->defaultSupplierDetail->supplier_code : '',
                        'pluginOptions' => [
                            'placeholder' => 'search ...',
                            'allowClear' => true,
                            'language' => [
                                'errorLoading' => new JsExpression("function () { return 'Waiting...'; }"),
                            ],
                            'ajax' => [
                                'url' => $url,
                                'dataType' => 'json',
                                'data' => new JsExpression("function(params) { return {q:params.term}; }")
                            ],
                            'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
                            'templateResult' => new JsExpression('function(res) { return res.text; }'),
                            'templateSelection' => new JsExpression('function (res) { return res.id; }'),
                        ],
                    ]);
                    //return !empty($model->defaultSupplierDetail) ? $model->defaultSupplierDetail->supplier_name : '';
                }
            ],
            [
                'label'=>'默认供应商名称',
                'format'=>'raw',
                'visible' => $is_visible,
                'value'=>function($model){
                    $supplierName = !empty($model->defaultSupplierDetail) ? $model->defaultSupplierDetail->supplier_name : '';
                    $supplierCode = !empty($model->defaultSupplierDetail) ? $model->defaultSupplierDetail->supplier_code : '';
                    $supplier = \app\models\Supplier::find()->select('id')->andWhere(['supplier_code'=>$supplierCode])->one();
                    $html=$supplierName;
                    $html.=Html::a('<span class="glyphicon glyphicon-zoom-in" style="font-size:20px;color:#f2adb1;margin-right:10px;" title="单击，查看供应商基本信息"></span>', ['#'],
                        [
                            'class' => 'btn btn-xs supplier',
                            'data-toggle' => 'modal',
                            'data-target' => '#create-modal',
                            'value' =>empty($supplier) ? '' :$supplier->id
                        ]);
                    $html.= Html::a('<span class="glyphicon glyphicon-zoom-in" style="font-size:20px;color:#f2adb1;margin-right:10px;" title="单击，查看备用供应商"></span>', ['#'],
                        [
                            'class' => 'btn btn-xs standby',
                            'data-toggle' => 'modal',
                            'data-target' => '#create-modal',
                            'value' =>$model->sku
                        ]);
                    $html .= \app\models\SupplierSearch::flagCrossBorder(true,$supplierCode);
                    return $html;
                }
            ],
            [
                'label'=>'采购员',
                'format'=>'raw',
                'value'=>function($model){
                    $html ='';
                    if(!empty($model->defaultSupplierDetail->buyerList)){
                        foreach ($model->defaultSupplierDetail->buyerList as $value){
                            switch ($value->type){
                                case 1:
                                    $html.='国内仓：'.$value->buyer.'<br/>';
                                    break;
                                case 2:
                                    $html.='海外仓：'.$value->buyer.'<br/>';
                                    break;
                                case 3:
                                    $html.='FBA：'.$value->buyer.'<br/>';
                                    break;
                                default:
                                    $html.='';
                            }
                        }
                    }
                    return $html;
                }
            ],
            [
                'label'=>'采购链接',
                'format'=>'raw',
                'visible' => $is_visible,
                'value' =>function($model){
                    $link = !empty($model->supplierQuote) ? $model->supplierQuote->supplier_product_address : '';
                    return Html::input('text',"SupplierQuotes[$model->id][link]",$link,['readonly'=>true,'class'=>'link dbclick','style'=>'width:70px']);
                }
            ],
            [
                'label'=>'开发员',
                'format'=>'raw',
                'value'=>function($model){
                    return $model->create_id;
                }
            ],
            [
                'label'=>'备注',
                'format'=>'raw',
                'value'=>function($model){
                    return Html::input('text','note',$model->note,['readonly'=>'readonly','productId'=>$model->id,'style'=>'width:70px']);
                }
            ],
            [
                'label'=>'货源状态',
                'format'=>'raw',
                'value'=>function($model){
                    $sourceStatus = $model->sourceStatus ? $model->sourceStatus->sourcing_status : 1;
                    return Html::dropDownList('',$sourceStatus,[1=>'正常',2=>'停产',3=>'断货'],['sku'=>$model->sku,'status'=>$sourceStatus,'class'=>'source_status']);
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
        'responsive' => true,
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

$requestUrl = Url::toRoute('create');
$batchUrl   = Url::toRoute('all-default-supplier');
$batchUrls  = Url::toRoute('all-add-product');
$msg        = '请选择产品';
$exportUrl  = Url::toRoute('export');
$standbyUrl  = Url::toRoute('standby-supplier');
$supplierUrl  = Url::toRoute('supplier/view-info');
$changeUrl  = Url::toRoute('change-source-status');
//$requestUrl = Url::toRoute('create');
$js = <<<JS
    $(document).on('click', '.creates', function () {

        $.get('{$requestUrl}', {},
            function (data) {
                $('.modal-body').html(data);
            }
        );
    });
    $(document).on('click', '.standby', function () {
        var sku = $(this).attr('value');
        $.get('{$standbyUrl}', {sku:sku},
            function (data) {
                $('.modal-body').html(data);
            }
        );
    });

    $(document).on('click', '.supplier', function () {
        var id = $(this).attr('value');
        $.get('{$supplierUrl}', {id:id,layout:'ajax'},
            function (data) {
                $('.modal-body').html(data);
            }
        );
    });
    $(document).on('click', '#batch', function () {
           var str='';
            //获取所有的值
            $("input[name='id[]']:checked").each(function(){
                str+=','+$(this).val();
                //alert(str);

            })
            str=str.substr(1);
            if (str&& str.length !=0)
            {
                     $.get('{$batchUrl}', {id: str},
                                function (data) {
                                    $('.modal-body').html(data);
                                }
                     );

            } else {
                     $('.modal-body').html('{$msg}');
                     return false;

            }
    });
    $(document).on('click', '#product', function () {

        $.get('{$batchUrls}', {},
            function (data) {
               $('.modal-body').html(data);
            }
       );
    });
    
     $(document).on('click', '.stock-sales-purchase', function () {
        $('.modal-body').html('正在请求数据....');
        $.get($(this).attr('href'), {},
            function (data) {
                $('.modal-body').html(data);
            }
        );
    });
    

    $(document).on('click','.export',function() {
        var sku      = $("#suppliergoodssearch-sku").val();
        var status   = $('#suppliergoodssearch-product_status').val();
        var supplier = $('#suppliergoodssearch-quotes-suppliercode').val();
        var line = $('#suppliergoodssearch-product_line').val();
        $(this).attr('href',"{$exportUrl}?sku="+sku+'&product_status='+status+'&suppliercode='+supplier+'&line='+line);
    });

    //双击编辑报价
    $("input.dbclick").dblclick(function(){
            $(this).removeAttr("readonly");
        });
    //失焦添加readonly
    $("input.price").blur(function(){
        $(this).attr("readonly","true");
        //将修改的值传入复选框属性
        $(this).closest('tr').find('input[name="id[]"]').attr('price',$(this).val());
    });

    $("input.pur_ticketed_point").blur(function(){
        $(this).attr("readonly","true");
        //将修改的值传入复选框属性
        $(this).closest('tr').find('input[name="id[]"]').attr('pur_ticketed_point',$(this).val());
    });

    $("input.link").blur(function(){
        $(this).attr("readonly","true");
        //将修改的值传入复选框属性
        $(this).closest('tr').find('input[name="id[]"]').attr('link',$(this).val());
    });

    $('[name="title"]').change(function(){
            var supplier = $(this).select2("val");
            //将数据写入复选框属性
            $(this).closest('tr').find('input[name="id[]"]').attr('suppliercode',supplier);
        });

    $('.apply').click(function(){
         var data = new Array();
         var type = $(this).attr('type');
         $('input[name="id[]"]').each(function(){
            if($(this).is(':checked')){
                var skudata = {quoteId:$(this).attr('quotesid'),sku:$(this).val(),suppliercode:$(this).attr('suppliercode'),price:$(this).attr('price'),pur_ticketed_point:$(this).attr('pur_ticketed_point'),link:$(this).attr('link')}
                data.push(skudata);
            }
         })
         if(data.length == 0){
             alert('请至少选择一个！');
         }else {
             layer.prompt({title: '备注', value: '', formType: 2}, function (remark, index) {
                    $.ajax({
                        url:'apply',
                        data:{data:data,type:type,remark:remark},
                        type: 'post',
                        dataType:'json',
                        success: function (data) {
                            window.location.reload();
                        }
                    });
                layer.close(index);
            });
         }    
    });

    //双击编辑报价
    $("input[name='note']").dblclick(function(){
            $(this).removeAttr("readonly");
        });
    //失焦添加readonly
    $("input[name='note']").change(function(){
        $(this).attr("readonly","true");
        var note = $(this).val();
        var id   = $(this).attr('productId');
        $.ajax({
            url:'note',
            data:{note:note,id:id},
            type: 'post',
            dataType:'json',
        });
       });

    //税点change
    $("input.pur_ticketed_point").keyup(function() {
        var value = $(this).val();
        var re = /^\d+(?=\.{0,1}[0-9]{0,2}$|$)/ 
        if (!re.test(value)) {
            $(this).val('');
            return false;
        }
        if (value >= 100) {
            $(this).val('');
            return false;
        }
        $(this).val(value);
    })

$(document).on('click', '#creates', function () {

        $.get($(this).attr('href'), {id:$(this).attr('value')},
            function (data) {
                $('.modal-body').html(data);
            }
        );
    });
    
    $('.source_status').on('change',function() {
        var thissku = $(this).attr('sku');
        var beforeVal = $(this).attr('status');
        var changeVal = $(this).val();
        var obj       = $(this);
        layer.confirm('是否修改'+thissku+'的货源状态',{title:"修改提示"},function() {
          $.post('{$changeUrl}',{sku:thissku,change_value:changeVal},function(data) {
                var response = JSON.parse(data);
                layer.msg(response.message);
                if(response.status=='error'){
                    obj.val(beforeVal);
                }else {
                    obj.attr('status',changeVal);
                }
                
          });
        },function() {
            obj.val(beforeVal);
        });
    });

    $(document).on('click','#source_status',function() {
        var skus= new Array();
        //获取所有的值
        $("input[name='id[]']:checked").each(function(){
            skus.push($(this).val());
        });
        if(skus.length<1){
            $('.modal-body').html('至少选择一个sku');
        }else {
            $.get($(this).attr('href'), {skus:skus.join(',')},
            function (data) {
                $('.modal-body').html(data);
            }
        );
        }
    });

JS;
$this->registerJs($js);
?>
