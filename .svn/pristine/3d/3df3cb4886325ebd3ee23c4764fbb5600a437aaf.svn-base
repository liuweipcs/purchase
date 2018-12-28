<?php
use yii\helpers\Html;
use kartik\grid\GridView;
use yii\bootstrap\Modal;
use yii\helpers\Url;
use app\services\PurchaseOrderServices;
use app\services\SupplierServices;
use app\services\BaseServices;
use app\models\PurchaseOrderItems;
use app\models\PurchaseOrder;
use app\models\PurchaseOrderCancel;
use app\models\PurchaseOrderCancelSub;
use app\models\PurchaseOrderReceipt;

$this->title = '取消未到货审核';
$this->params['breadcrumbs'][] = $this->title;
?>
    <style type="text/css">
        em {
            font-style: normal;
            color: red;
        }
    </style>


    <div class="panel panel-default">
        <div class="panel-body">
            <?php echo $this->render('_search', ['model'=>$searchModel]); ?>
        </div>
    </div>

<?= GridView::widget([
    'dataProvider' => $dataProvider,

    'options'=>[
        'id'=>'grid_purchase_order',
    ],

    'pager'=>[
        'options'=>['class' => 'pagination','style'=> "display:block;"],
        'firstPageLabel'=>"首页",
        'prevPageLabel'=>'上一页',
        'nextPageLabel'=>'下一页',
        'lastPageLabel'=>'末页',
    ],
    'columns' => [
        [
            'label'=>'id',
            'attribute' => 'ids',
            'value'=>
                function($model){
                    return  $model->id;   //主要通过此种方式实现
                },

        ],
        [
            'label' => '采购单号',
            'width' => '150px',
            "format" => "raw",
            'value' => function($model, $url, $key) {
                return $model->pur_number;
            },
        ],
        [
            'label' => '状态',
            'width' => '100px',
            "format" => "raw",
            'value' => function($model, $url, $key) {
                $purchas_status = PurchaseOrder::getPurchasStatus($model->pur_number);
                $res = PurchaseorderServices::getPurchaseStatus($purchas_status);
                return is_array($res)? '' :$res;
            },
        ],
        [
            'label' => '采购员',
            'width' => '100px',
            "format" => "raw",
            'value' => function($model, $url, $key) {
                return PurchaseOrder::getBuyer($model->pur_number);
            },
        ],
        [
            'label' => '取消类型',
            'width' => '100px',
            "format" => "raw",
            'value' => function($model, $url, $key) {
                return PurchaseorderServices::getCancelTypeCss($model->cancel_type);
            },
        ],
        [
            'label' => '取消明细',
            'width' => '200px',
            "format" => "raw",
            'value' => function($model, $url, $key) {
                $info = PurchaseOrderCancelSub::getCancelDetail($model->id);
                return "取消件数：{$info['cancel_ctq_total']}件<br />取消金额：{$info['cancel_price_total']}元";
            },
        ],
        [
            'label' => '创建人/时间',
            //'width' => '200px',
            "format" => "raw",
            'value' => function($model, $url, $key) {
                return "创建人：{$model->buyer}<br />创建时间：{$model->create_time}";
            },
        ],
        [
            'label'=>'审核状态',
            "format" => "raw",
            "visible" => true,
            'value'=> function($model, $url, $key) {
                return PurchaseOrderServices::getCancelAuditStatusCss($model->audit_status);
            },
        ],
        [
            'label'=>'收款状态',
            "format" => "raw",
            "visible" => true,
            'value'=> function($model, $url, $key) {
                $pay_status = PurchaseOrderReceipt::getPayStatus($model->requisition_number);
                return $pay_status != '' ? PurchaseOrderServices::getReceiptStatusCss($pay_status) : '';
            },
        ],
        [
            'label' => '审核人/时间',
            "format" => "raw",
            'value' => function($model, $url, $key) {
                return "创建人：{$model->audit}<br />创建时间：{$model->audit_time}";
            },
        ],
        [
            'label'=>'备注',
            "format" => "raw",
            "visible" => true,
            'value'=> function($model, $url, $key) {
                return PurchaseOrderCancel::getNote($model->id);
            },
        ],
        [
            'class' => 'kartik\grid\ActionColumn',
            'dropdown' => false,
            'width' => '180px',
            'template' => \mdm\admin\components\Helper::filterActionColumn('{view}{audit}{delete}'),
            'buttons' => [
                'view' => function($url, $model, $key) {
                    return Html::a('<i class=" fa fa-fw fa-plus-square"></i> 查看', ['view', ['cancel_id' => $model->id,'pur_number'=>$model->pur_number]], [
                        'title' => Yii::t('app', '查看'),
                        'class' => 'btn btn-xs view',
                        'data-toggle' => 'modal',
                        'data-target' => '#create-modal',
                    ]);
                },
                'audit' => function($url, $model, $key) {
                    if ($model->audit_status == 1) {
                        return Html::a('<i class=" fa fa-fw fa-plus-square"></i> 审核', ['audit', ['cancel_id' => $model->id,'pur_number'=>$model->pur_number]], [
                            'title' => Yii::t('app', '查看'),
                            'class' => 'btn btn-xs view',
                            'data-toggle' => 'modal',
                            'data-target' => '#create-modal',
                        ]);
                    }
                },

                'delete' => function($url, $model, $key) {
                    $allow_delete = [3,4];
                    $audit_status = $model->audit_status;
                    $username = Yii::$app->user->identity->username;

                    if ( in_array($audit_status, $allow_delete) && ( $model->buyer==$username  || BaseServices::getIsAdmin()) ) {
                        return Html::a('<i class="glyphicon glyphicon-trash"></i> 删除', ['delete-cancel', 'id' => $model->id], [
                            'title' => Yii::t('app', '删除'),
                            'class' => 'btn btn-xs',
                            'data' => [
                                'confirm' => '确定要取消么?',
                            ],
                        ]);
                    }
                },
            ],
        ],
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
    'showPageSummary' => false,

    'exportConfig' => [
        GridView::EXCEL => [],
    ],
    'panel' => [
        'type'=>'success',
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
$(function() {
    
    $('.handler').click(function() {
        $('#create-modal .modal-body').html('');
        $('#create-modal .modal-body').load($(this).attr('href'));
    });
    
    $('.view').click(function() {
        $('#create-modal .modal-body').html('');
        $('#create-modal .modal-body').load($(this).attr('href'));
    });
    
});     

JS;
$this->registerJs($js);
?>