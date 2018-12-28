<?php

use yii\helpers\Html;
use kartik\grid\GridView;
use Yii\helpers\Url;
use yii\bootstrap\Modal;
use app\config\Vhelper;
use \app\services\SupplierServices;
/* @var $this yii\web\View */
/* @var $searchModel app\models\SupplierGoodsSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', '样品检验');
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="stockin-index">

    <?= $this->render('_search1', ['model' => $searchModel]); ?>
    <p class="clearfix"></p>
    <?= Html::a(Yii::t('app', '添加采购单号'), '#', [
        'id' => 'number_in',
        'data-toggle' => 'modal',
        'data-target' => '#create-modal',
        'class' => 'btn btn-success',
    ]);?>
    <?= Html::a(Yii::t('app', '更新采购单号'), '#', [
        'id' => 'number_update',
        'data-toggle' => 'modal',
        'data-target' => '#create-modal',
        'class' => 'btn btn-info',
    ]);?>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        //'filterModel' => $searchModel,
        "options" => ["class" => "grid-view","style"=>"overflow:auto", "id" => "grid"],
        'pager'=>[
            //'options'=>['class'=>'hidden']//关闭分页
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
                'label' =>'SKU',
                'format'=>'raw',
                'value' => function($model, $key, $index, $column){
                    return  $model->sku;
                },

            ],
            [
                'label' => '供应商整合信息',
                'format'=> 'raw',
                'value' => function($model){
                    $user = !empty($model->apply) ? $model->apply->create_user_name : '';
                    $time = !empty($model->apply) ? $model->apply->create_time : '';
                    $supplier = !empty($model->apply) ? !empty($model->apply->newSupplier) ? $model->apply->newSupplier->supplier_name : '' : '';
                    return '申请修改人:'.$user.'<br/>申请时间：'.$time.'<br/>新供应商:'.$supplier;
                }
            ],
            [
                'label' => '品名',
                'format'=> 'raw',
                'value' => function($model){
                    return !empty($model->product) ? !empty($model->product->desc) ? $model->product->desc->title : '' : '';
                }
            ],
            [
                'label' =>'采购单号',
                'format'=>'raw',
                'value'=>function($model){
                    return $model->pur_number;
                }

            ],
            [
                'label' => '数量',
                'format'=> 'raw',
                'value' => function($model){
                    return $model->product_num;
                }
            ],
//            [
//                'label' => "供应链人员发出",
//                'format'=> 'raw',
//                'value' => function($model){
//
//                    $html= Html::button(Yii::t('app', '否'), [
//                        'inspectId'=>$model->id,
//                        'type'  => 'supplySend',
//                        'class' => 'btn btn-warning btn-sm inspect',
//                        'style'=> 'margin-top:5px',
//                        'data-toggle' => 'modal',
//                        'data-target' => '#create-modal',
//                    ]);
//                    switch($model->supply_chain_send){
//                        case 2:
//                            return SupplierServices::getSendTakeStatus($model->supply_chain_send).'<br/>确认人:'.$model->supply_send_name.'<br/>确认时间:'.$model->supply_chain_send_time;
//                        default:
//                            return $html;
//                    }
//                }
//            ],
            [
                'label' => '品控人员收货',
                'format'=> 'raw',
                'value' => function($model){
                    $html= Html::button(Yii::t('app', '否'), [
                        'inspectId'=>$model->id,
                        'type'  => 'qualityTake',
                        'class' => 'btn btn-primary btn-sm inspect',
                        'style'=> 'margin-top:5px',
                        'data-toggle' => 'modal',
                        'data-target' => '#create-modal',
                    ]);
                    switch($model->quality_control_take){
                        case 2:
                            return SupplierServices::getSendTakeStatus($model->quality_control_take).'<br/>确认人:'.$model->quality_take_name.'<br/>确认时间:'.$model->quality_control_take_time;
                        default:
                            return $html;
                    }
                }
            ],

//            [
//                'label' => '供应链人员收货',
//                'format'=> 'raw',
//                'value' => function($model){
//                    $html= Html::button(Yii::t('app', '否'), [
//                        'inspectId'=>$model->id,
//                        'type'  => 'supplyTake',
//                        'class' => 'btn btn-warning btn-sm inspect',
//                        'style'=> 'margin-top:5px',
//                        'data-toggle' => 'modal',
//                        'data-target' => '#create-modal',
//                    ]);
//                    switch($model->supply_chain_take){
//                        case 2:
//                            return SupplierServices::getSendTakeStatus($model->supply_chain_take).'<br/>确认人:'.$model->supply_take_name.'<br/>确认时间:'.$model->supply_chain_take_time;
//                        default:
//                            return $html;
//                    }
//                }
//            ],
            [
                'label' => '质检结果',
                'format'=> 'raw',
                'value' => function($model){
                    $html= Html::a(Yii::t('app', '合格'), '#', [
                        'data-toggle' => 'modal',
                        'data-target' => '#create-modal',
                        'inspectId'=>$model->id,
                        'class' => 'btn btn-success btn-sm quality',
                        'style'=> 'margin-top:5px'
                    ]);
                    $html.= '&nbsp&nbsp&nbsp'.Html::a(Yii::t('app', '不合格'), '#', [
                        'data-toggle' => 'modal',
                        'data-target' => '#create-modal',
                        'inspectId'=>$model->id,
                        'class' => 'btn btn-danger btn-sm qualityno',
                        'style'=> 'margin-top:5px'
                    ]);
                    $status = !empty($model->qc_result) ? SupplierServices::getSampleResultStatus($model->qc_result) : '';
                    $reason = $model->reason;
                    $time   = $model->confirm_time;
                    switch($model->qc_result){
                        case 1:
                            return $status.'<br/>'.$html;
                        default:
                            return $status.'<br/>质检结果确认人:'.$model->confirm_user_name.'<br/>确认时间:'.$time.'<br/>质检备注：'.$reason;
                    }
                }
            ],
            [
                'label' => '品控人员入库',
                'format'=> 'raw',
                'value' => function($model){
                    $html= Html::button(Yii::t('app', '否'), [
                        'inspectId'=>$model->id,
                        'type'  => 'qualitySend',
                        'class' => 'btn btn-primary btn-sm inspect',
                        'style'=> 'margin-top:5px',
                        'data-toggle' => 'modal',
                        'data-target' => '#create-modal',
                    ]);
                    switch($model->quality_control_send){
                        case 2:
                            return SupplierServices::getSendTakeStatus($model->quality_control_send).'<br/>确认人:'.$model->quality_send_name.'<br/>确认时间:'.$model->quality_control_send_time;
                        default:
                            return $html;
                    }
                }
            ],
            [
                'label' => '备注',
                'format'=> 'raw',
                'value' => function($model){
                    return empty($model->apply->remark)?'':$model->apply->remark;
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
$inspectUrl = Url::toRoute('inspect');
$qualityUrl = Url::toRoute('quality');
$qualitynoUrl = Url::toRoute('qualityno');
$insertUrl = Url::toRoute('insert-number');
$updateUrl = Url::toRoute('update-number');
$js = <<<JS
    $(document).on('click', '.inspect', function () {
            var  inspectId = $(this).attr('inspectId');
            var  type    = $(this).attr('type');
            var  t       = $(this).closest('td');
            $.get('{$inspectUrl}', {id:inspectId,type:type},function(data){
                $('.modal-body').html(data.message);
                if(data.status == 'success'){
                    var str ='<span class="label label-success">是</span><br>确认人：'+data.user+'<br>确认时间:'+data.time;
                    t.html(str);
                }
            },'json');
         });

    $(document).on('click', '.quality', function () {
            var  inspectId = $(this).attr('inspectId');
            $.get('{$qualityUrl}', {id:inspectId},
                function (data) {
                    $('.modal-body').html(data);
                });
         });
    
    $(document).on('click', '#number_in', function () {
        var ids= new Array();
        $('[name="id[]"]').each(function() {
          if($(this).is(":checked")){
              ids.push($(this).val());
          }
        });
        if(ids.length<1){
            $('.modal-body').html('至少选择一个!');
        }else {
            $.get('{$insertUrl}', {ids:ids.join(',')},
                function (data) {
                    $('.modal-body').html(data);
                });
             }
        });
    
    $(document).on('click', '#number_update', function () {
        var ids= new Array();
        $('[name="id[]"]').each(function() {
          if($(this).is(":checked")){
              ids.push($(this).val());
          }
        });
        if(ids.length<1){
            $('.modal-body').html('至少选择一个!');
        }else {
            $.get('{$updateUrl}', {ids:ids.join(',')},
                function (data) {
                    $('.modal-body').html(data);
                });
             }
        });

    $(document).on('click', '.qualityno', function () {
           var inspectId = $(this).attr('inspectId');
                $.get('{$qualitynoUrl}', {id:inspectId},
                function (data) {
                    $('.modal-body').html(data);
                }
            );
        });
JS;
$this->registerJs($js);
?>
