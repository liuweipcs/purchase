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

$this->title = '通途采购单';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="purchase-order-pay-index">


    <?php //echo $this->render('_search', ['model' => $searchModel]); ?>
    <?= Html::a(Yii::t('app', '导入通途采购单'), 'read-purchase', [
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
            'pur_number',
            'warehouse',
            'supplier_name',
            'sku',
            'buyer',
            'purchase_time',

            'product_name',
            'purchase_link',
            'currency',
            'purchase_price',
            'latest_offer',
            'purchase_quantity',

            'payment_status',
            'purchasing_status',
            'create_id',
            'create_time',
           /* [
                'class' => 'kartik\grid\ActionColumn',
                'dropdown' => false,
                'width'=>'180px',
                'template' => '{complete}{payment}',
                'buttons'=>[
                    'completes' => function ($url, $model, $key) {
                        return Html::a('<i class="glyphicon glyphicon-eye-open"></i> 采购明细', ['purchase-order/view','id'=>$model->pur_number], [
                            'title' => Yii::t('app', '采购明细'),
                            'class' => 'btn btn-xs red',
                            'data-toggle' => 'modal',
                            'data-target' => '#create-modal',
                            'id'=>'views',
                        ]);
                    },
                    'payments' => function ($url, $model, $key) {
                        return Html::a('<i class="glyphicon glyphicon-list-alt"></i> 日志', ['view','id'=>$key], [
                            'title' => Yii::t('app', '日志'),
                            'class' => 'btn btn-xs red',
                            'data-toggle' => 'modal',
                            'data-target' => '#create-modal',
                            'id'=>'logs',
                        ]);
                    },
                ],

            ],*/
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


    $(document).on('click', '#creates', function () {

        $.get($(this).attr('href'), {id:$(this).attr('value')},
            function (data) {
                $('.modal-body').html(data);
            }
        );
    });





JS;
$this->registerJs($js);
?>
