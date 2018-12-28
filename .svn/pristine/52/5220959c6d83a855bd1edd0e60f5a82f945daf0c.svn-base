<?php

use yii\helpers\Html;

use kartik\grid\GridView;
use Yii\helpers\Url;
use yii\bootstrap\Modal;
use app\config\Vhelper;
use \app\models\SupplierUpdateApply;
use \app\services\SupplierServices;
use \app\services\SupplierGoodsServices;
/* @var $this yii\web\View */
/* @var $searchModel app\models\SupplierGoodsSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', '供应商整合');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="stockin-index">
    <?= $this->render('_search', ['model' => $searchModel,'view'=>'integrat']); ?>
    <p class="clearfix"></p>
    <?= Html::a(Yii::t('app', '整合成功'), '#', [
        'id' => 'integrat',
        'class' => 'btn btn-success',
    ]);?>
    <?= Html::a(Yii::t('app', '整合不成功'), '#', [
        'id' => 'integratno',
        'data-toggle' => 'modal',
        'data-target' => '#create-modal',
        'class' => 'btn btn-danger',
    ]);?>

    <?= Html::a(Yii::t('app', '取消整合'), '#', [
        'id' => 'cancelIntegrat',
        'class' => 'btn btn-danger',
    ]);?>
    <?= Html::a(Yii::t('app', '批量备注'), '#', [
        'id' => 'batchnote',
        'data-toggle' => 'modal',
        'data-target' => '#create-modal',
        'class' => 'btn btn-info',
    ]);?>
    <?= Html::a('导出', ['#'], ['class' => 'btn btn-success interExport','id'=>'bulk-execl','target'=>'_blank']) ?>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        //'filterModel' => $searchModel,
        "options" => ["class" => "grid-view","style"=>"overflow:auto", "id" => "grid"],
        'filterSelector' => "select[name='".$dataProvider->getPagination()->pageSizeParam."'],input[name='".$dataProvider->getPagination()->pageParam."']",
        'pager'=>[
            //'options'=>['class'=>'hidden']//关闭分页
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
                'class' => 'kartik\grid\CheckboxColumn',
                'name'=>"id" ,
                'checkboxOptions' => function ($model, $key, $index, $column) {
                    return ['value' => $model->id];
                }

            ],
            [
                'label'=>'审核状态',
                'format'=>'raw',
                'width' =>'80px',
                'value'=>function($model){
                      //return SupplierServices::getApplyStatus($model->status);
                    switch($model->status){
                        case 1:
                            return SupplierServices::getApplyStatus($model->status);
                        case 2:
                            return SupplierServices::getApplyStatus($model->status).'<br/>'.$model->update_user_name.'<br/>'.date('Y-m-d',strtotime($model->update_time));
                        case 3:
                            return SupplierServices::getApplyStatus($model->status).'<br/>'.$model->update_user_name.'<br/>'.date('Y-m-d',strtotime($model->update_time)).'<br/>'.$model->refuse_reason;
                    }
                }

            ],
            [
                'label' =>'申请情况',
                'format'=>'raw',
                'width' =>'80px',
                'value' => function($model){
                    return $model->create_user_name.'<br/>'.date('Y-m-d',strtotime($model->create_time));
                   // return '申请人:'.$model->create_user_name.'<br/>申请时间:'.$model->create_time.'<br/>审核人:'.$model->update_user_name.'<br/>审核时间:'.$model->update_time;
                }
            ],
            [
//                'label' =>'整合状态',
                'attribute'=>'integrat_status',
                'format'=>'raw',
                'value' => function($model, $key, $index, $column){
                    switch($model->integrat_status){
                        case 1:
                            return SupplierServices::getIntegratStatus($model->integrat_status);
                        case 2:
                            return SupplierServices::getIntegratStatus($model->integrat_status);
                        case 3:
                            return SupplierServices::getIntegratStatus($model->integrat_status).'<br/>整合失败原因：'.$model->fail_reason;
                    }
                },

            ],
            [
                'label' => 'SKU创建时间',
                'format'=> 'raw',
                'value' => function($model){
                    return  $model->productDetail->create_time;
                }
            ],
//            [
//                'label' => '上次修改供应商时间',
//                'format'=> 'raw',
//                'value' => function($model){
//                    $data = SupplierUpdateApply::find()->where(['status'=>2,'sku'=>$model->sku])->andWhere('update_time < :time',[':time'=>$model->create_time])->orderBy('update_time DESC')->one();
//                    return !empty($data) ? $data->update_time : '';
//                }
//            ],
            [
                'label' => 'SKU状态',
                'format'=> 'raw',
                'value' => function($model){
                    return !empty($model->productDetail) ? SupplierGoodsServices::getProductStatus($model->productDetail->product_status) : '';
                }
            ],
//            [
//                'label' => '品类',
//                'format'=> 'raw',
//                'value' => function($model){
//                    return !empty($model->productDetail) ? !empty($model->productDetail->cat) ? $model->productDetail->cat->category_cn_name : '' : '';
//                }
//            ],
            [
                'label'=>'产品线',
                'attribute'=>'uploadimgss',
                'format'=>'raw',
                'value'=> function ($model, $key, $index, $column) {
                    $product= !empty($model->productDes)&&!empty($model->productDes->product_linelist_id) ? \app\services\BaseServices::getProductLine($model->productDes->product_linelist_id): '';
                    return $product;
                }
            ],
            [
                'label' => '图片',
                'format'=> 'raw',
                'value' => function($model){
                    return Html::a(\toriphes\lazyload\LazyLoad::widget(['src'=>Vhelper::getSkuImage($model->sku),'width'=>'80px','height'=>'80px']),Vhelper::getSkuImage($model->sku),['target'=>'_blank']);
                }
            ],
            [
                'label' => 'SKU',
                'format'=> 'raw',
                'value' => function($model){
                    return Html::a($model->sku, Yii::$app->params['SKU_ERP_Product_Detail'].$model->sku,['target'=>'blank']);
                }
            ],
            [
                'label' => '品名',
                'format'=> 'raw',
                'value' => function($model){
                    return !empty($model->productDetail) ? !empty($model->productDetail->desc) ? $model->productDetail->desc->title : '' : '';
                }
            ],
            [
                'label' => '原单价',
                'format'=> 'raw',
                'value' => function($model){
                    return !empty($model->oldQuotes) ? $model->oldQuotes->supplierprice : '';
                }

            ],
            [
                'label' => '原供应商',
                'format'=> 'raw',
                'value' => function($model){
//                    $supplier = !empty($model->oldSupplier) ? $model->oldSupplier->supplier_name : '';
//                    return $supplier.Html::a('<span class="glyphicon glyphicon-list-alt" style="font-size:20px;color:#f2adb1;margin-right:10px;" title="单击，查看采购单明细"></span>', ['get-purchase-log'],['id' => 'logs',
//                        'data-toggle' => 'modal',
//                        'data-target' => '#create-modal','value' =>$model->sku,
//                    ]);
                    $supplier = !empty($model->oldSupplier) ? $model->oldSupplier->supplier_name : '';
                    $num = !empty($model->old_supplier_code) ? SupplierUpdateApply::getProduct($model->old_supplier_code) :0;
                    //$data = $supplier.'<br/>'.'供应商绑定sku数量：'.$model->old_product_num;
                    $data = $supplier.'<br/>'.'供应商绑定sku数量：'.$num;
                    return !empty($model->oldSupplier) ? $data : '';
                }
            ],
            [
                'label' => '现单价',
                'format'=> 'raw',
                'value' => function($model){
                    return !empty($model->newQuotes) ? $model->newQuotes->supplierprice : '';
                }

            ],
            [
                'label' => '现在供应商',
                'format'=> 'raw',
                'value' => function($model){
//                    $supplier = !empty($model->newSupplier) ? $model->newSupplier->supplier_name : '';
//                    return $supplier.Html::a('<span class="glyphicon glyphicon-list-alt" style="font-size:20px;color:#f2adb1;margin-right:10px;" title="单击，查看采购单明细"></span>', ['get-purchase-log'],['id' => 'logs',
//                        'data-toggle' => 'modal',
//                        'data-target' => '#create-modal','value' =>$model->sku,
//                    ]);
                    $supplier = !empty($model->newSupplier) ? $model->newSupplier->supplier_name : '';
                    $num = !empty($model->new_supplier_code) ? SupplierUpdateApply::getProduct($model->new_supplier_code) :0;
                    //$data = $supplier.'<br/>'.'供应商绑定sku数量：'.$model->new_product_num;
                    $data = $supplier.'<br/>'.'供应商绑定sku数量：'.$num;
                    return !empty($model->newSupplier) ? $data : '';
                }
            ],
            [
                'label' => '是否拿样',
                'format'=> 'raw',
                'value' => function($model){
//                    $html= Html::a(Yii::t('app', '拿样'), '#', [
//                        'applyId'=>$model->id,
//                        'type'  => 'sample',
//                        'class' => 'btn btn-success btn-sm sample',
//                        'style'=> 'margin-top:5px'
//                    ]);
//                    $html.= Html::a(Yii::t('app', '不拿样'), '#', [
//                        'applyId'=>$model->id,
//                        'type'  => 'sampleno',
//                        'class' => 'btn btn-danger btn-sm sample',
//                        'style'=> 'margin-top:5px'
//                    ]);
//                    return $model->is_sample == 1&&$model->create_user_id == Yii::$app->user->id && $model->status ==1 ? SupplierServices::getSampleStatus($model->is_sample).'<br/>'.$html : SupplierServices::getSampleStatus($model->is_sample);
                    return !empty($model->is_sample) ? SupplierServices::getSampleStatus($model->is_sample) : '';
                }
            ],
            [
                'label' => '质检测试样品结果',
                'format'=> 'raw',
                'value' => function($model){
                    $status = !empty($model->qualityResult) ? !empty($model->qualityResult->qc_result) ? SupplierServices::getSampleResultStatus($model->qualityResult->qc_result) : '' : '';
                    $reason = !empty($model->qualityResult) ? $model->qualityResult->reason : '';
                    $time   = !empty($model->qualityResult) ? $model->qualityResult->confirm_time : '';
                    if($model->is_sample ==3){
                        if(!empty($model->qualityResult)){
                            switch($model->qualityResult->qc_result){
                                case 1:
                                    return $status;
                                case 2:
                                    return $status.'<br/>质检结果确认人:'.$model->qualityResult->confirm_user_name.'<br/>确认时间:'.$time;
                                case 3:
                                    return $status.'<br/>质检结果确认人:'.$model->qualityResult->confirm_user_name.'<br/>确认时间:'.$time.'<br/>不合格原因：'.$reason;
                                default:
                                    return $status;
                            }
                        }
                    }
                    return'';
                }
            ],
            [
                'label'=>'整合备注',
                'format'=>'raw',
                'value'=>function($model){
                    return Html::input('text','integrat_note',$model->integrat_note,['class'=>'note','readonly'=>'readonly','applyId'=>$model->id,'style'=>'width:100px']);
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
$requestUrl = Url::toRoute('integrat');
$request = Url::toRoute('integratno');
$note  = Url::toRoute('note');

$sampleUrl = Url::toRoute('sample');
$samplenoUrl = Url::toRoute('sampleno');
$exportUrl   = Url::toRoute('export');
$js = <<<JS

    $("#cancelIntegrat").on('click',function(){
        var ids = new Array();
        $('[name="id[]"]').each(function(){
            if($(this).is(':checked')){
                ids.push($(this).val());
            }
       });
        if(ids.length ==0){
            alert('至少选择一个');
        }else {
            $.ajax({
                url:'cancel-integrat',
                data:{ids:ids},
                type: 'post',
                dataType:'json',
                success:function(data){
                }
            });
        }
       });
    $(document).on('click', '#integrat', function () {
       var ids = new Array();
       $('[name="id[]"]').each(function(){
            if($(this).is(':checked')){
                ids.push($(this).val());
            }
       });
       if(ids.length < 1){
            alert('至少选择一个！');
            return false;
       }else {
            $.get('{$requestUrl}', {id:ids.join(',')}
        );
        }
    });

    $(document).on('click', '#integratno', function () {
       var ids = new Array();
       $('[name="id[]"]').each(function(){
            if($(this).is(':checked')){
                ids.push($(this).val());
            }
       });
       if(ids.length ==0){
            $('.modal-body').html('至少选择一个');
            return false;
       }else {

            $.get('{$request}', {ids:ids.join(',')},
            function (data) {
                $('.modal-body').html(data);
            }
        );
        }
    });

//双击编辑报价
    $(".note").dblclick(function(){
            $(this).removeAttr("readonly");
        });
    //失焦添加readonly
    $(".note").change(function(){
        $(this).attr("readonly","true");
        var note = $(this).val();
        var id   = $(this).attr('applyId');
        $.ajax({
            url:'note',
            data:{note:note,id:id},
            type: 'post',
            dataType:'json',
            success:function(data){
                $(this).val(data.note);
            }
        });
       });

    $('.interExport').on('click',function(){
        var applyName = $('[name="SupplierUpdateApplySearch[create_user_name]"').val();
        var start = $('[name="SupplierUpdateApplySearch[apply_start_time]"').val();
        var end = $('[name="SupplierUpdateApplySearch[apply_end_time]"').val();
        var url = "{$exportUrl}"+'?applyname='+applyName+'&start='+start+'&end='+end;
        $(this).attr('href',url);
    });

    $('#batchnote').on('click',function(){
       var ids = new Array();
       $('[name="id[]"]').each(function(){
            if($(this).is(':checked')){
                ids.push($(this).val());
            }
       });
        $.get('{$note}', {id:ids.join(',')},
            function (data) {
                $('.modal-body').html(data);
            }
        );
    });
//    $(document).on('click', '.sample', function () {
//            var  applyId = $(this).attr('applyId');
//            var  type    = $(this).attr('type');
//            $.get('sampleUrl', {id:applyId,type:type});
//         });

JS;
$this->registerJs($js);
?>
