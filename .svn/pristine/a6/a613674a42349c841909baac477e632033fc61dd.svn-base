<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\services\PurchaseOrderServices;
use app\services\BaseServices;
use app\services\SupplierServices;
use kartik\daterange\DateRangePicker;

use kartik\select2\Select2;
use yii\web\JsExpression;

/* @var $this yii\web\View */
/* @var $model app\models\PurchaseOrderPaySearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="purchase-order-pay-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <div class="col-md-1"><?= $form->field($model, 'pay_status')->dropDownList(['0'=>'作废 ','1'=>'待收款','2'=>'已收款'],['prompt' => '请选择'])->label('状态') ?></div>
    <!--    <div class="col-md-1"><?/*= $form->field($model, 'receipt_status')->dropDownList(['1'=>'全部到货','2'=>'部分到货','3'=>'入库'],['prompt' => 'please choose'])->label('到货状态') */?></div>-->
    <div class="col-md-1"><?= $form->field($model, 'pay_type')->dropDownList(SupplierServices::getDefaultPaymentMethod(),['prompt' => '请选择'])->label('支付方式') ?></div>

    <!--    <div class="col-md-1"><?/*= $form->field($model, 'pay_status')->dropDownList(['1'=>'是','2'=>'否'],['prompt' => 'please choose'])->label('是否已销账') */?></div>-->
    <div class="col-md-1"><?= $form->field($model, 'pur_number',[
            'inputOptions' => [
                'placeholder' => '多个用空格隔开',
                'class' => 'form-control',
            ],
        ]) ?></div>
    <div class="col-md-1"><?= $form->field($model, 'requisition_number') ?></div>

    <div class="col-md-1">
        <?= $form->field($model, 'applicant')->widget(Select2::classname(), [
            'options' => ['placeholder' => '请输入申请人...'],
            'data'    => BaseServices::getEveryOne(),
            'pluginOptions' => [
                'language'  => [
                    'errorLoading'  => new JsExpression("function () { return 'Waiting...'; }"),
                ],
                'escapeMarkup'      => new JsExpression('function (markup) { return markup; }'),
                'templateResult'    => new JsExpression('function(res) { return res.text; }'),
                'templateSelection' => new JsExpression('function (res) { return res.text; }'),
                'allowClear' => true
            ],
        ]);
        ?>
    </div>

    <div class="col-md-1">
        <?= $form->field($model, 'payer')->widget(Select2::classname(), [
            'options' => ['placeholder' => '请输入收款人...'],
            'data'    => BaseServices::getEveryOne(),
            'pluginOptions' => [
                'language'  => [
                    'errorLoading'  => new JsExpression("function () { return 'Waiting...'; }"),
                ],
                'escapeMarkup'      => new JsExpression('function (markup) { return markup; }'),
                'templateResult'    => new JsExpression('function(res) { return res.text; }'),
                'templateSelection' => new JsExpression('function (res) { return res.text; }'),
                'allowClear' => true
            ],
        ]);
        ?>
    </div>


    <div class="col-md-1">
        <?= $form->field($model, 'supplier_special_flag')->dropDownList(\app\services\SupplierServices::supplierSpecialFlag(),['prompt'=>'请选择'])->label('跨境宝供应商') ?>
    </div>

    <div class="col-md-2"><label class="control-label" for="purchaseorderpaysearch-applicant">创建时间：</label><?php
        $addon = <<< HTML
<span class="input-group-addon">
    <i class="glyphicon glyphicon-calendar"></i>
</span>
HTML;
        echo '<div class="input-group drp-container">';
        echo DateRangePicker::widget([
                'name'=>'PurchaseOrderReceiptSearch[created_at1]',
                'useWithAddon'=>true,
                'convertFormat'=>true,
                'startAttribute' => 'PurchaseOrderReceiptSearch[start_time]',
                'endAttribute' => 'PurchaseOrderReceiptSearch[end_time]',
                'startInputOptions' => ['value' => date('Y-m-d H:i:s',strtotime("-6 month"))],
                'endInputOptions' => ['value' => date('Y-m-d H:i:s',time())],
                'pluginOptions'=>[
                    'locale'=>['format' => 'Y-m-d H:i:s'],
                ]
            ]).$addon ;
        echo '</div>';
        ?></div>
    <div class="form-group col-md-3" style="margin-top: 24px;">
        <?= Html::submitButton(Yii::t('app', '搜索'), ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton(Yii::t('app', '重置'), ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
