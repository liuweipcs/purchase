<?php

use yii\helpers\Html;
use kartik\grid\GridView;
use yii\bootstrap\Modal;
use app\services\PurchaseOrderServices;
/* @var $this yii\web\View */
/* @var $searchModel app\models\PurchaseReceiveSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = '数量异常处理';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="purchase-receive-index">

    <?php echo $this->render('_search', ['model' => $searchModel,'view'=>$view]); ?>
    <p class="clearfix"></p>
    <h4><span class="glyphicon glyphicon-heart" style="color: red" aria-hidden="true"></span>温馨小提示:<span style="color: red">异常不知道处理请教刘伶俐<i class="fa fa-fw fa-smile-o"></i></h4>
    <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'pager'=>[
                //'options'=>['class'=>'hidden']//关闭分页
                'firstPageLabel'=>"首页",
                'prevPageLabel'=>'上一页',
                'nextPageLabel'=>'下一页',
                'lastPageLabel'=>'末页',
            ],
            'columns' => [
                ['class' => 'yii\grid\SerialColumn'],
                [
                    'label'=>'单号',
                    'attribute'=>'pur_number',
                    'format'=>'raw',
                    'value'=>function($data){
                        $str='';
                        if($data->receive_type=='2'){
                            $str.="<span class='label label-danger'>".Yii::$app->params['receive_type'][$data->receive_type]."</span>";
                        }else{
                            $str.="<span class='label label-info'>".Yii::$app->params['receive_type'][$data->receive_type]."</span>";
                        }
                        $str.=" <span class='label label-primary'>".Yii::$app->params['receive_status'][$data->receive_status]."</span><br/>";
                        $str.="采购单:{$data->pur_number}<br/>";
                        $str.="sku:{$data->sku}".\app\services\SupplierGoodsServices::getSkuStatus($data->sku)."<br/>";
                        return $str;
                    }
                ],
                [
                    'label'=>'供应商',
                    'attribute'=>'supplier_code',
                    'format'=>'raw',
                    'value'=>function($data){
                        $str='';
                        $str.="{$data->supplier_name}";
                        return $str;
                    }
                ],
                'buyer',
                'creator',
                [
                    'label'=>'订单数量',
                    'attribute'=>'total_qty',
                    'format'=>'raw',
                    'value'=>function($data){
                        return $data->total_qty;
                    }
                ],
                [
                    'label'=>'到货数量',
                    'attribute'=>'total_delivery_qty',
                    'format'=>'raw',
                    'value'=>function($data){
                        return $data->total_delivery_qty;
                    }
                ],
                [
                    'label'=>'赠送数量',
                    'attribute'=>'total_presented_qty',
                    'format'=>'raw',
                    'value'=>function($data){
                        return $data->total_presented_qty;
                    }
                ],
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
                            $url = "/purchase-receive/view-detail?express_no={$model->express_no}&pur_number={$model->pur_number}&handle_type={$model->handle_type}&sku={$model->sku}" ;
                            return Html::a("<span class='label label-success'>明细</span>", $url, ['title' => '查看', 'class' => 'view-detail','data-toggle'=>'modal','data-target'=>'#view-detail-modal']);
                        },
                        'update' => function ($url, $model,$index) {
                            if($model->receive_status==1&&$model->handle_type==0){
                                $url = "/purchase-receive/handle-detail?express_no={$model->express_no}&pur_number={$model->pur_number}&handle_type={$model->handle_type}&sku={$model->sku}" ;
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