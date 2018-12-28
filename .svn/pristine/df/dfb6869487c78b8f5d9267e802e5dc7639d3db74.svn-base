<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\daterange\DateRangePicker;
use app\services\BaseServices;
use app\services\SupplierServices;
use app\services\PurchaseOrderServices;
use kartik\select2\Select2;
use yii\web\JsExpression;
$url = \yii\helpers\Url::to(['/supplier/search-supplier']);
/* @var $this yii\web\View */
/* @var $model app\models\PurchaseOrderSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="purchase-order-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',

    ]); ?>

    <?= Html::input('hidden', 'source', $model->source) ?>


    <div class="col-md-1"><?= $form->field($model, 'buyer')->widget(Select2::classname(), [
            'options' => ['placeholder' => '请输入采购员 ...'],
            'data' =>BaseServices::getEveryOne('','name'),
            'pluginOptions' => [
                'allowClear' => true,
                'language' => [
                    'errorLoading' => new JsExpression("function () { return 'Waiting...'; }"),
                ],
                /*'ajax' => [
                    'url' => $url,
                    'dataType' => 'json',
                    'data' => new JsExpression('function(params) { return {q:params.term}; }')
                ],*/
                'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
                'templateResult' => new JsExpression('function(res) { return res.text; }'),
                'templateSelection' => new JsExpression('function (res) { return res.text; }'),
            ],
        ])->label('采购员');
        ?>
    </div>
    <div class="col-md-1"><?= $form->field($model, 'purchas_status')->dropDownList(['99'=>'未全部到货','6'=>'全到货','7'=>'等待到货','8'=>'部分到货等待剩余','9'=>'部分到货不等待剩余','10'=>'已作废'],['prompt' => '请选择']) ?></div>
    <!--<div class="col-md-1"><?/*= $form->field($model, 'pay_status')->dropDownList(['0' =>'作废',
                                                                                '1' =>'未申请付款',
                                                                                '2' =>'已申请付款(待审批)',
                                                                                '3' =>'审批不通过',
                                                                                '4' =>'已审批(待付款)',
                                                                                '5' =>'已付款',],['prompt' => '请选择'])->label('付款状态') */?></div>-->
    <div class="col-md-1">
        <?= $form->field($model,'pay_status')->dropDownList(PurchaseOrderServices::getPayStatus(), ['prompt' => '请选择'])->label('付款状态'); ?>
    </div>
    <!--<div class="col-md-1"><?/*= $form->field($model, 'refund_status')->dropDownList(PurchaseOrderServices::getReceiptStatus(),['prompt' => '请选择'])->label('退款状态') */?></div>-->

<!--    <div class="col-md-1">--><?php //$form->field($model, 'is_arrival')->dropDownList(['2'=>'今日到','1'=>'晚到'], ['prompt' => 'please choose'])->label('到货') ?><!--</div>-->
    <!--<div class="col-md-1"><?/*= $form->field($model, 'receiving_exception_status')->dropDownList(PurchaseOrderServices::getPurchaseEx(), ['prompt' => '请选择'])->label('收货异常状态') */?></div>
    <div class="col-md-1"><?/*= $form->field($model, 'qc_abnormal_status')->dropDownList(PurchaseOrderServices::getPurchaseExs(), ['prompt' => '请选择'])->label('QC异常状态') */?></div>-->
    <div class="col-md-1"><?= $form->field($model, 'pur_number', [
            'inputOptions' => [
                'placeholder' => '多个用逗号隔开',
                'class' => 'form-control',
            ],
        ]) ?></div>
<!--    <div class="col-md-1">--><?php //$form->field($model, 'ss.supplier_type')->dropDownList(SupplierServices::getSupplierType(), ['prompt' => 'please choose'])->label('供应商类型') ?><!--</div>-->
    <div class="col-md-1">
        <?= $form->field($model, 'supplier_code')->widget(Select2::classname(), [
            'options' => ['placeholder' => '请输入供应商 ...','value' =>!empty($model->supplier_code)?$model->supplier_code:''],
            'pluginOptions' => [
                'placeholder' => 'search ...',
                'allowClear' => true,
                'language' => [
                    'errorLoading' => new JsExpression("function () { return 'Waiting...'; }"),
                ],
                'ajax' => [
                    'url' => $url,
                    'dataType' => 'json',
                    'data' => new JsExpression('function(params) { return {q:params.term,status:null}; }')
                ],
                'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
                'templateResult' => new JsExpression('function(res) { return res.text; }'),
                'templateSelection' => new JsExpression('function (res) { return res.text; }'),
            ],
        ])->label('供应商');
        ?>
    </div>

   <!-- <div class="col-md-1"><?/*= $form->field($model, 'create_type')->dropDownList(PurchaseOrderServices::getCreateType(), ['prompt' => 'please choose']) */?></div>-->
    <div class="col-md-1"><?= $form->field($model, 'items.sku', [
            'inputOptions' => [
                'placeholder' => '多个用逗号隔开',
                'class' => 'form-control',
            ],
        ])->label('sku') ?></div>
    <div class="col-md-1"><?= $form->field($model, 'ship.express_no')->label('物流单号') ?></div>
    <div class="col-md-1"><?= $form->field($model, 'account_type')->dropDownList(SupplierServices::getSettlementMethod(), ['prompt' => '请选择'])->label('结算方式') ?></div>
    <!--<div class="col-md-1"><?php /*$form->field($model, 'audit_return')->dropDownList(PurchaseOrderServices::getAuditReturn(), ['prompt' => 'please choose']) */?></div>-->
    <div class="col-md-1"><?= $form->field($model, 'warehouse_code')->dropDownList(BaseServices::getWarehouseCode(), ['prompt' => '请选择']) ?></div>
   <!-- <div class="col-md-1"><?/*= $form->field($model, 'shipping_method')->dropDownList(\app\services\PurchaseOrderServices::getShippingMethod(), ['prompt' => 'please choose']) */?></div>-->
   <!-- <div class="col-md-1"><?/*= $form->field($model, 'merchandiser')->dropDownList(BaseServices::getEveryOne('','name'), ['prompt' => 'please choose']) */?></div>-->

    <div class="col-md-2" ><label class="control-label" for="purchaseorderpaysearch-applicant">审核时间：</label><?php
        $addon = <<< HTML
<span class="input-group-addon">
    <i class="glyphicon glyphicon-calendar"></i>
</span>
HTML;
        echo '<div class="input-group drp-container">';
        echo DateRangePicker::widget([
                'name'=>'PurchaseOrderSearch[time]',
                'useWithAddon'=>true,
                'convertFormat'=>true,
                'startAttribute' => 'PurchaseOrderSearch[start_time]',
                'endAttribute' => 'PurchaseOrderSearch[end_time]',
//            'startInputOptions' => ['value' => date('Y-m-d H:i:s',strtotime("-6 month"))],
//            'endInputOptions' => ['value' => date('Y-m-d H:i:s',time())],
//            'startInputOptions' => ['value' => !empty($model->start_time) ? $model->start_time : date('Y-m-d',strtotime("-6 month"))],
                'startInputOptions' => ['value' => !empty($model->start_time) ? date('Y-m-d',strtotime($model->start_time)) : date('Y-m-d',strtotime("-6 month"))],
                'endInputOptions' => ['value' => !empty($model->end_time) ? date('Y-m-d',strtotime($model->end_time)) : date('Y-m-d',time())],
                'pluginOptions'=>[
                    'locale'=>['format' => 'Y-m-d'],
                ]
            ]).$addon ;
        echo '</div>';
        ?></div>


    <div class="col-md-1">
        <?= $form->field($model, 'supplier_special_flag')->dropDownList(\app\services\SupplierServices::supplierSpecialFlag(),['prompt'=>'请选择'])->label('跨境宝供应商') ?>
    </div>
    <div class="form-group col-md-1" style="margin-top: 24px;float:left">
        <?= Html::submitButton('搜索', ['class' => 'btn btn-primary']) ?>
        <?= Html::a('重置', ['index', 'source' => $model->source], ['class' => 'btn btn-default']) ?>


    </div>
    <?php ActiveForm::end(); ?>

</div>
