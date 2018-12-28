<?php

use yii\helpers\Html;
use kartik\grid\GridView;
use yii\bootstrap\Modal;
use yii\helpers\Url;
use app\services\PurchaseOrderServices;
use app\models\PurchaseOrderItems;
use app\services\BaseServices;

/* @var $this yii\web\View */
/* @var $searchModel app\models\PurchaseOrderSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = '采购单异常审核';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="purchase-order-index">

    <?php  echo $this->render('_search', ['model' => $searchModel]); ?>
    <p class="clearfix"></p>
    <p>

        <?= Html::a('批量审核', ['all-review','name'=>'audit'], ['class' => 'btn btn-success','id'=>'batch_review','data-toggle' => 'modal', 'data-target' => '#create-modal',]) ?>
    </p>

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
                'checkboxOptions' => function ($model, $key, $index, $column) {
                    return ['value' => $model->pur_number];
                }
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
                'label'=>'订单',
                'attribute' => 'pur_numbers',
                "format" => "raw",
                'value'=>
                    function($model, $key, $index, $column){
                        $data = PurchaseOrderServices::getPurchaseStatus($model->purchas_status).'&nbsp;&nbsp;';
                        $data.= $model->is_expedited==2 ? '<span class="label label-danger">加急采购单</span>&nbsp;&nbsp;':'';
                        $data .= !empty($model->refund_status)?'<span class="label label-success">'.PurchaseOrderServices::getReceiptStatus($model->refund_status).'</span><br/>':'';
                        $data.=Yii::t('app','采购单').':'.$model->pur_number."<br/>";
                        $data.=Yii::t('app','供应商').':'.$model->supplier_name."<br/>";
                        $data.=Yii::t('app','采购员').':'.$model->buyer."<br/>";
                        $data.=Html::a('<span class="glyphicon glyphicon-zoom-in"  style="font-size:20px;color:orange;margin-right:10px;" title="单击，查看采购产品明细"></span>', ['purchase-order/view'],['id' => 'views',
                            'data-toggle' => 'modal',
                            'data-target' => '#create-modal',
                            'value' =>$model->pur_number,
                            //'status' =>$model->purchas_status,
                            //'currency_code' =>$model->currency_code,
                        ]);

                        return $data;
                    },
            ],
            [
                'label'=>'仓库',
                'attribute' => 'ids',
                "format" => "raw",



                'value'=>
                    function($model){
                        $data =BaseServices::getWarehouseCode($model->warehouse_code).'<br/>';
                        if(!empty($model->is_transit) && $model->is_transit!=1)
                        {
                            if(!empty($model->transit_warehouse)){
                                $data .= BaseServices::getWarehouseCode($model->transit_warehouse);
                            }

                        }
                        return  $data;   //主要通过此种方式实现
                    },
            ],
            [
                'label'=>'运输方式',
                'attribute' => 'ids',
                'value'=>
                    function($model){
                        return  PurchaseOrderServices::getShippingMethod($model->shipping_method);   //主要通过此种方式实现
                    },
            ],
            [
                'label'=>'sku/包装方式',
                'attribute' => 'ids',
                "format" => "raw",
                'value'=>
                    function($model){
                        return  PurchaseOrderItems::getSkus($model->pur_number,2,$model->warehouse_code);   //主要通过此种方式实现
                    },
            ],
            [
                'label'=>'金额',
                'attribute' => 'ids',
                "format" => "raw",
                'value'=>
                    function($model, $key, $index, $column){
                        $data =Yii::t('app','应付').':'.PurchaseOrderItems::getCountPrice($model->pur_number).$model->currency_code."<br/>";
                        //$data.=Yii::t('app','实付').':'.$model->pur_number."<br/>";
                        //$data.=Yii::t('app','运费').':'.$model->orderShip['freight'].$model->currency_code."<br/>";
                        return $data;
                    },
            ],
            [
                'label'=>'创建时间',
                'attribute' => 'created_ats',
                "format" => "raw",
                'value'=>
                    function($model, $key, $index, $column){
                        return $model->created_at;
                    },
            ],
           /* [
                'label'=>'预计到货时间',
                'attribute' => 'created_ats',
                "format" => "raw",
                'value'=>
                    function($model, $key, $index, $column){
                        return $model->date_eta;
                    },
            ],*/
            [
                'label'=>'确认备注',
                'attribute' => 'created_ats',
                "format" => "raw",
                'value'=>
                    function($model, $key, $index, $column){
                        return $model->confirm_note;
                    },
            ],
           /* [
                'label'=>'审核备注',
                'attribute' => 'created_ats',
                "format" => "raw",
                'value'=>
                    function($model, $key, $index, $column){
                        return $model->audit_note;
                    },
            ],*/
            [
                'class' => 'kartik\grid\ActionColumn',
                'dropdown' => false,
                'width'=>'180px',
                'template' => '{edit}',
                'buttons'=>[
                    'view' => function ($url, $model, $key) {
                        return Html::a('<i class="glyphicon glyphicon-eye-open"></i> 详细', ['purchase-order/views','id'=>$key], [
                            'title' => Yii::t('app', '详细'),
                            'class' => 'btn btn-xs red view',
                            'data-toggle' => 'modal',
                            'data-target' => '#create-modal',
                        ]);
                    },
                    'review' => function ($url, $model, $key) {
                        return Html::a('<i class="glyphicon glyphicon-ok"></i> 审核', ['review','name'=>'audit'], [
                            'title' => Yii::t('app', '审核 '),
                            'class' => 'btn btn-xs purple',
                            'data-toggle' => 'modal',
                            'data-target' => '#create-modal',
                            'id' =>$key,
                        ]);
                    },
                    'edit' => function ($url, $model, $key) {
                        if ($model->refund_status == '6') {
                            return Html::a('<i class="glyphicon glyphicon-edit"></i> 编辑', ['edit','name'=>'audit','id'=>$key,'pur_number' => $model->pur_number], [
                                'title' => Yii::t('app', '审核 '),
                                'class' => 'btn btn-xs purple edit',
                                'data-toggle' => 'modal',
                                'data-target' => '#create-modal',
                                'id' =>$key,
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
        'bordered' =>true,
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
$historys         = Url::toRoute(['tong-tool-purchase/get-history']);
$js = <<<JS
    $(function(){
        $("#batch_review").on('click', function(){
            var ids = $('#grid_purchase_order').yiiGridView('getSelectedRows');
            if(ids==''){
                alert('请先选择!');
                return false;
            }else{
                $.get($(this).attr('href'), {id: ids},
                    function (data) {
                        $('.modal-body').html(data);
                    }
                );
            }
        });
    });
    $(document).on('click', '.b', function () {

        $.get($(this).attr('href'), {sku:$(this).attr('data')},
            function (data) {
               $('.modal-body').html(data);
            }
        );
    });
    $(document).on('click','.data-updates', function () {

        $.get('{$historys}', {sku:$(this).attr('data')},
            function (data) {
               $('.modal-body').html(data);

            }
        );
    });
    $(document).on('click', '#views', function () {
        $.get($(this).attr('href'), {id:$(this).attr('value'),status:$(this).attr('status'),currency_code:$(this).attr('currency_code')},
            function (data) {
                $('.modal-body').html(data);
            }
        );
    });
    $(document).on('click', '.purple', function () {
        $.get($(this).attr('href'), {id:$(this).attr('id')},
            function (data) {
                $('.modal-body').html(data);
            }
        );
    });
     $(document).on('click', '.view', function () {
        $.get($(this).attr('href'), {id:$(this).attr('id')},
            function (data) {
                $('.modal-body').html(data);
            }
        );
    });
     $(document).on('click', '.edit', function () {
        $.get($(this).attr('href'), {id:$(this).attr('id'),pur_number:$(this).attr('pur_number')},
            function (data) {
                $('.modal-body').html(data);
            }
        );
    });



JS;
$this->registerJs($js);
?>
