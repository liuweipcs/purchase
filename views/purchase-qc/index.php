<?php

use yii\helpers\Html;
use kartik\grid\GridView;
use yii\bootstrap\Modal;
use app\services\PurchaseOrderServices;

/* @var $this yii\web\View */
/* @var $searchModel app\models\PurchaseQcSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = '品检异常处理';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="purchase-qc-index">

    <?php  echo $this->render('_search', ['model' => $searchModel]); ?>
    <p class="clearfix"></p>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        //'filterModel' => $searchModel,
        'pager'=>[
            //'options'=>['class'=>'hidden']//关闭分页
            'firstPageLabel'=>"首页",
            'prevPageLabel'=>'上一页',
            'nextPageLabel'=>'下一页',
            'lastPageLabel'=>'末页',
        ],
        'columns' => [
            'id',
            [
                'label'=>'单号',
                'attribute'=>'express_no',
                'format'=>'raw',
                'value'=>function($data){
                    $str='';
                    $str.="<span class='label label-info'>".Yii::$app->params['qc_status'][$data->qc_status]."</span></br>";
                    /*if($data->handle_type){
                        $str.="<span class='label label-danger'>".Yii::$app->params['handle_type_qc'][$data->handle_type]."</span></br>";
                    }*/
                    $str.="采购单:{$data->pur_number}<br/>";
                    $str.="快递单:{$data->express_no}<br/>";
                    return $str;
                }
            ],
            'buyer',
            'creator',
            [
                'label'=>'供应商',
                'attribute'=>'supplier_code',
                'format'=>'raw',
                'value'=>function($data){
                    $str=!empty($data->supplier_name)?$data->supplier_name:\app\services\BaseServices::getSupplierName($data->supplier_code);
                    return $str;
                }
            ],
            [
                'label'=>'品检类型',
                'attribute'=>'check_type',
                'format'=>'raw',
                'value'=>function($data){
                    $str=$data->check_type>0?Yii::$app->params['check_type'][$data->check_type]:$data->check_type;
                    return $str;
                }
            ],
            'total_qty',
            'total_delivery_qty',
            'total_presented_qty',
            'total_check_qty',
            'total_good_products_qty',
            'total_bad_products_qty',

            'created_at',
            [
                'label'=>'推送状态',
                'attribute' => 'created_ats',
                "format" => "raw",
                'value'=>
                    function($model, $key, $index, $column){
                        return PurchaseOrderServices::getPush($model->is_push);
                    },
            ],
            [
                'header' => '操作',
                'class' => 'yii\grid\ActionColumn',
                'template' => '{view} {update}',
                'buttons' => [
                    'view' => function ($url, $model) {
                        $url = "/purchase-qc/view-detail?express_no={$model->express_no}&pur_number={$model->pur_number}&handle_type={$model->handle_type}" ;
                        return Html::a("<span class='label label-success'>明细</span>", $url, ['title' => '查看', 'class' => 'view-detail','data-toggle'=>'modal','data-target'=>'#view-detail-modal']);
                    },
                    'update' => function ($url, $model) {
                        $datas= [1,5];
                        if(in_array($model->qc_status,$datas)){
                            $url = "/purchase-qc/handle-detail?express_no={$model->express_no}&pur_number={$model->pur_number}&handle_type={$model->handle_type}" ;
                            return Html::a("<span class='label label-warning'>处理异常</span>", $url, ['title' => '编辑', 'class'=>'handle-detail','data-toggle'=>'modal','data-target'=>'#view-detail-modal']);
                        }
                    },
                ],
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

Modal::begin([
    'id' => 'view-detail-modal',
    'header' => '<h4 class="modal-title">QC异常明细</h4>',
    //'footer' => '<a href="#" class="btn btn-primary" data-dismiss="modal">关闭</a>',
    'size'=>'modal-lg',
]);
Modal::end();

$js = <<<JS
$(function(){
    //异常明细
    $("a.view-detail").click(function(){
        var url=$(this).attr("href");
        $.get(url, {},
            function (tmp) {
                $('#view-detail-modal').find('.modal-body').html(tmp);
            }
        );
   });
    //处理异常
    $("a.handle-detail").click(function(){
        var url=$(this).attr("href");
        $.get(url, {},
            function (tmp) {
                $('#view-detail-modal').find('.modal-body').html(tmp);
            }
        );
   });
});
JS;
$this->registerJs($js);
?>