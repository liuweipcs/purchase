<?php

use yii\helpers\Html;
use kartik\grid\GridView;
use yii\bootstrap\Modal;

/* @var $this yii\web\View */
/* @var $searchModel app\models\PurchaseQcSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'QC异常审核';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="purchase-qc-index">

    <?php  echo $this->render('_search', ['model' => $searchModel]); ?>
    <p class="clearfix"></p>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        //'filterModel' => $searchModel,
        'columns' => [
            'id',
            [
                'label'=>'单号',
                'attribute'=>'express_no',
                'format'=>'raw',
                'value'=>function($data){
                    $str='';
                    $str.="<span class='label label-info'>".Yii::$app->params['qc_status'][$data->qc_status]."</span> ";
                    $str.="<span class='label label-danger'>".Yii::$app->params['handle_type_qc'][$data->handle_type]."</span></br>";
                    if ($data->total_refund_amount > 0) {
                        $str .= " 退款金额:{$data->total_refund_amount}<br/>";
                    }
                    if ($data->handle_type == 3){
                        $str .= " 退货数量:{$data->total_bad_products_qty}<br/>";
                    } elseif($data->handle_type == 4){
                        $str .= " 换货数量:{$data->total_bad_products_qty}<br/>";
                    }
                    $str.="采购单:{$data->pur_number}<br/>";
                    $str.="收货快递单:{$data->express_no}<br/>";
                    return $str;
                }
            ],
            'buyer',
            [
                'label'=>'供应商',
                'attribute'=>'supplier_code',
                'format'=>'raw',
                'value'=>function($data){
                    $strs=!empty($data->supplier_name)?$data->supplier_name:\app\services\BaseServices::getSupplierName($data->supplier_code);
                    $str="{$strs}[{$data->supplier_code}]";
                    return $str;
                }
            ],
            [
                'label'=>'品检类型',
                'attribute'=>'check_type',
                'format'=>'raw',
                'value'=>function($data){
                    $str=Yii::$app->params['check_type'][$data->check_type];
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
                'header' => '操作',
                'class' => 'yii\grid\ActionColumn',
                'template' => '{view} {update}',
                'buttons' => [
                    'view' => function ($url, $model) {
                        $url = "/purchase-qc-audit/view-detail?express_no={$model->express_no}&pur_number={$model->pur_number}&handle_type={$model->handle_type}" ;
                        return Html::a("<span class='label label-success'>明细</span>", $url, ['title' => '查看', 'class' => 'view-detail','data-toggle'=>'modal','data-target'=>'#view-detail-modal']);
                    },
                    'update' => function ($url, $model) {
                        if($model->qc_status=='3'){
                            $url = "/purchase-qc-audit/audit-detail?express_no={$model->express_no}&pur_number={$model->pur_number}&handle_type={$model->handle_type}" ;
                            return Html::a("<span class='label label-warning'>审核</span>", $url, ['title' => '编辑', 'class'=>'audit-detail','data-toggle'=>'modal','data-target'=>'#view-detail-modal']);
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
            'heading'=>'<h3 class="panel-title"><i class="glyphicon glyphicon-globe"></i> Countries</h3>',
            'type'=>'success',
            'after'=>Html::a('<i class="glyphicon glyphicon-repeat"></i> 刷新', ['index'], ['class' => 'btn btn-info']),
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
    $("a.audit-detail").click(function(){
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