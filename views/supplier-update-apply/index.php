<?php

use yii\helpers\Html;

use kartik\grid\GridView;
use Yii\helpers\Url;
use yii\bootstrap\Modal;
use app\config\Vhelper;
use yii\widgets\ActiveForm;
use \app\models\SupplierUpdateApply;
use \app\services\SupplierServices;
use kartik\select2\Select2;
use yii\web\JsExpression;
/* @var $this yii\web\View */
/* @var $searchModel app\models\SupplierGoodsSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', '供应商信息修改审核');
$this->params['breadcrumbs'][] = $this->title;
?>

<div >
    <?= $this->render('_search', ['model' => $searchModel,'view'=>'index']); ?>
    <p class="clearfix"></p>
    <div style="position:fixed; left:16%; top:20%;">
    <?= Html::button(Yii::t('app', '审核通过'), [
        'id' => 'check',
        'class' => 'btn btn-success',
    ]);?>

    <?= Html::a(Yii::t('app', '审核不通过'), '#', [
        'id' => 'checkno',
        'data-toggle' => 'modal',
        'data-target' => '#create-modal',
        'class' => 'btn btn-danger',
    ]);?>
    <?= Html::a(Yii::t('app', '拿样'), '#', [
        'type'  => 'sample',
        'class' => 'btn btn-success batchsample',
    ]);?>
    <?= Html::a(Yii::t('app', '不拿样'), '#', [
        'type'  => 'sampleno',
        'class' => 'btn btn-danger batchsample',
    ]);?>
    </div>
    <div>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        //'filterModel' => $searchModel,
        "options" => ["class" => "grid-view","style"=>"overflow:auto", "id" => "grid"],
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
            [
                'class' => 'kartik\grid\CheckboxColumn',
                'name'=>"id" ,
                'checkboxOptions' => function ($model, $key, $index, $column) {
                    return ['value' => $model->id];
                }

            ],
            [
                'label' =>'SKU创建时间',
                'format'=>'raw',
                'value' => function($model, $key, $index, $column){
                    return  !empty($model->productDetail) ? $model->productDetail->create_time : '';
                },

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
                'label' => '申请提交时间',
                'format'=> 'raw',
                'value' => function($model){
                    return $model->create_time;
                }
            ],
            [
                'label' => 'SKU审核状态',
                'format'=> 'raw',
                'value' => function($model){
                    switch($model->status){
                        case 1:
                            return SupplierServices::getApplyStatus($model->status);
                        case 2:
                            return SupplierServices::getApplyStatus($model->status).'<br/>'.$model->update_user_name.'<br/>'.$model->update_time;
                        case 3:
                            return SupplierServices::getApplyStatus($model->status).'<br/>'.$model->update_user_name.'<br/>'.$model->update_time.'<br/>'.$model->refuse_reason;
                    }
                    //return !empty($model->productDetail) ? SupplierGoodsServices::getProductStatus($model->productDetail->product_status) : '';
                }
            ],
            [
                'label' => '申请类型',
                'value' =>function($model){
                    return SupplierServices::getApplyTypeText($model->type);
                }
            ],
            [
                'label'=>'产品线',
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
/*                     return Html::img(Yii::$app->params['ERP_URL'].'/services/api/system/index/method/getimage/sku/' . $model->sku, [
                        'width' => '100px',
                        'height' => '100px',
                    ]); */
                    //return !empty($model->productDetail) ? \toriphes\lazyload\LazyLoad::widget(['src'=>Vhelper::getSkuImage($model->sku)]) : '';
                    return !empty($model->productDetail) ? \toriphes\lazyload\LazyLoad::widget(['src'=>Yii::$app->params['ERP_URL'].'/services/api/system/index/method/getimage/sku/' . $model->sku]) : '';

                }
            ],
            [
                'label' => 'SKU',
                'format'=> 'raw',
                'value' => function($model){
                   //$product = Vhelper::toSkuImgUrl($model->sku,$model->productDetail->uploadimgs);

                    return Html::a($model->sku, Yii::$app->params['SKU_ERP_Product_Detail'].$model->sku,['target'=>'blank']);
                    //return $model->sku;
                }
            ],
            [
                'label' => '商品名称',
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
                'label' => '原税点',
                'format'=> 'raw',
                'value' => function($model){
                    return !empty($model->oldQuotes) ? $model->oldQuotes->pur_ticketed_point : '';
                }
            ],
            [
                'label' => '原供应商',
                'format'=> 'raw',
                'value' => function($model){
                    $supplier = !empty($model->oldSupplier) ? $model->oldSupplier->supplier_name : '';
                    $num = !empty($model->old_supplier_code) ? SupplierUpdateApply::getProduct($model->old_supplier_code) : 0;
                    //$data = $supplier.'<br/>'.'供应商绑定sku数量：'.$model->old_product_num;
                    $data = $supplier.'<br/>'.'供应商绑定sku数量：'.$num;
                    return !empty($model->oldSupplier) ? $data : '';
                }
            ],
            [
                'label' => '现单价',
                'format'=> 'raw',
                'value' => function($model){
                    $oldprice = !empty($model->oldQuotes) ? $model->oldQuotes->supplierprice : 0;
                    $newprice = !empty($model->newQuotes) ? $model->newQuotes->supplierprice : 0;
                    //return !empty($model->newQuotes) ? $model->newQuotes->supplierprice : '';
//                    if($oldprice-$newprice>10||$oldprice-$newprice<-10){
//                        return "<span style='color:red;font-weight: bold;font-size: large'>$newprice</span>";
//                    }
                    if($oldprice<$newprice){
                        return "<span style='color: red;font-weight: bold;font-size: large'>$newprice</span>";
                    }elseif($oldprice>$newprice){
                        return "<span style='color: green;font-weight: bold;font-size: large'>$newprice</span>";
                    }else{
                        return $newprice;
                    }
                }
            ],
            [
                'label' => '现税点',
                'format'=> 'raw',
                'value' => function($model){
                    return !empty($model->newQuotes) ? $model->newQuotes->pur_ticketed_point : '';
                }
            ],
            [
                'label' => '现供应商',
                'format'=> 'raw',
                'value' => function($model){
                    $supplier = !empty($model->newSupplier) ? $model->newSupplier->supplier_name : '';
                    $num = !empty($model->old_supplier_code) ? SupplierUpdateApply::getProduct($model->new_supplier_code) : 0;
                    //$data = $supplier.'<br/>'.'供应商绑定sku数量：'.$model->new_product_num;
                    $data = $supplier.'<br/>'.'供应商绑定sku数量：'.$num;
                    return !empty($model->newSupplier) ? $data : '';
                }
            ],
            [
                'label' => '申请人员',
                'format'=> 'raw',
                'value' => function($model){
                    return $model->create_user_name;
                }
            ],
            [
                'label'=>'链接',
                'format' => 'raw',
                'value'  => function($model){
                    $str=!empty($model->newQuotes) ? $model->newQuotes->supplier_product_address : '';
                    $newLink=stristr($str,'http') ? $str:'https://'.$str;
                    $newLinkhtml =  "<a href='$newLink' title='$newLink' target='_blank'><i class='fa fa-fw fa-internet-explorer'></i></a>";
                    $oldstr=!empty($model->oldQuotes) ? $model->oldQuotes->supplier_product_address : '';
                    $oldLink     =  stristr($oldstr,'http') ? $oldstr :'https://'.$oldstr;
                    $oldLinkhtml =  "<a href='$oldLink' title='$oldLink' target='_blank'><i class='fa fa-fw fa-internet-explorer'></i></a>";
                    return '新链接：'.$newLinkhtml.'<br>原链接：'.$oldLinkhtml;
                }
            ],
            [
                'label' => '是否拿样',
                'format'=> 'raw',
                'value' => function($model){
                    $html= Html::a(Yii::t('app', '拿样'), '#', [
                        'applyId'=>$model->id,
                        'type'  => 'sample',
                        'class' => 'btn btn-success btn-sm sample',
                        'style'=> 'margin-top:5px'
                    ]);
                    $html.= Html::a(Yii::t('app', '不拿样'), '#', [
                        'applyId'=>$model->id,
                        'type'  => 'sampleno',
                        'class' => 'btn btn-danger btn-sm sample',
                        'style'=> 'margin-top:5px'
                    ]);
                    return $model->is_sample == 1&&$model->create_user_id == Yii::$app->user->id && $model->status ==1 ? SupplierServices::getSampleStatus($model->is_sample).'<br/>'.$html : SupplierServices::getSampleStatus($model->is_sample);

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
                        if(!empty($model->qualityResult->qc_result)){
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
                'label' => '备注',
                'format'=> 'raw',
                'value' => function($model){
                    return empty($model->remark)?'':$model->remark;
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

$requestUrl = Url::toRoute('refuse');
$sampleUrl = Url::toRoute('sample');
$js = <<<JS
    $(document).on('click', '#checkno', function () {
       var ids = new Array();
       $('[name="id[]"]').each(function(){
            if($(this).is(':checked')){
                ids.push($(this).val());
            }
       });
        $.get('{$requestUrl}', {id:ids.join(',')},
        function (data) {
            $('.modal-body').html(data);
        }
        );
    });
    $(document).on('click','#check',function(){
        var data = new Array();
       $('[name="id[]"]').each(function(){
            if($(this).is(':checked')){
                data.push($(this).val());
            }
       });
       if(data.length == 0){
            alert('请至少选择一个！');
       }else{
            $.ajax({
                url:'check',
                data:{data:data},
                type: 'post',
                dataType:'json',
            });
       }
    });

    $(document).on('click', '.sample', function () {
            var  applyId = $(this).attr('applyId');
            var  type    = $(this).attr('type');
            $.get('{$sampleUrl}', {id:applyId,type:type});
         });

    $(document).on('click', '.batchsample', function () {
            var str = '';
            $('input[name="id[]"]').each(function(){
                if($(this).is(':checked')){
                    str += ','+$(this).val();
                }
            });
            str  = str.substr(1);
            if(str == ''){
                alert('请选择一个！');
                return false;
            }
            var  type    = $(this).attr('type');
            $.get('{$sampleUrl}', {id:str,type:type});
         });

JS;
$this->registerJs($js);
?>
