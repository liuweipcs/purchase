<?php

use yii\helpers\Html;

use kartik\grid\GridView;
use Yii\helpers\Url;
use yii\bootstrap\Modal;
use app\config\Vhelper;
use kartik\select2\Select2;
use yii\web\JsExpression;
use app\services\BaseServices;
use app\services\SupplierServices;
/* @var $this yii\web\View */
/* @var $searchModel app\models\SupplierGoodsSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'SKU销量信息');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="stockin-index">


    <?php echo $this->render('_search1', ['model' => $searchModel]);?>
    <?php /* $this->render('_search1', ['model' => $searchModel])*/?>
    <p class="clearfix"></p>
    <?php Html::a(Yii::t('app', '批量更新默认供应商'), '#', [
        'id' => 'batch',
        'data-toggle' => 'modal',
        'data-target' => '#create-modal',
        'class' => 'btn btn-success',
    ]);?>
    <?php //Html::a('导出', ['#'],['class' => 'btn btn-info','id'=>'exportamount','target'=>'_blank']) ?>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        "options" => ["class" => "grid-view","style"=>"overflow:auto", "id" => "grid"],
    		'filterSelector' => "select[name='".$dataProvider->getPagination()->pageSizeParam."'],input[name='".$dataProvider->getPagination()->pageParam."']",
    		'pager'=>[
    				'options'=>['class' => 'pagination','style'=> "display:block;"],
    				'class'=>\liyunfang\pager\LinkPager::className(),
    				'pageSizeList' => [20, 50, 100, 200],
    				//                'options'=>['class'=>'hidden'],//关闭分页
    				'firstPageLabel'=>"首页",
    				'prevPageLabel'=>'上一页',
    				'nextPageLabel'=>'下一页',
    				'lastPageLabel'=>'末页',
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
                    $link  = !empty($model->supplierQuote) ? $model->supplierQuote->supplier_product_address : '';
                    return ['value' => $model->sku,'supplierCode'=>$supplier,'price'=>$price,'link'=>$link];
                }

            ],
//             [
//                 'label'=>'审核状态',
//                 'format'=>'raw',
//                 'value'=>function($model){
//                     return !empty($model->updateApply) ? '有' : '无';
//                 }
//             ],
//             [
//                 'label'=>'产品状态',
//                 'attribute'=>'uploadimgss',
//                 'format'=>'raw',
//                 'value'=> function ($model, $key, $index, $column) {


//                     $product= app\services\SupplierGoodsServices::getProductStatus($model->product_status)."<br/>";

//                     return $product;
//                 }
//             ],
//             [
//                 'label'=>'首次合作时间',
//                 'format'=>'raw',
//                 'value'=>function($model){
//                     return date('Y-m-d H:i:s',$model->suppliers->create_time);
//                 }
//             ],
            [
            'label'=>'sku',
//             'attribute'=>'sku',
            'format'=>'raw',
            'value'=> function ($model, $key, $index, $column)
            {
            	//$product = Vhelper::toSkuImgUrl($model->sku,$model->uploadimgs);
            
            	return Html::a($model->sku, Yii::$app->params['SKU_ERP_Product_Detail'].$model->sku,['target'=>'blank']);
            	//return Html::a($model->sku, Yii::$app->params['SKU_ERP_Product_Detail'].$model->sku,['target'=>'blank']).Html::a('',$product,['target'=>'blank','class'=>'glyphicon glyphicon-picture']);
            
            }
            ],
            [
            'label'=>'单价',
            'format'=>'raw',
            'attribute'=>'price',
            'value'=> function($model){
            $price =  !empty($model->price) ? $model->price : '';
            return Html::input('text',"SupplierQuotes[$model->id][supplierprice]",$price,['readonly'=>true,'class'=>'price dbclick','style'=>'width:70px']);
            }
            ],
            [
            'label'=>'产品图片',
            'attribute'=>'uploadimgss',
            'format'=>'raw',
            'value'=> function ($model, $key, $index, $column) {
            $product = Html::img(Vhelper::downloadImg($model->sku,'',2),['width'=>'80px']);
            return $product;
            }
            ],
            [
            'label'=>'产品名称',
            //'attribute'=>'uploadimgss',
            'format'=>'raw',
            'width'=>'300px',
            'value'=> function ($model, $key, $index, $column) {
            $product = !empty($model->name)?$model->name."<br/>":''."<br/>";
            return $product;
            }
             
            ],
            [
            'label'=>'供应商',
            'attribute'=>'uploadimgss',
            'format'=>'raw',
             'value'=> function ($model) {
//                  if($model->purchaseSupplier!=null){
                  	return $model->supplier_name;
//                       }else {
//                    	 return '';
//                       }     
                  }
              ],
              [
              'label'=>'采购员',
              'attribute'=>'uploadimgss',
              'format'=>'raw',
              'value'=> function ($model) {
                               if($model->supplierbuyer!=null){
              return $model->supplierbuyer[0]->buyer;
                                    }else {
                                 	 return '';
                                    }
              }
              ],
              [
              'label'=>'3天销量',
              'attribute'=>'days_sales_3',
              'format'=>'raw',
              'value'=> function ($model) {
                  if($model->days_sales_3!=null){
                    return $model->days_sales_3;
                  }else {
                  	return 0;
                  }
               
              }
              ],
              [
              'label'=>'15天销量',
              'attribute'=>'days_sales_15',
              'format'=>'raw',
              'value'=> function ($model) {
	              if($model->days_sales_15!=null){
	                return $model->days_sales_15;
	              }else{
	              	return 0;
	              }
               
              }
              ],
              [
              'label'=>'30天销量',
              'attribute'=>'days_sales_30',
              'format'=>'raw',
              'value'=> function ($model) {
	              if($model->days_sales_30!=null){
	                return $model->days_sales_30;
	              }else{
	              	return 0;
	              }
              }
              ],
              [
              'label'=>'60天销量',
              'attribute'=>'days_sales_60',
              'format'=>'raw',
              'value'=> function ($model) {
	              if($model->days_sales_60!=null){
	              return $model->days_sales_60;
	              }else{
	              	return 0;
	              }
              }
              ],
              [
              'label'=>'90天销量',
              'attribute'=>'days_sales_90',
              'format'=>'raw',
              'value'=> function ($model) {
                 if($model->days_sales_90!=null){
                  return $model->days_sales_90;
                  }else{
                  	return 0;
                  }
              }
              ],
              [
              'label'=>'下单量',
              'attribute'=>'qty_13',
              'format'=>'raw',
              'value'=> function ($model) {
              $qty_13=isset($model->qty_13)?$model->qty_13:0;
              return $qty_13;
               
              }
              ],
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
$exportUrl  = Url::toRoute('exportamount');
$standbyUrl  = Url::toRoute('standby-supplier');
$supplierUrl  = Url::toRoute('supplier/update');
//$requestUrl = Url::toRoute('create');
$js = <<<JS
    $(document).on('click','#exportamount',function() {
        var str='';
        //获取所有的值
        $("input[name='id[]']:checked").each(function(){
            str+=','+$(this).val()+'_'+$(this).attr('pur_number');
        })
        str=str.substr(1);
        if(str ==''){
            alert('请至少选择一个');exit();
        }else{
//         alert(str);return false;
        $(this).attr('href',"{$exportUrl}?purNumber="+str);
       }
    });
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
        $.get('{$supplierUrl}', {id:id},
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

    $("input.link").blur(function(){
        $(this).attr("readonly","true");
        //将修改的值传入复选框属性
        $(this).closest('tr').find('input[name="id[]"]').attr('link',$(this).val());
    });

    $("select").change(function(){
            var supplier = $(this).select2("val");
            //将数据写入复选框属性
            $(this).closest('tr').find('input[name="id[]"]').attr('suppliercode',supplier);
        });

    $('.apply').click(function(){
        var data = new Array();
        var type = $(this).attr('type');
        $('input[name="id[]"]').each(function(){
            if($(this).is(':checked')){
                var skudata = {quoteId:$(this).attr('quotesid'),sku:$(this).val(),suppliercode:$(this).attr('suppliercode'),price:$(this).attr('price'),link:$(this).attr('link')}
                data.push(skudata);
            }
        })
        if(data.length == 0){
            alert('请至少选择一个！');
        }else {
            $.ajax({
            url:'apply',
            data:{data:data,type:type},
            type: 'post',
            dataType:'json',
            success:function(data){
                window.location.reload();
            }
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
