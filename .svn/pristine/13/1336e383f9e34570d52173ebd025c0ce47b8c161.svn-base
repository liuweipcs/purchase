<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use kartik\file\FileInput;
use app\models\User;
use kartik\tabs\TabsX;
use app\services\BaseServices;
use app\services\PurchaseOrderServices;
use app\services\SupplierServices;

/* @var $this yii\web\View */
/* @var $model app\models\Stockin */

$this->title='采购详情 ： 采购单号:'.$model->pur_number;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', '采购审核'), 'url' => ['index']];

?>
<div class="stockin-view">


    <?php $form = ActiveForm::begin(); ?>
    <h3 class="fa-hourglass-3">基本信息</h3>
    <div class="row">


        <div class="col-md-2"><?= $form->field($model, 'pur_number')->textInput(['maxlength' => true,'placeholder'=>'易佰网络', 'disabled'=>'disabled']) ?></div>

        <div class="col-md-2"><?= $form->field($model, 'tracking_number')->textInput(['disabled'=>'disabled','value'=>'123123']) ?></div>
        <div class="col-md-2"><?= $form->field($model, 'supplier_code')->textInput(['maxlength' => true,'value'=>'','disabled'=>'disabled'])->label('入库单号') ?></div>
        <div class="col-md-2"><?= $form->field($model, 'warehouse_code')->textInput(['value'=>BaseServices::getWarehouseCode($model->warehouse_code),'disabled'=>'disabled']) ?></div>
        <div class="col-md-2"><?= $form->field($model, 'shipping_method')->textInput(['value'=>PurchaseOrderServices::getShippingMethod($model->shipping_method),'disabled'=>'disabled']) ?></div>
        <div class="col-md-2"><?= $form->field($model, 'supplier_code')->textInput(['disabled'=>'disabled','value'=>BaseServices::getSupplierName($model->supplier_code)])->label('供应商') ?></div>
        <div class="col-md-2"><?= $form->field($model, 'pay_status')->textInput(['disabled'=>'disabled','value'=>strip_tags(PurchaseOrderServices::getPayStatus($model->shipping_method))])->label('付款状态') ?></div>
        <div class="col-md-2"><?= $form->field($model, 'created_at')->textInput(['disabled'=>'disabled']) ?></div>
        <div class="col-md-2"><?= $form->field($model, 'date_eta')->textInput(['disabled'=>'disabled']) ?></div>
        <div class="col-md-2"><?= $form->field($model, 'audit_time')->textInput(['disabled'=>'disabled']) ?></div>
        <div class="col-md-2"><?= $form->field($model, 'creator')->textInput(['maxlength' => true,'disabled'=>'disabled']) ?></div>
        <div class="col-md-2"><?= $form->field($model, 'auditor')->textInput(['maxlength' => true,'disabled'=>'disabled',])->label('审批人') ?></div>
        <div class="col-md-2"><?= $form->field($model, 'buyer')->textInput(['maxlength' => true,'disabled'=>'disabled','value'=>!empty($model->buyer)?BaseServices::getEveryOne($model->buyer):'']) ?></div>
        <div class="col-md-2"><?= $form->field($model, 'purchas_status')->textInput(['maxlength' => true,'disabled'=>'disabled','value'=>strip_tags(PurchaseOrderServices::getPurchaseStatus($model->purchas_status))]) ?></div>
        <div class="col-md-2"><?= $form->field($model, 'pur_type')->textInput(['maxlength' => true,'disabled'=>'disabled','value'=>!empty($model->buyer)?PurchaseOrderServices::getPurType($model->pur_type):'']) ?></div>
        <div class="col-md-2"><?= $form->field($model, 'merchandiser')->textInput(['maxlength' => true,'disabled'=>'disabled','value'=>!empty($model->buyer)?BaseServices::getEveryOne($model->merchandiser):'']) ?></div>
        <div class="col-md-2"><?= $form->field($model, 'shipping_method')->textInput(['maxlength' => true,'disabled'=>'disabled','value'=>PurchaseOrderServices::getShippingMethod($model->shipping_method)]) ->label('建议运输方式')?></div>
        <div class="col-md-2"><?= $form->field($model, 'pay_number')->textInput(['maxlength' => true,'disabled'=>'disabled']) ->label('支付单号')?></div>
        <div class="col-md-2"><?= $form->field($model, 'reference')->textInput(['maxlength' => true,'disabled'=>'disabled'])?></div>
        <div class="col-md-2"><?= $form->field($model, 'account_type')->textInput(['maxlength' => true,'disabled'=>'disabled','value'=>!empty($model->account_type)?SupplierServices::getSettlementMethod($model->account_type):''])?></div>
        <div class="col-md-2"><?= $form->field($model, 'pay_type')->textInput(['maxlength' => true,'disabled'=>'disabled','value'=>!empty($model->pur_type)?SupplierServices::getDefaultPaymentMethod($model->pay_type):''])?></div>
    </div>
    <?php
    $items = [
        [
            'label'=>'<span class="glyphicon glyphicon-star" aria-hidden="true"></span>采购产品',
            'content'=>$this->render('view',['model'=>$model]),
            //'content'=> !empty($pay)?$pay:'暂无数据',

        ],
//        [
//            'label'=>'<span class="glyphicon glyphicon-certificate" aria-hidden="true"></span>入库信息',
//            //'content'=>$this->render('_contact',['model_pay'=>$model_contact]),
//            'content'=>!empty($contact)?$contact:'暂无数据',
//
//        ],
//        [
//            'label'=>'<span class="glyphicon glyphicon-question-sign" aria-hidden="true"></span>收货异常',
//            'content'=>'暂无数据',
//
//        ],
//        [
//            'label'=>'<span class="glyphicon glyphicon-warning-sign" aria-hidden="true"></span>质检异常',
//            'content'=>'暂无数据',
//
//        ],
//        [
//            'label'=>'<span class="glyphicon glyphicon-list-alt" aria-hidden="true"></span>采购日志',
//            'content'=>'暂无数据',
//
//        ],



    ];

    echo TabsX::widget([
        'items'=>$items,
        'position'=>TabsX::POS_ABOVE,
        'encodeLabels'=>false
    ]);?>

    <?php ActiveForm::end(); ?>




</div>

