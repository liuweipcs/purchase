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
use app\models\SupplierQuotes;
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
    if(Helper::checkRoute('create'))
    {
        echo Html::a('创建需求', ['create'], ['class' => 'btn btn-info'/*,'id'=>'create' ,'data-toggle' => 'modal', 'data-target' => '#create-modal'*/]);
    }
    ?>
    <?php
    if(Helper::checkRoute('revoke-demand'))
    {
        echo Html::a('撤销需求', ['revoke-demand'], ['class' => 'btn btn-info', 'id' => 'submit-audit' /*,'data-toggle' => 'modal', 'data-target' => '#create-modal'*/]);
    }
    ?>
    <?php
    if(Helper::checkRoute('create-purchase-order'))
    {
        //echo Html::a('创建采购计划单', ['create-purchase-order'], ['class' => 'btn btn-info create-purchase pp',/*'data-toggle' => 'modal', 'data-target' => '#create-modal'*/]);
        echo Html::a('创建采购计划单', ['purchase-order-confirm/addproduct'], ['class' => 'btn btn-info create-purchase pp',/*'data-toggle' => 'modal', 'data-target' => '#create-modal'*/]);
    }
    ?>

    <?php
    if(Helper::checkRoute('agree'))
    {
        echo Html::a('批量同意', ['agree'], ['class' => 'btn btn-primary over-bulk-consent']);
    }
    ?>

    <?php
    if(Helper::checkRoute('disagree'))
    {
        echo Html::a('批量驳回', ['disagree'], ['class' => 'btn btn-warning over-dismiss-batches', 'data-toggle' => 'modal', 'data-target' => '#create-modal']);
    }
    ?>

    <?php
    if(Helper::checkRoute('purchase-sum-import'))
    {
       echo  Html::a('采购需求导入', ['purchase-sum-import'], ['class' => 'btn btn-success over-purchase-sum-import', 'data-toggle' => 'modal', 'data-target' => '#create-modal']);
    }
    ?>

    <h4><p class="glyphicon glyphicon-heart pps" style="color: red" aria-hidden="true">温馨提示:</p><span style="color: red"><p class="pps">1.撤销的需求不可再还原,请看清楚了再撤销。</p><p class="pps">2.请选择相同的供应商生成采购单</p><p class="pps">3.默认展示未生成的,查看已生成请用上面的搜索</p></h4>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        //'filterModel' => $searchModel,
        'options'=>[
            'id'=>'grid_overseas_purchase',
        ],
        'pager'=>[
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
            'id',
            /*[
                'label'=>'产品图片',
                'attribute'=>'uploadimgs',
                'format'=>'raw',
                'value' => function ($model) {
                    return Vhelper::toSkuImg($model->sku,$model->uploadimgs);
                }
            ],*/

            [
                'label'=>'产品信息',
                'attribute' => 'product_name',
                "format" => "raw",
                'value'=>
                    function($model){
                        $data = '产品名：'.$model->product_name.'<br/>';
                        $data.= $model->product_category?'产品分类：'.BaseServices::getCategory($model->product_category).'<br/>':'';
                        $data.= '<span style="color:red">sku:'.$model->sku.'</span><br/>';
                        $data.= '<span style="color:#00a65a">需求单号:'.$model->demand_number.'</span><br/>';
                        $suppliercode =!empty($model->supplierQuotes['quotes_id'])?SupplierQuotes::getFileds($model->supplierQuotes['quotes_id'],'suppliercode')->suppliercode:'';
                        $data.= !empty($suppliercode)?'<span style="color:#00a65a">供应商:'.BaseServices::getSupplierName($suppliercode).'</span></br>':'<span style="color:#00a65a">供应商:</span></br>';
                        $data.= $model->is_purchase==1?'是否生成采购计划：<span style="color:red">未生成</span>':'是否生成采购计划：<span style="color:#00a65a">已生成</span>';
                        return $data;
                    },

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
                'attribute' => 'purchase_quantity',
                "format" => "raw",
                'value'=>
                    function($model){

                        $data ='采购数：'.'<span style="color:red">'.$model->purchase_quantity.'</span><br/>';
                        return $data;
                    },

            ],
            [
                'attribute' => 'purchase_warehouse',
                "format" => "raw",
                'value'=>
                    function($model){
                        return  BaseServices::getWarehouseCode($model->purchase_warehouse);
                    },

            ],
            [
                'label'=>'需求信息',
                'attribute' => 'create_id',
                "format" => "raw",
                'value'=>
                    function($model){
                        $data = '需求人:'.$model->create_id.'<br/>';
                        $data .='需求时间:'.$model->create_time;
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
            /*[
                'label'=>'推送状态',
                'attribute' => 'is_push',
                "format" => "raw",
                'value'=>
                    function($model, $key, $index, $column){
                        return PurchaseOrderServices::getPush($model->is_push);
                    },
            ],*/
            [
                'class' => 'kartik\grid\ActionColumn',
                'dropdown' => false,
                'width'=>'180px',
                'template' => Helper::filterActionColumn('{update}{agree}{disagree}{purchase-disagree}{delete}'),
                'buttons'=>[
                    'update' => function ($url, $model, $key)
                    {
                        $arr= ['1','4','5','3'];
                        if(!in_array($model->level_audit_status,$arr)) {
                            return Html::a('<i class="fa fa-fw fa-check"></i>修改', ['update', 'id' => $key], [
                                'title' => Yii::t('app', '修改'),
                                'class' => 'btn btn-xs red'
                            ]);
                        }
                    },
                    'agree' => function ($url, $model, $key)
                    {
                        $arr= ['1','2','4','5','3'];
                        if(!in_array($model->level_audit_status,$arr)) {
                            $page=Yii::$app->request->get('page') ? Yii::$app->request->get('page') : 1;
                            return Html::a('<i class="fa fa-fw fa-check"></i>同意', ['agree', 'id' => $key,'page'=>$page], [
                                'title' => Yii::t('app', '同意'),
                                'class' => 'btn btn-xs red'
                            ]);
                        }
                    },
                    'disagree' => function ($url, $model, $key)
                    {
                        $arr= ['1','2','4','5','3'];
                        if(!in_array($model->level_audit_status,$arr)) {
                            $page=Yii::$app->request->get('page') ? Yii::$app->request->get('page') : 1;
                            return Html::a('<i class="fa fa-fw fa-close"></i>驳回', ['disagree', 'id' => $key,'page'=>$page], [
                                'title'       => Yii::t('app', '驳回'),
                                'class'       => 'btn btn-xs disagree',
                                'data-toggle' => 'modal',
                                'data-target' => '#create-modal',
                            ]);
                        }
                    },
                    'purchase-disagree' => function ($url, $model, $key)
                    {
                        $arr= ['1'];
                        if(in_array($model->level_audit_status,$arr)) {
                            $page=Yii::$app->request->get('page') ? Yii::$app->request->get('page') : 1;
                            return Html::a('<i class="fa fa-fw fa-close"></i>采购驳回', ['purchase-disagree', 'id' => $key,'page'=>$page], [
                                'title'       => Yii::t('app', '采购驳回'),
                                'class'       => 'btn btn-xs pdisagree',
                                'data-toggle' => 'modal',
                                'data-target' => '#create-modal',
                            ]);
                        }
                    },
                    'delete' => function ($url, $model, $key)
                    {
                        $arr= ['3'];
                        if(in_array($model->level_audit_status,$arr)) {
                            $page=Yii::$app->request->get('page') ? Yii::$app->request->get('page') : 1;
                            return Html::a('<i class="fa fa-fw fa-close"></i>删除', ['delete', 'id' => $key,'page'=>$page], [
                                'title'       => Yii::t('app', '删除'),
                                'class'       => 'btn btn-xs pdisagree',

                            ]);
                        }
                    },
                ]

            ],
        ],
        'containerOptions' => ["style"=>"overflow:auto"], // only set when $responsive = false
        'toolbar' =>  [
            '{export}',
        ],


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
$page=Yii::$app->request->get('page') ? Yii::$app->request->get('page') : 1;
$requestUrl = Url::toRoute('view');
$arrival='请选择需要标记到货日期的采购单';
$js = <<<JS
    $(function(){
            $("a#submit-audit").click(function(){
                var ids = $('#grid_overseas_purchase').yiiGridView('getSelectedRows');
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
            //批量同意
        $(".over-bulk-consent").click(function(){
            var ids = $('#grid_overseas_purchase').yiiGridView('getSelectedRows');
            if(ids==''){
                alert('请先选择数据!');
                return false;
            }else{
                var url=$(this).attr("href");
                $.post(url, {ids:ids,page:"{$page}"},
                    function (data) {
                        $('.modal-body').html(data);
                    }
                );
               return false; 
            }
        });
        
        //批量驳回
        $(".over-dismiss-batches").click(function(){
            var ids = $('#grid_overseas_purchase').yiiGridView('getSelectedRows');
            if(ids==''){
                alert('请先选择数据!');
                return false;
            }else{
                var url=$(this).attr("href");
                $.post(url, {id:ids,page:"{$page}"},
                    function (data) {
                        $('.modal-body').html(data);
                    }
                );
            }
        });
        
        
        //采购需求批量导入
        $(".over-purchase-sum-import").click(function(){
            $.get($(this).attr('href'), {},
            function (data) {
                $('.modal-body').html(data);
            });
        });
    

    /*$(function(){
        //点击生成采购单
        $("a.create-purchase").click(function(){
            var ids = $('#grid_overseas_purchase').yiiGridView('getSelectedRows');
            if(ids==''){
                alert('请先选择需要生成的数据!');
                return false;
            }else{
                var url=$(this).attr("href");
                if($(this).hasClass("pp"))
                    {
                        url = '/platform-summary/create-purchase-order';
                    }
                url=url+'?ids='+ids;
                $(this).attr('href',url);
                $.get(url, {},
                    function (data) {
                        $('.modal-body').html(data);
                    }
                );
            }
        });
    });*/

JS;
$this->registerJs($js);
?>
