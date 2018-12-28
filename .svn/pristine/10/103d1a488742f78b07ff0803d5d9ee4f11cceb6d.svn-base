<?php
use yii\helpers\Html;
use yii\bootstrap\Modal;
use kartik\grid\GridView;
use app\services\SupplierServices;
use app\services\PurchaseOrderServices;
use app\services\BaseServices;
use app\models\PurchaseOrderPay;
$this->title = '国内请款单';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="panel panel-success">
    <div class="panel-body">
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
            'label' => '合同单号',
            'format' => 'raw',
            'value' => function($model) {
                $data = "<p>合同号：{$model->pur_number}</p>";
                $data .= "<p>请款单号：{$model->requisition_number}</p>";
                return $data;
            }
        ],
        [
            'label' => '状态',
            'format' => 'raw',
            'value' => function($model) {
                return PurchaseOrderServices::getPayStatusType($model->pay_status);
            }
        ],
        [
            'label' => '请款金额',
            'format' => 'raw',
            'value' => function($model) {
                return '<strong>'.$model->pay_price.'</strong>';
            }
        ],
        [
            'label' => '供应商',
            'format' => 'raw',
            'value' => function($model) {
                $sub_html = \app\models\SupplierSearch::flagCrossBorder(true,$model->supplier_code);
                return BaseServices::getSupplierName($model->supplier_code).$sub_html;
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
            'template' => "{view}{delete}",
            'buttons' => [
                'view' => function($url, $model, $key) {
                    return Html::a('<i class="glyphicon glyphicon-eye-open"></i> 查看',['view', 'id' => $model->id],[
                        'title' => '查看',
                        'class' => 'btn btn-xs red view',
                        'data-toggle' => 'modal',
                        'data-target' => '#create-modal'
                    ]);
                },
                'delete' => function($url, $model, $key) {
                    if(!in_array($model->pay_status, [5, 6]) && Yii::$app->user->identity->id == $model->applicant) {
                        return Html::a('<i class="glyphicon glyphicon-trash"></i> 删除','javascript:void(0)', [
                            'title' => '删除',
                            'class' => 'btn btn-xs red delete',
                            'data-payid' => $model->id,
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

<?php
echo GridView::widget([
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
            'format' => 'raw',
            'value'=>function($model){
                $sub_html = \app\models\SupplierSearch::flagCrossBorder(true,$model->supplier_code);
                return BaseServices::getSupplierName($model->supplier_code).$sub_html;
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
                    if($model->pay_status == 10 && in_array(Yii::$app->user->identity->grade, [2, 3])) {
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
                    if(!in_array($model->pay_status, [5, 6]) && (Yii::$app->user->identity->id == $model->applicant) || BaseServices::getIsAdmin()) {
                        return Html::a('<i class="glyphicon glyphicon-trash"></i> 删除','javascript:void(0)', [
                            'title' => '删除',
                            'class' => 'btn btn-xs red delete',
                            'data-payid' => $model->id,
                        ]);
                    }
                },


/*                'edit' => function($url, $model, $key) {
                    if(!in_array($model->pay_status, [5, 6]) && Yii::$app->user->identity->id == $model->applicant) {
                        return Html::a('<i class="glyphicon glyphicon-pencil"></i> 编辑',['edit', 'id' => $model->id], [
                            'title' => '编辑',
                            'class' => 'btn btn-xs red edit',
                            'data-toggle' => 'modal',
                            'data-target' => '#create-modal'
                        ]);
                    }
                },*/

            ]
        ]
    ],
    'containerOptions'=>["style"=>"overflow:auto"],
    'pjax'=>false,
    'bordered'=>true,
    'striped'=>false,
    'condensed'=>true,
    'responsive'=>true,
    'hover'=>true,
    'floatHeader'=>false,
    'showPageSummary'=>false,
    'exportConfig'=>[
        GridView::EXCEL=>[],
    ],
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
    $('.view').click(function(){
        $('#create-modal .modal-body').html('');
        $('#create-modal .modal-body').load($(this).attr('href'));
    });
    $('.edit').click(function(){
        $('#create-modal .modal-body').html('');
        $('#create-modal .modal-body').load($(this).attr('href'));
    });
    $('.delete').click(function(){
        var self=this,
            id=$(self).attr('data-payid'),
            msg='确定删除ID为 <b style="color:red;">'+id+'</b> 的数据吗？';
        layer.confirm(msg,{icon:3,title:'温馨提示'},function(index){
            layer.load(0,{shade:false});
            $.ajax({
                url:'/purchase-order-pay/delete',
                data:{id:id},
                type:'post',
                dataType:'json',
                success:function(data){
                    if(data.error==0){
                        location.reload();
                    }else{
                        layer.alert(data.message);
                    }
                }
            });
        });
    });
});
JS;
$this->registerJs($js);
?>

