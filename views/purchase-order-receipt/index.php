<?php

use yii\helpers\Html;
use yii\helpers\Url;
use kartik\grid\GridView;
use app\services\SupplierServices;
use yii\bootstrap\Modal;
use app\services\PurchaseOrderServices;
use app\services\BaseServices;
/* @var $this yii\web\View */
/* @var $searchModel app\models\PurchaseOrderPaySearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = '采购收款管理';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="purchase-order-pay-index">


    <?php echo $this->render('_search', ['model' => $searchModel]); ?>
    <p class="clearfix"></p>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'options'=>[
            'id'=>'grid_purchase_order',
        ],
        'pager'=>[
            //'options'=>['class'=>'hidden']//关闭分页
            'firstPageLabel'=>"首页",
            'prevPageLabel'=>'上一页',
            'nextPageLabel'=>'下一页',
            'lastPageLabel'=>'末页',
        ],
        'columns' => [
            [
                'class' => 'yii\grid\CheckboxColumn',
                'name'=>"id" ,

            ],
            [
                'label'=>'id',
                'attribute' => 'ids',
                'value'=>
                    function($model){
                        return  $model->id;   //主要通过此种方式实现
                    },

            ],
            [
                'label'=>'单号',
                'attribute' => 'pur_numbers',
                "format" => "raw",
                'value'=>
                    function($model, $key, $index, $column){

                        $data =Yii::t('app','采购单').':'.$model->pur_number."<br/>";
                        $data.=Yii::t('app','申请单').':'.$model->requisition_number."<br/>";

                        return $data;
                    },

            ],
//            [
//                'label'=>'结算方式',
//                'attribute' => 'ids',
//                'value'=>
//                    function($model){
//                        return  SupplierServices::getSettlementMethod($model->settlement_method);   //主要通过此种方式实现
//                    },
//
//            ],
            [
                'label'=>'供应商',
                'attribute' => 'ids',
                "format" => "raw",
                'value'=>
                    function($model){
                        return $model->supplier['supplier_name'];
                    },

            ],
            [
                'label'=>'状态',
                'attribute' => 'ids',
                "format" => "raw",
                'value'=>
                    function($model){
                        //return '<span class="label label-success">'.PurchaseOrderServices::getReceiptStatus($model->pay_status).'</span>';
                        /*return Html::a('<span class="label label-success">'.PurchaseOrderServices::getReceiptStatus($model->pay_status).'</span>', ['/purchase-order/refund-handler', 'pur_number' => $model->pur_number],
                            ['data-toggle' => 'modal', 'data-target' => '#create-modal']);*/
                        if(in_array($model->pay_status, [10]) && preg_match('/^PO/',strtoupper($model->pur_number))) {
                            return Html::a('<span class="label label-success">'.PurchaseOrderServices::getReceiptStatus($model->pay_status).'</span>',
                                ['/purchase-order/refund-handler', 'pur_number' => $model->pur_number,'requisition_number'=>$model->requisition_number], ['class' => 'refund-handler','data-toggle' => 'modal', 'data-target' => '#create-modal']);
                        } else {
                            return '<span class="label label-success">'.PurchaseOrderServices::getReceiptStatus($model->pay_status).'</span>';
                        }
                    },

            ],
            [
                'label'=>'名称',
                'attribute' => 'ids',
                "format" => "raw",
                'value'=>
                    function($model){
                        return $model->pay_name;
                    },

            ],
            [
                'label'=>'金额',
                'attribute' => 'ids',
                "format" => "raw",
                'value'=>
                    function($model){
                        $data = $model->pay_price.'<br/>';
                        $data.= '('.$model->currency.')';
                        return $data;
                    },

            ],
            [
                'label'=>'备注',
                'attribute' => 'ids',
                "format" => "raw",
                'value'=>
                    function($model){
                        return $model->review_notice;
                    },

            ],
            [
                'label'=>'操作人',
                'attribute' => 'ids',
                "format" => "raw",
                'value'=>
                    function($model){
                        $data  = Yii::t('app','申请人:').BaseServices::getEveryOne($model->applicant).'<br/>';
                       // $data .= Yii::t('app','审核人:').BaseServices::getEveryOne($model->auditor).'<br/>';
                        $data .= !empty($model->payer)?Yii::t('app','收款人:').BaseServices::getEveryOne($model->payer):Yii::t('app','收款人:').$model->payer;
                        return $data;
                    },

            ],
            [
                'label'=>'操作时间',
                'attribute' => 'ids',
                "format" => "raw",
                'value'=>
                    function($model){
                        $data  = Yii::t('app','申请时间:').$model->application_time.'<br/>';
                        $data .= Yii::t('app','收款时间:').$model->payer_time.'<br/>';
                       // $data .= Yii::t('app','审批时间:').$model->processing_time;
                        return $data;
                    },

            ],
            [
                'class' => 'kartik\grid\ActionColumn',
                'dropdown' => false,
                'width'=>'180px',
                'template' => '{complete}{payments}{edit}',
                'buttons'=>[
                    'complete' => function ($url, $model, $key) {
                        return Html::a('<i class="glyphicon glyphicon-eye-open"></i> 采购明细', ['purchase-order/view','id'=>$model->pur_number], [
                            'title' => Yii::t('app', '采购明细'),
                            'class' => 'btn btn-xs red',
                            'data-toggle' => 'modal',
                            'data-target' => '#create-modal',
                            'id'=>'views',
                        ]);
                    },
                    'payment' => function ($url, $model, $key) {
                        return Html::a('<i class="glyphicon glyphicon-list-alt"></i> 日志', ['view','id'=>$key], [
                            'title' => Yii::t('app', '日志'),
                            'class' => 'btn btn-xs red',
                            'data-toggle' => 'modal',
                            'data-target' => '#create-modal',
                            'id'=>'logs',
                        ]);
                    },
                    // 	已审批 3  等待到货 8  部分到货等待剩余 7
                    'edit' => function ($url, $model, $key) {
                        if(in_array($model->pay_status, [10]) && preg_match('/^PO/',strtoupper($model->pur_number))) {
                            return Html::a('<i class="glyphicon glyphicon-ok"></i> 编辑', ['/purchase-order/edit', 'pur_number' => $model->pur_number], [
                                'title' => Yii::t('app', '编辑'),
                                'class' => 'btn btn-xs edit',
                                'data-toggle' => 'modal',
                                'data-target' => '#create-modal',
                            ]);
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
    'id' => 'create-modal',
    'header' => '<h4 class="modal-title">系统信息</h4>',
    'footer' => '<a href="#" class="btn btn-primary closes" data-dismiss="modal">关闭</a>',
    'size'=>'modal-lg',
    'options'=>[
        'data-backdrop'=>'static',//点击空白处不关闭弹窗

    ],
]);
Modal::end();

$js = <<<JS


    $(document).on('click', '#views', function () {

        $.get($(this).attr('href'), {id:$(this).attr('value')},
            function (data) {
                $('.modal-body').html(data);
            }
        );
    });
     $(document).on('click', '#logs', function () {

        $.get($(this).attr('href'), {id:$(this).attr('value')},
            function (data) {
                $('.modal-body').html(data);
            }
        );
    });

    $(document).on('click', '.refund-handler', function() {
         $('.modal-body').html('');
         $('.modal-body').load($(this).attr('href'));
     });

    // 编辑采购单
    $(document).on('click', '.edit', function () {
        $('.modal-body').html('');
        $.get($(this).attr('href'), {},
            function (data) {
                $('.modal-body').html(data);
            }
        );
    });
JS;
$this->registerJs($js);
?>
