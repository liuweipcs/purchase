<?php

use yii\helpers\Html;
use kartik\grid\GridView;
use yii\bootstrap\Modal;
/* @var $this yii\web\View */
/* @var $searchModel app\models\PurchaseReceiveSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = '收货异常审核';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="purchase-receive-index">

    <?php  echo $this->render('_search', ['model' => $searchModel]); ?>
    <p class="clearfix"></p>
    <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'columns' => [
                ['class' => 'yii\grid\SerialColumn'],
                [
                    'label'=>'单号',
                    'attribute'=>'pur_number',
                    'format'=>'raw',
                    'value'=>function($data){
                        $str='';
                        if($data->receive_type=='less'){
                            $str.="<span class='label label-danger'>".Yii::$app->params['receive_type'][$data->receive_type]."</span>";
                        }else{
                            $str.="<span class='label label-info'>".Yii::$app->params['receive_type'][$data->receive_type]."</span>";
                        }
                        $str.=" <span class='label label-primary'>".Yii::$app->params['handle_type'][$data->handle_type]."</span><br/>";
                        if($data->is_return == 1){
                            $str .= " 退款金额:{$data->total_refund_amount}";
                        }
                        $str.="<br/>采购单:{$data->pur_number}<br/>";
                        return $str;
                    }
                ],
                [
                    'label'=>'供应商',
                    'attribute'=>'supplier_code',
                    'format'=>'raw',
                    'value'=>function($data){
                        $str='';
                        $str.="{$data->supplier_name}[{$data->supplier_code}]";
                        return $str;
                    }
                ],
                'buyer',
                [
                    'label'=>'总预期数量',
                    'attribute'=>'total_qty',
                    'format'=>'raw',
                    'value'=>function($data){
                        return $data->total_qty;
                    }
                ],
                [
                    'label'=>'总收货数量',
                    'attribute'=>'total_delivery_qty',
                    'format'=>'raw',
                    'value'=>function($data){
                        return $data->total_delivery_qty;
                    }
                ],
                [
                    'label'=>'总赠送',
                    'attribute'=>'total_presented_qty',
                    'format'=>'raw',
                    'value'=>function($data){
                        return $data->total_presented_qty;
                    }
                ],
                 'created_at',
                [
                    'header' => '操作',
                    'class' => 'yii\grid\ActionColumn',
                    'template' => '{view} {update}{receipt}',
                    'buttons' => [
                        'view' => function ($url, $model) {
                            $url = "/purchase-receive-audit/view-detail?pur_number={$model->pur_number}&handle_type={$model->handle_type}" ;
                            return Html::a("<span class='label label-success'>明细</span>", $url, ['title' => '查看', 'class' => 'view-detail','data-toggle'=>'modal','data-target'=>'#view-detail-modal']);
                        },
                        'update' => function ($url, $model) {
                            if($model->receive_status=='2'){
                                $url = "/purchase-receive-audit/audit-detail?pur_number={$model->pur_number}&handle_type={$model->handle_type}" ;
                                return Html::a("<span class='label label-warning'>审核</span>", $url, ['title' => '编辑', 'class'=>'audit-detail','data-toggle'=>'modal','data-target'=>'#view-detail-modal']);
                            }
                        },
                        'receipt' => function ($url, $model) {
                            if($model->receive_status=='3'&&$model->handle_type=='stop'){
                                $url = "/purchase-receive-audit/receipt?pur_number={$model->pur_number}&handle_type={$model->handle_type}" ;
                                return Html::a("<span class='label label-warning'>申请收款</span>", $url, ['title' => '收款', 'class'=>'audit-detail','data-toggle'=>'modal','data-target'=>'#view-detail-modal']);
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
    'header' => '<h4 class="modal-title">收货异常明细</h4>',
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
    //审核异常
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