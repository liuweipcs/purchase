<?php

use yii\helpers\Html;
use yii\widgets\Pjax;
use kartik\grid\GridView;
use yii\bootstrap\Modal;
use app\models\ReturnGoods;
use \app\services\BaseServices;
use app\services\PurchaseOrderServices;

/* @var $this yii\web\View */
/* @var $searchModel app\models\ReturnGoodsSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', '采购退货');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="exchange-goods-index">

    <h1><?php //echo Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?php //echo Html::a(Yii::t('app', 'Create Exchange Goods'), ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'options'=>[
            'id'=>'grid_exchange_goods',
        ],
        'columns' => [
            ['class' => 'yii\grid\CheckboxColumn','name'=>"id"],
            'return_number',
            'express_no',

            [
                'label'=>'运费',
                'attribute' => 'freights',
                'value'=> function($model){
                    return $model->freight;
                },

            ],
            [
                'label'=>'快递公司',
                'attribute' => 'cargo_companys',
                'value'=> function($model){
                    return $model->cargo_company;
                },

            ],
            'pur_number',
            [
                'label'=>'供应商名',
                'attribute' => 'supplier_names',
                'value'=> function($model){
                    return $supplier_name=!empty($model->supplier_name)?$model->supplier_name:BaseServices::getSupplierName($model->supplier_code);
                },

            ],
            [
                'label'=>'数量',
                'attribute' => 'qtys',
                'value'=> function($model){
                    return $model->qty;
                },

            ],

            [
                'label'=>'sku',
                'attribute' => 'skus',
                'value'=> function($model){
                    return $model->sku;
                },

            ],
            [
                'label'=>'产品名',
                'attribute' => 'pro_names',
                'value'=> function($model){
                    return $model->pro_name;
                },

            ],
            [
                'label'=>'采购员',
                'attribute' => 'buyers',
                'value'=> function($model){
                    return $model->buyer;
                },

            ],

            [
                'attribute' => 'state',
                'value'=> function($model){ return ReturnGoods::changeState()[$model->state];},
                'filter'=>ReturnGoods::changeState(),
            ],
            [
                'label'=>'创建人',
                'attribute' => 'create_users',
                'value'=> function($model){
                    return $model->create_user;
                },
            ],

            [
                'label'=>'创建时间',
                'attribute' => 'create_times',
                'value'=> function($model){ if($model->create_time){return date('Y-m-d H:i:s',$model->create_time);}else{ return '';} },
                'filterType'=>GridView::FILTER_DATETIME ,
            ],
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
                'header'=>'操作',
                'class' => 'yii\grid\ActionColumn',
                'template'=>'{update}',
                'buttons'=>[
                    'update'=> function ($url, $model, $key){
                        $html = '';
                        if ($model->state == 0) {
                            return Html::a('<i class="glyphicon glyphicon-plus"></i> 添加地址', ['addlogistic', 'id' => $model->id], [
                                'title' => Yii::t('app', '添加地址'),
                                'class' => 'btn btn-xs handle',
                                'data-toggle' => 'modal',
                                'data-target' => '#batch-handle-modal',
                            ]);
                        } elseif ($model->state == 2) {
                            $href = ['refund', 'id' => $key, 'pur_number' => $model->pur_number,'sku'=>$model->sku];
                            $options = [
                                'title' => Yii::t('app', '生成退款单'),
                                'data-confirm' => '确认生成退款单？',
                                'data-method' => 'post',
                            ];
                            $html = Html::a("<span class='label label-success'>生成退款单</span>", $href, $options);
                        }
                        return $html;
                    }
                ],
            ],

        ],

        'containerOptions' => ["style"=>"overflow:auto"], // only set when $responsive = false
        'toolbar' =>  [

            //'{export}',
        ],

        'pjax' => true,
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
    'id' => 'batch-handle-modal',
    'header' => '<h4 class="modal-title">采购退货</h4>',
    //'footer' => '<a href="#" class="btn btn-primary closes" data-dismiss="modal">Close</a>',
]);
Modal::end();

$js = <<<JS
$(function(){
    //处理异常
    $("a.handle").click(function(){
        var url=$(this).attr("href");
        $.post(url, {},
            function (data) {
                $('#batch-handle-modal').find('.modal-body').html(data);
            }
        );
    });
});
JS;
$this->registerJs($js);
?>
