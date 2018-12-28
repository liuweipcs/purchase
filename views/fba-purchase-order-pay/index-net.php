<?php
use yii\helpers\Html;
use yii\bootstrap\Modal;
use kartik\grid\GridView;
use app\services\SupplierServices;
use app\services\PurchaseOrderServices;
use app\services\BaseServices;
use app\models\PurchaseOrderPay;

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

                $order_number = !empty($model->purchaseOrderPayType) ? $model->purchaseOrderPayType->platform_order_number : '';

                if(!$order_number) {
                    $order_number = !empty($model->orderOrders) ? $model->orderOrders->order_number : '';
                }

                if($order_number) {
                    $data .= "<p>拍单号：{$order_number}</p>";
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
            'label'=>'付款方式',
            'value'=>function($model){
                return !empty($model->pay_type)?SupplierServices::getDefaultPaymentMethod($model->pay_type):'';
            }
        ],
        [
            'label'=>'结算方式',
            'value'=>function($model){
                return !empty($model->settlement_method)?SupplierServices::getSettlementMethod($model->settlement_method):'';
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
            'label'=>'备注',
            'value'=>function($model){
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
                    return Html::a('<i class="glyphicon glyphicon-eye-open"></i> 查看',['view','id'=>$model->id],[
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