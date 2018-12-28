<?php
use yii\helpers\Html;
use kartik\grid\GridView;
use yii\bootstrap\Modal;
use yii\helpers\Url;
use app\services\PurchaseOrderServices;
use mdm\admin\components\Helper;
use app\config\Vhelper;
use toriphes\lazyload\LazyLoad;
use app\models\WarehouseResults;
use app\models\DeclareCustoms;

$this->title = '报关&开票';
$this->params['breadcrumbs'][] = $this->title;
?>
    <div class="panel panel-default">
        <div class="panel-body">
            <?= $this->render('_search', ['model' => $searchModel]); ?>
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
                'label'=>'Open_Id',
                'attribute' => 'id',
                'value'=> function($model){
                    return  $model->id;   //主要通过此种方式实现
                },
            ],
            [
                'label'=>'Item_Id',
                'attribute' => 'item_id',
                'value'=> function($model){
                    return  $model->item_id;   //主要通过此种方式实现
                },
            ],
            'pur_number', //采购单号
            [
                'label'=>'sku',
                'format'=>'raw',
                'value'=>function($model){
                    $subHtml = \app\models\ProductRepackageSearch::getPlusWeightInfo($model->sku,true);// 加重SKU标记
                    return $model->sku . $subHtml;
                }
            ],
            'name', //商品名称
            'product_img' => [//图片
                'attribute' => 'product_img',
                'format' => "raw",
                'value' => function ($model) {
                    $img = Html::img(Vhelper::downloadImg($model->sku,$model->product_img,2),['width'=>'80px']);
                    return $img;
                    // return Html::a($img,['purchase-suggest/img', 'sku' => $model->sku,'img' => $model->product_img], ['class' => "img", 'style'=>'margin-right:5px;', 'title' => '大图查看','data-toggle' => 'modal', 'data-target' => '#created-modal']);

                }
            ], 
            'purNumber.buyer', //采购员
            'purNumber.supplier_name',
            'pur_ticketed_point' => [
                'label' => '税点',
                'value' => function ($model) {
                    $pur_ticketed_point = !empty($model->supplierQuote) ? $model->supplierQuote->pur_ticketed_point : 0;
                    return $pur_ticketed_point.'%';
                }
            ], //税点
            'price', //订单价格

            'declared_price' => [//申报价格（美金）
                // 'visible'=>$bool,
                'label' => '申报价格（美金）',
                'format' => 'raw',
                'value' => function ($model) {
                    return DeclareCustoms::find()->select('price')->where(['pur_number'=>$model->pur_number, 'sku' => $model->sku])->scalar();
                }
            ],
            'customs_details' => [//报关信息
                'label' => '报关信息',
                'format' => 'raw',
                'value' => function ($model) {
                    $html = DeclareCustoms::getHtml($model->pur_number,$model->sku,$model->key_id);
                    return $html;
                }
            ],
            'is_customs' => [//是否报关
                'label' => '是否报关',
                'format' => 'raw',
                'value' => function ($model) {
                    $is_clear = DeclareCustoms::find()->select('is_clear')->where(['pur_number'=>$model->pur_number, 'sku' => $model->sku])->scalar();
                    return !empty($is_clear) ? (($is_clear==1) ? '否':'是') : '未知';
                }
            ],
            'open_time' => [
                'attribute' => 'purchaseTicketOpen.open_time',
                'value' => function ($model) {
                    return !empty($model->open_time)? date('Y-m-d', strtotime($model->open_time)):'';
                }
            ], //开票日期
            'ticket_name', //开票品名
            'issuing_office', //开票单位
            'tickets_number', //开票数量
            'total_par', //票面总金额
            'invoice_code', //开票编码
            'status' => [//状态
                'label' => '状态',
                'format' => 'raw',
                'value' => function ($model) {
                    $status = !empty($model->status)?$model->status : 0;
                    return PurchaseOrderServices::getTicketOpenStatus($status,true);
                }
            ],
            'note', //备注
            [
                'class' => 'kartik\grid\ActionColumn',
                'width'=>'100px',
                'template' => Helper::filterActionColumn('{update-ticket}<br />{financial-audit}'),
                'buttons'=>[
                    'update-ticket' => function ($url, $model, $key) {
                        $status = !empty($model->status)?$model->status : 0;

                        if ($status == 0 || $status == 3) {
                            return Html::a('<i class="glyphicon glyphicon-edit"></i> 采购更新', 
                                ['update-ticket', 'pur_number' => $model->pur_number, 'sku' => $model->sku,'key_id' => $model->key_id],
                                [
                                    'class' => 'btn btn-xs update-ticket',
                                    'data-toggle' => 'modal',
                                    'data-target' => '#create-modal',
                                ]
                            );
                        }
                    },
                    'financial-audit' => function ($url, $model, $key) {
                        $status = !empty($model->status)?$model->status : 0;
                        // Yii::$app->user->identity->username
                        if ($status == 1) {
                            return Html::a('<i class="glyphicon glyphicon-yen"></i> 财务审批', 
                                ['financial-audit', 'pur_number' => $model->pur_number, 'sku' => $model->sku,'key_id' => $model->key_id],
                                [
                                'class' => 'btn btn-xs financial-audit',
                                'data-toggle' => 'modal',
                                'data-target' => '#create-modal',
                                ]
                            );
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
$msg = '无数据';
$js = <<<JS
$(function() {
    /**
     * 更新开票
     */
    $(document).on('click', '.update-ticket', function () {
        $.get($(this).attr('href'), {},
            function (data) {
                $('.modal-body').html(data);
            }
        );
    });
    /**
     * 财务审批
     */
    $(document).on('click', '.financial-audit', function () {
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