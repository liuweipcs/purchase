<?php

use yii\helpers\Html;
use kartik\grid\GridView;
use yii\bootstrap\Modal;
use yii\helpers\Url;
use app\services\BaseServices;
use app\services\PurchaseOrderServices;
use mdm\admin\components\Helper;
use app\config\Vhelper;
use toriphes\lazyload\LazyLoad;
use app\models\WarehouseResults;
use app\models\PurchaseTicketOpen;
use app\models\PurchaseOrderItemsStock;
use app\models\StockLog;
use app\models\DeclareCustoms;

$this->title = '含税采购订单跟踪';
$this->params['breadcrumbs'][] = $this->title;
$bool = false;
?>
    <div class="panel panel-default">
        <div class="panel-body">
            <?= $this->render('_search', ['model' => $searchModel]); ?>
        </div>
        <div class="panel-footer">
            <?php
            if(Helper::checkRoute('revoke-confirmation'))
                Html::a('采购确认', ['#'], ['class' => 'btn btn-success', 'id' => 'submit-audits', 'data-toggle' => 'modal', 'data-target' => '#create-modal',]);
            ?>
        </div>
    </div>

<?= GridView::widget([
        'dataProvider' => $dataProvider,
        // 'filterModel' => $searchModel,
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
                'class' => 'kartik\grid\CheckboxColumn',
                'name'=>"id" ,
                'checkboxOptions' => function ($model, $key, $index, $column) {
                    return ['value' => $model->pur_number];
                }
            ],
            [
                'label'=>'id',
                'attribute' => 'ids',
                'format' => 'raw',
                'value'=> function($model){
                    return Html::a($model->id,
                           ['all-details','pur_number' =>$model->pur_number,'sku' =>$model->sku,],
                           [
                               'class' => 'instock-details',
                               'data-toggle' => 'modal',
                               'data-target' => '#create-modal',
                           ]
                    );
                },
            ],
            [
                'label'=>'sku',
                'format'=>'raw',
                'value'=>function($model){
                    $subHtml = \app\models\ProductRepackageSearch::getPlusWeightInfo($model->sku,true);// 加重SKU标记
                    return $model->sku . $subHtml;
                }
            ],
            'pur_number', //采购单号
            [
                'label'=>'商品名称',
                'format'=>'raw',
                'width' => '130px',
                'value'=>function($model){
                    return $model->name;
                }
            ],
            'product_img' => [//图片
                // 'visible'=>$bool,
                'attribute' => 'product_img',
                'format' => "raw",
                'value' => function ($model) {
                    // $img = LazyLoad::widget(['src'=>Vhelper::getSkuImage($model->sku)]);
                    $img = Html::img(Vhelper::downloadImg($model->sku,$model->product_img,2),['width'=>'110px']);
                    return $img;
                    // return Html::a($img,['purchase-suggest/img', 'sku' => $model->sku,'img' => $model->product_img], ['class' => "img", 'style'=>'margin-right:5px;', 'title' => '大图查看','data-toggle' => 'modal', 'data-target' => '#created-modal']);

                }
            ], 
            'purNumber.buyer', //采购员
            'pur_ticketed_point' => [
                'label' => '税点',
                'value' => function ($model) {
                    $pur_ticketed_point = !empty($model->supplierQuote) ? $model->supplierQuote->pur_ticketed_point : 0;
                    return $pur_ticketed_point.'%';
                }
            ], //税点
            'price', //订单价格
            'is_check' => [//是否商检
                // 'visible'=>$bool,
                'label' => '是否商检',
                'value' => function ($model) {
                    $is_inspection = $model->product->is_inspection;
                    $res = $is_inspection===0?'未知':($is_inspection==1?'否':'是');
                    return $res;
                }
            ],
            'purNumber.supplier_name',//供应商名称
            'ctq',//订单数量
            'instock_qty_count' => [//入库总数 instock-details
                // 'visible' => $bool,
                'label' => '入库总数',
                'format' => 'raw',
                'value' => function ($model) {
                    $res = StockLog::find()
                        ->where(['pur_number'=>$model->pur_number, 'sku'=>$model->sku, 'operate_type'=> 'delivery'])
                        ->sum('change_qty');

                    return Html::a($res, 
                        ['instock-details','pur_number' =>$model->pur_number,'sku' =>$model->sku,],
                        [
                            'class' => 'instock-details',
                            'data-toggle' => 'modal',
                            'data-target' => '#create-modal',
                        ]
                    );
                }
            ],
            'delivery-details' => [//发货总数 delivery-details
                // 'visible' => $bool,
                'label' => '发货总数',
                'format' => 'raw',
                'value' => function ($model) {
                    $order_total = $model->purchaseOrderItemsStock['order_total']?:0;

                    return Html::a($order_total, 
                        ['delivery-details','pur_number' =>$model->pur_number,'sku' =>$model->sku,],
                        [
                            'class' => 'instock-details',
                            'data-toggle' => 'modal',
                            'data-target' => '#create-modal',
                        ]
                    );
                }
            ],
            'customs-details' => [//报关总数 customs-details
                // 'visible' => $bool,
                'label' => '报关总数',
                'format' => 'raw',
                'value' => function ($model) {
                    $amounts = DeclareCustoms::find()->select('amounts')->where(['pur_number'=>$model->pur_number, 'sku' => $model->sku])->sum('amounts');
                    $amounts = $amounts?:0;
                    return Html::a($amounts, 
                        ['customs-details','pur_number' =>$model->pur_number,'sku' =>$model->sku,],
                        [
                            'class' => 'instock-details',
                            'data-toggle' => 'modal',
                            'data-target' => '#create-modal',
                        ]
                    );
                }
            ],
            'ticket-details' => [//开票总数 ticket-details
                // 'visible' => $bool,
                'label' => '开票总数',
                'format' => 'raw',
                'value' => function ($model) {
                    $res = PurchaseTicketOpen::getOpenInfo($model->sku,$model->pur_number,true);
                    $tickets_number = !empty($res)? $res:0;

                    return Html::a($tickets_number, 
                        ['ticket-details','pur_number' =>$model->pur_number,'sku' =>$model->sku,],
                        [
                            'class' => 'instock-details',
                            'data-toggle' => 'modal',
                            'data-target' => '#create-modal',
                        ]
                    );
                }
            ],
            'inventory_quantity' => [//库存数量
                'label' => '库存数量',
                'value' => function ($model) {
                    //$stock = $model->purchaseOrderItemsStock['stock']?:0;
                    //$profit_loss = $model->purchaseOrderItemsStock['profit_loss']?:0;

                    // 库存数量 = 入库数量 - 报关数量
                    $stock   = StockLog::find()->where(['pur_number'=>$model->pur_number, 'sku'=>$model->sku, 'operate_type'=> 'delivery'])->sum('change_qty');
                    $amounts = DeclareCustoms::find()->select('amounts')->where(['pur_number'=>$model->pur_number, 'sku' => $model->sku])->sum('amounts');
                    $amounts = $amounts?:0;

                    return $stock - $amounts;
                    
                },
            ],
            [
                'label' => '退税仓实际库存',
                'value' => function ($model) {
                    $stock = \app\models\Stock::findOne(['warehouse_code' => 'TS','sku' => $model->sku]);
                    if($stock){
                        $stock = ($stock->available_stock)?$stock->available_stock:$stock->stock;// 优先显示 可用数量
                    }else{
                        $stock = '';
                    }
                    return $stock;
                }
            ],
            'overflowing_quantity' => [ //损溢数量
                'label' => '损溢数量',
                'value' => function ($model) {
                    $profit_loss = $model->purchaseOrderItemsStock['profit_loss']?:0;
                    return $profit_loss;
                }
            ],
            'reservoir-details' => [//库龄 reservoir-details
                // 'visible' => $bool,
                'label' => '库龄',
                'format' => 'raw',
                'value' => function ($model) {
                    $res = StockLog::getInstockInfo($model->pur_number, $model->sku); //已完结
                    
                    if (!empty($res[0]['ku_age'])) {
                        return Html::a($res[0]['ku_age'], 
                            ['reservoir-details','pur_number' =>$model->pur_number,'sku' =>$model->sku,],
                            [
                                'class' => 'instock-details',
                                'data-toggle' => 'modal',
                                'data-target' => '#create-modal',
                            ]
                        );
                    } else {
                        return ;
                    }

                    
                }
            ],
            'status' => [//状态
                'label' => '状态',
                'format' => 'raw',
                'value' => function ($model) {
                    $status = PurchaseTicketOpen::getOpenStatus($model->sku,$model->pur_number);
                    if ( ($status == 2) || ($status==3) ) {
                        $html = '已完成';
                    } else {
                        $html = '未完成';
                    }
                    return $html;
                    // return PurchaseOrderServices::getTicketOpenStatus($status,true);
                }
            ],

            [
                'class' => 'kartik\grid\ActionColumn',
                'dropdown' => true,
                'width'=>'180px',
                'template' => '{payment}',
                'visible' => $bool,
                'buttons'=>[
                    'payment' => function ($url, $model, $key) {

                        if (Yii::$app->user->identity->username) {

                            return Html::a('<i class="glyphicon glyphicon-yen"></i> 申请付款', ['payment', 'pur_number' => $model->pur_number], [
                                'title' => Yii::t('app', '申请付款'),
                                'class' => 'btn btn-xs payment',
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
            // '{export}',
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
    // 'closeButton' =>false,
    'options'=>[
        // 'data-backdrop'=>'static',//点击空白处不关闭弹窗
        'z-index' =>'-1',
    ],
]);
Modal::end();

$sumbitUrl  = Url::toRoute(['submit-audit']);
$msg        = '无数据';
$js         = <<<JS
$(function() {

    $(document).on('click', '.instock-details', function () {
        // var pur_number = $(this).attr('pur_number');
        // var sku = $(this).attr('sku');
        $.get($(this).attr('href'), {},
            function (data) {
                $('.modal-body').html(data);
            }
        );
    });
});
JS;
$this->registerJs($js);
?>