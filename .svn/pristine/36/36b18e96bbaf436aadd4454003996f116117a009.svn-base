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

$this->title = Yii::t('app', '采购单信息');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="stockin-index">


    <?php echo $this->render('_search', ['model' => $searchModel]);?>
    <?php /* $this->render('_search1', ['model' => $searchModel])*/?>
    <p class="clearfix"></p>
    <?php Html::a(Yii::t('app', '批量更新默认供应商'), '#', [
        'id' => 'batch',
        'data-toggle' => 'modal',
        'data-target' => '#create-modal',
        'class' => 'btn btn-success',
    ]);?>
    <?= Html::a('导出', ['#'],['class' => 'btn btn-info','id'=>'exportamount','target'=>'_blank']) ?>
    <?= Html::a('导出全部', ['#'],['class' => 'btn btn-info','id'=>'exportalldata','target'=>'_blank']) ?>
    <?= Html::a('导出汇总信息', ['#'],['class' => 'btn btn-info','id'=>'exportsumdata','target'=>'_blank']) ?>
    <?php // Html::button('导出全部',['class' => 'btn btn-info','id'=>'exportAllamount']) ?>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        "options" => ["class" => "grid-view","style"=>"overflow:auto", "id" => "grid"],
        'pager'=>[
            //'options'=>['class'=>'hidden']//关闭分页
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
                    return ['value' => $model->sku,'pur_number'=>$model->pur_number,'supplierCode'=>$supplier,'price'=>$price,'link'=>$link];
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
            [
                'label'=>'首次合作时间',
                'format'=>'raw',
                'value'=>function($model){
	                if ($model->suppliers!=NULL){
	                    return date('Y-m-d H:i:s',$model->suppliers->create_time);	                
	            }else {
	            	return '';
	              }
	            }
            ],
               [
                   'label'=>'供应商',
                   'attribute'=>'uploadimgss',
                   'format'=>'raw',
                   'value'=> function ($model) {
                       if($model->suppliers!=null){
                       	return $model->suppliers->supplier_name;
                       }else {
                       	return '';
                       }
                  
                     
                  }
              ],
              [
              'label'=>'结算方式',
              'attribute'=>'uploadimgss',
              'format'=>'raw',
              'value'=> function ($model) {
              if(!empty($model->suppliers->supplier_settlement)){
              	return  !empty($model->suppliers->supplier_settlement)?SupplierServices::getSettlementMethod($model->suppliers->supplier_settlement):'';
              }else {
              	return '';
              }
              
               
              }
              ],
              [
              'label'=>'PO号',
              'attribute'=>'pur_number',
              'format'=>'raw',
              'value'=> function ($model) {
                  return $model->pur_number;
               
              }
              ],
              
//             [
//                 'label'=>'产品线',
//                 'attribute'=>'uploadimgss',
//                 'format'=>'raw',
//                 'value'=> function ($model) {
//                     $product= !empty($model->product_category) ? BaseServices::getProductLine($model->product_category): '';
//                     return $product;
//                 }
//             ],
            [
            'label'=>'产品线',
            'attribute'=>'uploadimgss',
            'format'=>'raw',
            'value'=> function ($model, $key, $index, $column) {
                $product= !empty($model->product->product_category_id) ? BaseServices::getCategory($model->product->product_category_id): '';
                return $product;
            },
//             'filter'=>BaseServices::getCategory(),
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
                'label'=>'sku',
                //'attribute'=>'uploadimgss',
                'format'=>'raw',
                'value'=> function ($model, $key, $index, $column)
                {
                    //$product = Vhelper::toSkuImgUrl($model->sku,$model->uploadimgs);

                    return Html::a($model->sku, Yii::$app->params['SKU_ERP_Product_Detail'].$model->sku,['target'=>'blank']);
                    //return Html::a($model->sku, Yii::$app->params['SKU_ERP_Product_Detail'].$model->sku,['target'=>'blank']).Html::a('',$product,['target'=>'blank','class'=>'glyphicon glyphicon-picture']);

                }
            ],
            [
                'label'=>'产品名称',
                //'attribute'=>'uploadimgss',
                'format'=>'raw',
            	'width'=>'300px',
                'value'=> function ($model, $key, $index, $column) {
                    $product = !empty($model->desc->title)?$model->desc->title."<br/>":''."<br/>";
                    return $product;
                }
               
            ],
            [
            'label'=>'下单时间',
            //'attribute'=>'uploadimgss',
            'format'=>'raw',
            'value'=> function ($model, $key, $index, $column) {
            $product = !empty($model->purNumber->created_at)?$model->purNumber->created_at."<br/>":''."<br/>";
            return $product;
            }
            ],
            [
                'label'=>'单价',
                'format'=>'raw',
                'value'=> function($model){
                    $price =  !empty($model->price) ? $model->price : '';
                    return Html::input('text',"SupplierQuotes[$model->id][supplierprice]",$price,['readonly'=>true,'class'=>'price dbclick','style'=>'width:70px']);
                }
            ],
            [
            'label'=>'数量',
            'format'=>'raw',
            'pageSummary'=>true,
            'value'=> function($model){
            $ctq =  !empty($model->ctq) ? $model->ctq : 0;
            return $ctq;
            //return Html::input('text',"SupplierQuotes[$model->id][supplierprice]",$ctq,['readonly'=>true,'class'=>'price dbclick','style'=>'width:70px']);
            }
            ],
            [
            'label'=>'金额',
            'format'=>'raw',
            'pageSummary'=>true,
            'value'=> function($model){          
            $price =  !empty($model->items_totalprice) ? $model->items_totalprice : 0;
            return $price;
           // return Html::input('text',"SupplierQuotes[$model->id][supplierprice]",$price,['readonly'=>true,'class'=>'price dbclick','style'=>'width:70px']);
            }
            ],
            [
            'label'=>'采购员',
            'format'=>'raw',
            'value'=> function($model){
            $buyer =  !empty($model->purNumber->buyer) ? $model->purNumber->buyer : '';
            return $buyer;
            }
            ],
            [
            'label'=>'采购类型',
            'format'=>'raw',
            'value'=> function($model){
            $purchase_type =  !empty($model->purNumber->purchase_type) ? $model->purNumber->purchase_type : '';
	            if($purchase_type==1){
	          	    return '国内';
	            }elseif($purchase_type==2){
	            	return '海外';
	            }elseif ($purchase_type==3){
	            	return 'FBA';
	            }
            }
            ],
//             [
//                 'label'=>'默认供应商编码',
//                 'format'=>'raw',
//                 'value'=>function($model){
//                     $url = \yii\helpers\Url::to(['/supplier/search-supplier']);
//                     return Select2::widget([ 'name' => 'title',
//                         'options' => ['placeholder' => '请输入供应商 ...'],
//                         'value'    =>!empty($model->defaultSupplierDetail) ? $model->defaultSupplierDetail->supplier_code : '',
//                         'pluginOptions' => [
//                             'placeholder' => 'search ...',
//                             'allowClear' => true,
//                             'language' => [
//                                 'errorLoading' => new JsExpression("function () { return 'Waiting...'; }"),
//                             ],
//                             'ajax' => [
//                                 'url' => $url,
//                                 'dataType' => 'json',
//                                 'data' => new JsExpression("function(params) { return {q:params.term}; }")
//                             ],
//                             'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
//                             'templateResult' => new JsExpression('function(res) { return res.text; }'),
//                             'templateSelection' => new JsExpression('function (res) { return res.id; }'),
//                         ],
//                     ]);
//                     //return !empty($model->defaultSupplierDetail) ? $model->defaultSupplierDetail->supplier_name : '';
//                 }
//             ],
//             [
//                 'label'=>'默认供应商名称',
//                 'format'=>'raw',
//                 'value'=>function($model){
//                     $supplierName = !empty($model->defaultSupplierDetail) ? $model->defaultSupplierDetail->supplier_name : '';
//                     $supplierCode = !empty($model->defaultSupplierDetail) ? $model->defaultSupplierDetail->supplier_code : '';
//                     $supplier = \app\models\Supplier::find()->andFilterWhere(['supplier_code'=>$supplierCode])->one();
//                     $html=Html::a($supplierName, ['#'],
//                         [
//                             'class' => 'btn btn-xs supplier',
//                             'data-toggle' => 'modal',
//                             'data-target' => '#create-modal',
//                             'value' =>$supplier->id
//                         ]);
//                     $html.= Html::a('<span class="glyphicon glyphicon-zoom-in" style="font-size:20px;color:#f2adb1;margin-right:10px;" title="单击，查看备用供应商"></span>', ['#'],
//                         [
//                             'class' => 'btn btn-xs standby',
//                             'data-toggle' => 'modal',
//                             'data-target' => '#create-modal',
//                             'value' =>$model->sku
//                         ]);
//                     return $html;
//                 }
//             ],
//             [
//                 'label'=>'供应链人员',
//                 'format'=>'raw',
//                 'value'=>function($model){
//                     return '供应链人员B';
//                 }
//             ],
//             [
//                 'label'=>'采购员',
//                 'format'=>'raw',
//                 'value'=>function($model){
//                     return !empty($model->supplierQuote) ? !empty($model->supplierQuote->default_buyer) ? BaseServices::getEveryOne($model->supplierQuote->default_buyer) : '' : '';

//                 }
//             ],
//             [
//                 'label'=>'采购链接',
//                 'format'=>'raw',
//                 'value' =>function($model){
//                     $link = !empty($model->supplierQuote) ? $model->supplierQuote->supplier_product_address : '';
//                     return Html::input('text',"SupplierQuotes[$model->id][link]",$link,['readonly'=>true,'class'=>'link dbclick']);
//                 }
//             ],
//             [
//                 'label'=>'开发员',
//                 'format'=>'raw',
//                 'value'=>function($model){
//                     return $model->create_id;
//                 }
//             ],
//             [
//                 'label'=>'备注',
//                 'format'=>'raw',
//                 'value'=>function($model){
//                     return Html::input('text','note',$model->note,['readonly'=>'readonly','productId'=>$model->id,'style'=>'width:70px']);
//                 }
//             ],


        ],
        'containerOptions' => ["style"=>"overflow:auto"], // only set when $responsive = false
        'toolbar' =>  [

            //'{export}',
        ],


//         'pjax' => false,
//         'bordered' => true,
//         'striped' => false,
//         'condensed' => true,
//         'responsive' => true,
//         'hover' => false,
//         'floatHeader' => false,
//         'showPageSummary' => false,

//         'exportConfig' => [
//             GridView::EXCEL => [],
//         ],
//         'panel' => [
//             //'heading'=>'<h3 class="panel-title"><i class="glyphicon glyphicon-globe"></i> Countries</h3>',
//             'type'=>'success',
//             //'before'=>false,
//             //'after'=>false,
//             //'after'=>Html::a('<i class="glyphicon glyphicon-repeat"></i> 刷新', ['index'], ['class' => 'btn btn-info']),
//             //'footer'=>true
//         ],
        'pjax' => false,
        'bordered' => true,
        'striped' => false,
        'condensed' => true,
        'responsive' => true,
        'hover' => false,
        'floatHeader' => false,
        'showPageSummary' => true,
        'toggleDataOptions' =>[
        		'maxCount' => 5000,
        		'minCount' => 1000,
        		'confirmMsg' => Yii::t(
        				'app',
        				'有{totalCount} 记录. 您确定要全部显示?',
        				['totalCount' => number_format($dataProvider->getTotalCount())]
        				),
        		'all' => [
        				'icon' => 'resize-full',
        				'label' => Yii::t('app', '所有'),
        				'class' => 'btn btn-default',
        
        		],
        		'page' => [
        				'icon' => 'resize-small',
        				'label' => Yii::t('app', '单页'),
        				'class' => 'btn btn-default',
        
        		],
        ],
        'exportConfig' => [
        		GridView::EXCEL => [],
        ],
        'panel' => [
        		//'heading'=>'<h3 class="panel-title"><i class="glyphicon glyphicon-globe"></i> Countries</h3>',
        		'type'=>'success',
        		//'after'=>Html::a('<i class="glyphicon glyphicon-repeat"></i> 刷新', ['index'], ['class' => 'btn btn-info']),
        		//'footer'=>true
        ],
    ]);?>
</div>
<div id="exportdata"></div>
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
$exportallUrl  = Url::toRoute('exportalldata');
$exportsumUrl  = Url::toRoute('exportsumdata');
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
    $(document).on('click','#exportalldata',function() {
//    var product_category_id=$('#purchaseamountsearch-product_category_id').val();
//      var product_category_id=$('select[name="PurchaseAmountSearch[product_category_id]"]').val();
        var supplier_code=$('select[name="PurchaseAmountSearch[supplier_code]"]').val();
//        var purchase_type=$('select[name="PurchaseAmountSearch[purchase_type]"]').val();
//        var sku=$('input[name="PurchaseAmountSearch[sku]"]').val();
        var agree_time=$('input[name="PurchaseAmountSearch[agree_time]"]').val();
//         var fromstring = '';
//         var inputstring=$('.expressdata').html();alert(inputstring);
// 		fromstring += '<form method="POST" target="_blank" action="{$exportallUrl}" id="exportAlidata">';
// 		fromstring +='<input type="text" name="product_category_id" class="date"   value="'+product_category_id+'"/>';
// 		fromstring +='<input type="text" name="supplier_code" class="date"  value="'+supplier_code+'"/>';
// 		fromstring +='<input type="text" name="purchase_type"  value="'+purchase_type+'"/>';
// 		fromstring +='<input type="text" name="sku"  value="'+sku+'"/>';
// 		fromstring +='<input type="text" name="agree_time"  value="'+agree_time+'"/>';
// 		fromstring += '</form>';
// 		$('#exportAlidata').serialize();

//         $('#exportdata').html('');
// 		$('#exportdata').html(fromstring);
// 		$('#exportAlidata').submit();
 $(this).attr('href',"{$exportallUrl}?agree_time="+agree_time+'&supplier_code='+supplier_code);
    });
    $(document).on('click','#exportsumdata',function() {
        var supplier_code=$('select[name="PurchaseAmountSearch[supplier_code]"]').val();
        var agree_time=$('input[name="PurchaseAmountSearch[agree_time]"]').val();
        $(this).attr('href',"{$exportsumUrl}?agree_time="+agree_time+'&supplier_code='+supplier_code);
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
