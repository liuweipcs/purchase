<?php
use yii\helpers\Html;
use yii\bootstrap\Modal;
use kartik\grid\GridView;
use app\services\SupplierServices;
use app\services\PurchaseOrderServices;
use app\services\BaseServices;
use app\models\PurchasePayForm;
use app\models\PurchaseSuggest;
use yii\helpers\Url;
use app\models\PurchaseOrderItems;
use mdm\admin\components\Helper;
use app\models\PurchaseOrderPay;
$this->title = '海外仓请款单';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="box box-success">
    <div class="box-body">
        <?= $this->render('_search', ['model' => $searchModel]); ?>
    </div>
</div>

<?php if($source == 1): ?>

<div class="btn-group" style="margin-bottom: 10px;">
    <span class="btn btn-danger" disabled="disabled">合同单</span>
    <a href="?source=2" class="btn btn-default">网采单</a>
</div>

<?= GridView::widget([
    'dataProvider' => $dataProvider,

    'columns' => [
        [
            'class' => 'yii\grid\CheckboxColumn',
            'name' => "id" ,
            'checkboxOptions' => function ($model) {
                return ['value' => $model->pur_number];
            }
        ],
        [
            'label' => 'id',
            'attribute' => 'ids',
            'value' => function($model) {
                return $model->id;
            }
        ],
        [
            'label' => '单号',
            'format' => 'raw',
            'value' => function($model) {
                $data = "<p style='margin-bottom: 8px;'>".PurchaseOrderServices::getPayStatusType($model->pay_status)."</p>";
                $data .= "<p>合同号：{$model->pur_number}</p>";
                $data .= "<p>请款单号：{$model->requisition_number}</p>";
                $form_id = PurchasePayForm::getPayForm($model->id);
                if($form_id) {
                    $data .= "<a title='查看付款申请书' href='/purchase-compact/show-form?id=$model->id' class='show-form' data-toggle='modal' data-target='#create-modal'><span class='glyphicon glyphicon-envelope'></span></a>";
                }
                return $data;
            }
        ],
        [
            'label' => '请款信息',
            'format' => 'raw',
            'value' => function($model) {
                $data = "<p>申请金额：<strong style='color:red;'>{$model->pay_price}</strong></p>";
                $data .= "<p>请款名称：{$model->pay_name}</p>";

                if(!empty($model->purchase_account)) {
                    $data .= "<p>付款账号：{$model->purchase_account}</p>";
                }
                if(!empty($model->pai_number)) {
                    $data .= "<p>拍单号：{$model->pai_number}</p>";
                }
                return $data;
            }
        ],
        [
            'label' => '信息',
            'width' => '300px',
            'format' => 'raw',
            'value' => function($model) {
                $sname = !empty($model->supplier_code) ? BaseServices::getSupplierName($model->supplier_code) : '';
                $js = !empty($model->settlement_method) ? SupplierServices::getSettlementMethod($model->settlement_method) : '';
                $zf = !empty($model->pay_type) ? SupplierServices::getDefaultPaymentMethod($model->pay_type) : '';
                $data = "<p>供应商：".$sname."</p>";
                $data .= "<p>支付方式：".$zf."</p>";
                $data .= "<p>结算方式：".$js."</p>";
                return $data;
            }
        ],
        [
            'label' => '申请人/时间',
            'format' => 'raw',
            'value' => function($model){
                if($model->applicant) {
                    $data = Yii::t('app', '申请人：').BaseServices::getEveryOne($model->applicant)."<br/>";
                    $data .= Yii::t('app', '申请时间：').$model->application_time."<br/>";
                    return $data;
                }
            }
        ],
        [
            'label' => '付款人/时间',
            'format' => 'raw',
            'value' => function($model) {
                if($model->payer) {
                    $data = Yii::t('app', '付款人：').BaseServices::getEveryOne($model->payer)."<br/>";
                    $data .= Yii::t('app', '付款时间：').$model->payer_time."<br/>";
                    return $data;
                }
            }
        ],
        [
            'label'  => '备注',
            'format' => 'raw',
            'width' => '200px',
            'value'  => function($model) {
                $data  = !empty($model->create_notice) ? Yii::t('app', '创建备注：').$model->create_notice."<br/>" : '';
                $data .= !empty($model->review_notice) ? Yii::t('app', '审核备注：').$model->review_notice."<br/>" : '';
                $data .= !empty($model->payment_notice) ? Yii::t('app', '财务备注：').$model->payment_notice."<br/>" : '';
                return $data;
            }
        ],
        [
            'class' => 'kartik\grid\ActionColumn',
            'width' => '180px',
            'header' => '操作',
            'template' => "<p>{view}</p><p>{audit}</p><p>{delete}</p><p>{form}</p>",
            'buttons' => [
                'view' => function($url, $model, $key) {
                    return Html::a('<i class="glyphicon glyphicon-eye-open"></i> 查看',['view', 'id' => $model->id],[
                        'title' => '查看',
                        'class' => 'btn btn-xs red c-view',
                        'data-toggle' => 'modal',
                        'data-target' => '#create-modal'
                    ]);
                },
                'audit' => function($url, $model, $key) {
                    if($model->pay_status == 10) {
                        return Html::a('<i class="glyphicon glyphicon-wrench"></i> 经理审核',['compact-audit', 'id' => $model->id], [
                            'title' => '经理审核',
                            'class' => 'btn btn-xs red c-audit',
                            'data-toggle' => 'modal',
                            'data-target' => '#create-modal'
                        ]);
                    }
                },
                'delete' => function($url, $model, $key) {
                    if(!in_array($model->pay_status, [5, 6]) && Yii::$app->user->identity->id == $model->applicant) {
                        return Html::a('<i class="glyphicon glyphicon-trash"></i> 删除','javascript:void(0)', [
                            'title' => '删除',
                            'class' => 'btn btn-xs red delete',
                            'data-payid' => $model->id,
                        ]);
                    }
                },
                'form' => function($url, $model, $key) {
                    if(Yii::$app->user->identity->id == $model->applicant || Yii::$app->user->identity->id == 307) {
                        return Html::a('<i class="glyphicon glyphicon-pencil"></i> 添加付款申请书',['write-payment', 'compact_number' => $model->pur_number, 'pid' => $model->id], [
                            'title' => '添加付款申请书',
                            'class' => 'btn btn-xs red',
                        ]);
                    }
                }
            ]
        ]
    ],
    'containerOptions' => ["style" => "overflow:auto"],
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
/*    'exportConfig' => [
        GridView::EXCEL => [],
    ],*/
    'panel' => [
        'type' => 'success',
    ]
]);
?>

<?php else: ?>

<div class="btn-group" style="margin-bottom: 10px;">
    <a href="?source=1" class="btn btn-default">合同单</a>
    <span class="btn btn-danger" disabled="disabled">网采单</span>
</div>

<?= GridView::widget([
    'dataProvider'=>$dataProvider,
    'columns'=>[
        [
            'class'=>'kartik\grid\CheckboxColumn',
            'name'=>"id",
            'checkboxOptions'=>function ($model,$key,$index,$column){
                return ['value'=>$model->id];
            }
        ],
        [
            'label'=>'id',
            'attribute'=>'ids',
            'value'=>function($model){
                return $model->id;
            }
        ],
        [
            'label' => '单号',
            'format' => 'raw',
            'value' => function($model) {
                $data = "<p>".PurchaseOrderServices::getPayStatusType($model->pay_status)."</p>";
                $data .= "<p>订单号：{$model->pur_number}</p>";
                $data .= "<p>请款单号：{$model->requisition_number}</p>";
                if($model->orderOrders) {
                    $data .= "<p>拍单号：{$model->orderOrders->order_number}</p>";
                }
                return $data;
            }
        ],
        [
            'label' => '请款金额/运费',
            'format' => 'raw',
            'value' => function($model) {
                return PurchaseOrderPay::getPrice($model);
            }
        ],
        [
            'label'=>'供应商名称',
            'value'=>function($model){
                return BaseServices::getSupplierName($model->supplier_code);
            }
        ],
        [
            'label' => '支付方式',
            'value' => function($model) {
                return !empty($model->pay_type) ? SupplierServices::getDefaultPaymentMethod($model->pay_type) : '';
            }
        ],
        [
            'label' => '结算方式',
            'value' => function($model) {
                return !empty($model->settlement_method) ? SupplierServices::getSettlementMethod($model->settlement_method) : '';
            }
        ],
        [
            'label' => '申请',
            'format' => 'raw',
            'value' => function($model){
                if($model->applicant) {
                    $data = Yii::t('app', '申请人：').BaseServices::getEveryOne($model->applicant)."<br/>";
                    $data .= Yii::t('app', '申请时间：').$model->application_time."<br/>";
                    return $data;
                }
            }
        ],
        [
            'label' => '付款',
            'format' => 'raw',
            'value' => function($model) {
                if($model->payer) {
                    $data = Yii::t('app', '付款人：').BaseServices::getEveryOne($model->payer)."<br/>";
                    $data .= Yii::t('app', '付款时间：').$model->payer_time."<br/>";
                    return $data;
                }
            }
        ],
        [
            'label'  => '备注',
            'format' => 'raw',
            'width' => '200px',
            'value'  => function($model) {
                $data  = !empty($model->create_notice) ? Yii::t('app', '创建备注：').$model->create_notice."<br/>" : '';
                $data .= !empty($model->review_notice) ? Yii::t('app', '审核备注：').$model->review_notice."<br/>" : '';
                $data .= !empty($model->payment_notice) ? Yii::t('app', '财务备注：').$model->payment_notice."<br/>" : '';
                return $data;
            }
        ],
        [
            'class'=>'kartik\grid\ActionColumn',
            'width'=>'180px',
            'header'=>'操作',
            'template'=>"{view}{audit}{submit}{delete}{edit}",
            'buttons'=>[
                'view' => function($url,$model,$key) {
                    return Html::a('<i class="glyphicon glyphicon-eye-open"></i> 查看',['view','id' => $model->id],[
                        'title'=>'查看',
                        'class'=>'btn btn-xs red view',
                        'data-toggle'=>'modal',
                        'data-target'=>'#create-modal'
                    ]);
                },
                'audit' => function($url, $model, $key) {
                    if($model->pay_status == 10) {
                        return Html::a('<i class="glyphicon glyphicon-wrench"></i> 经理审核',['audit', 'id' => $model->id], [
                            'title' => '经理审核',
                            'class' => 'btn btn-xs red audit',
                            'data-toggle' => 'modal',
                            'data-target' => '#create-modal'
                        ]);
                    }
                },
                'submit' => function($url, $model, $key) {
                    if($model->pay_status == -1 && Yii::$app->user->identity->id == $model->applicant) {
                        return Html::a('<i class="glyphicon glyphicon-open"></i> 提交',['submit','id' => $model->id], [
                            'title' => '提交',
                            'class' => 'btn btn-xs red submit',
                            'data-toggle' => 'modal',
                            'data-target' => '#create-modal'
                        ]);
                    }
                },
                'delete' => function($url, $model, $key) {
                    if(!in_array($model->pay_status, [5, 6]) && Yii::$app->user->identity->id == $model->applicant) {
                        return Html::a('<i class="glyphicon glyphicon-trash"></i> 删除','javascript:void(0)', [
                            'title' => '删除',
                            'class' => 'btn btn-xs red delete',
                            'data-payid' => $model->id,
                        ]);
                    }
                },
            ]
        ]
    ],
    'containerOptions'=>["style"=>"overflow:auto"],
    'toolbar' =>  [],
    'pjax'=>false,
    'bordered'=>true,
    'striped'=>false,
    'condensed'=>true,
    'responsive'=>true,
    'hover'=>true,
    'floatHeader'=>false,
    'showPageSummary'=>false,
    'panel'=>[
        'type'=>'success',
    ]
]);
?>

<?php endif; ?>

<?php
Modal::begin([
    'id'=>'create-modal',
    'header'=>'<h4 class="modal-title">系统信息</h4>',
    'footer'=>'<a href="#" class="btn btn-primary closes" data-dismiss="modal">关闭</a>',
    'size'=>'modal-lg',
    'options'=>[
        'data-backdrop'=>'static',
    ]
]);
Modal::end();
$js = <<<JS
$(function(){
    $('.submit').click(function(){
        $('#create-modal .modal-body').html('');
        $('#create-modal .modal-body').load($(this).attr('href'));
    });
    
    
    $('.audit').click(function(){
        $('#create-modal .modal-body').html('');
        $('#create-modal .modal-body').load($(this).attr('href'));
    });
    
    // 合同单查看
    $('.c-view').click(function() {
        $('#create-modal .modal-body').html('');
        $('#create-modal .modal-body').load($(this).attr('href'));
    });
    
    // 合同单审核
    $('.c-audit').click(function() {
        $('#create-modal .modal-body').html('');
        $('#create-modal .modal-body').load($(this).attr('href'));
    });
    
    $('.view').click(function(){
        $('#create-modal .modal-body').html('');
        $('#create-modal .modal-body').load($(this).attr('href'));
    });
    $('.edit').click(function(){
        $('#create-modal .modal-body').html('');
        $('#create-modal .modal-body').load($(this).attr('href'));
    });
    
    $('.delete').click(function() {
        var self = this,
            id = $(self).attr('data-payid');
        layer.confirm('是否要删除', {icon:3, title:'温馨提示'}, function(index) {
            $.ajax({
                url: '/overseas-purchase-order-pay/delete',
                data: {id:id},
                type: 'post',
                dataType: 'json',
                success:function(data) {
                    if(data.error == 0) {
                        location.reload();
                    } else {
                        layer.alert(data.message);
                    }
                }
            });
        });
    });
    
    $('.show-form').click(function() {
        $.get($(this).attr('href'), function(data) {
            $('.modal-body').html(data);
        });
    });
    
});
JS;
$this->registerJs($js);
?>

