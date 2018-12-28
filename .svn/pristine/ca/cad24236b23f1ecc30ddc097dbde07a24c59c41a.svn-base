<?php
use yii\helpers\Html;
use kartik\grid\GridView;
use yii\bootstrap\Modal;
use yii\helpers\Url;
use app\services\PurchaseOrderServices;
use app\services\SupplierServices;
use app\services\BaseServices;
use app\models\PurchaseOrderItems;
use app\models\PurchaseOrderPay;
use app\models\PurchaseOrderPayDetail;
use app\models\SupervisorGroupBind;
use app\models\UebExpressReceipt;
use mdm\admin\components\Helper;

$this->title = '售后单信息';
$this->params['breadcrumbs'][] = $this->title;

$bool = SupervisorGroupBind::getGroupPermissions(38);
?>

<?= GridView::widget([
    'dataProvider' => $dataProvider,
    //'filterModel' => $searchModel,
    'options'=>[
        'id'=>'grid_purchase_order',
    ],
    //'showFooter'=>true,
    'filterSelector' => "select[name='".$dataProvider->getPagination()->pageSizeParam."'],input[name='".$dataProvider->getPagination()->pageParam."']",
    'pager'=>[
        'options'=>['class' => 'pagination','style'=> "display:block;"],
        'class'=>\liyunfang\pager\LinkPager::className(),
        'pageSizeList' => [20, 50, 100, 200],
//                'options'=>['class'=>'hidden'],//关闭分页
        'firstPageLabel'=>"首页",
        'prevPageLabel'=>'上一页',
        'nextPageLabel'=>'下一页',
        'lastPageLabel'=>'末页',
    ],
    'columns' => [
//        [
//            'class' => 'kartik\grid\CheckboxColumn',
//            'name'=>"id" ,
//            'checkboxOptions' => function ($model, $key, $index, $column) {
//                return ['value' => $model->id];
//            }
//
//        ],
        [
            'label'=>'SKU',
            'value'=>function($model){
                return $model->sku;
            }
        ],
        [
            'label'=>'平台',
            'value'=>function($model){
                return $model->platform_code;
            }
        ],
        [
            'label'=>'售后单创建时间',
            'value'=>function($model){
                return $model->data_create_time;
            }
        ],
        [
            'label'=>'异常原因',
            'value'=>function($model){
                return $model->reason;
            }
        ]
   ],
    'containerOptions' => ["style"=>"overflow:auto"], // only set when $responsive = false
    'toolbar' =>  [

        '{export}',
    ],


    'pjax' => false,
    'bordered' => true,
    'striped' => false,
    'condensed' => true,
    'responsive' => true,
    'hover' => true,
    'floatHeader' => false,
    'showPageSummary' => true,

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

JS;
$this->registerJs($js);
?>