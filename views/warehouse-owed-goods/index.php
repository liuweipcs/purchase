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

$this->title = '当日仓库欠货';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="purchase-order-pay-index">


    <?php //echo $this->render('_search', ['model' => $searchModel]); ?>
    <?php Html::a(Yii::t('app', '导入通途采购单'), 'read-purchase', [
        'id' => 'creates',
        'data-toggle' => 'modal',
        'data-target' => '#create-modal',
        'class' => 'btn btn-success creates',
    ]);?>
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
            //'id',
            'sku',
            'platform_code',
            'platform_order_id',
            'warehouse_code',
            [
                'label'=>'欠货数量',
                'attribute' => 'quantity_goods',
                'format'=>'raw',
                'value'=>
                    function($model){
                        return  '<span style="color:red">'.$model->total_quantity_goods.'</span>';   //主要通过此种方式实现
                    },
            ],
            'name',
            'order_pay_time',
            'create_time',
            'update_time',
            'confirmor',
            'note',
            [
                'label'=>'是否已采购',
                'attribute' => 'supplier_namea',
                'format'=>'raw',
                'value'=>
                    function($model){
                        return  $model->is_purchase==2?'<span style="color:red">否</span>':'<span style="color:#00a65a">是</span>';   //主要通过此种方式实现
                    },
            ],
            [
                'class' => 'kartik\grid\ActionColumn',
                'dropdown' => false,
                'width'=>'180px',
                'template' => '{complete}{payment}',
                'buttons'=>[
                    'complete' => function ($url, $model, $key) {
                        if($model->is_purchase==0) {
                            return Html::a('<i class="fa fa-fw fa-check"></i> 编辑', ['disabled', 'sku' => $model->sku,'page'=>Yii::$app->request->get('page')], [
                                'title' => Yii::t('app', '采购明细'),
                                'class' => 'btn btn-xs red',
                                'data-toggle' => 'modal',
                                'data-target' => '#create-modal',
                                'id'    => 'views',
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
            //'heading'=>'<h3 class="panel-title"><i class="glyphicon glyphicon-globe"></i> Countries</h3>',
            'type'=>'success',
            //'after'=>Html::a('<i class="glyphicon glyphicon-repeat"></i> 刷新', ['index'], ['class' => 'btn btn-info']),
            'before'=>false,
            'after'=>false,
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

        $.get($(this).attr('href'), {},
            function (data) {
                $('.modal-body').html(data);
            }
        );
    });





JS;
$this->registerJs($js);
?>
