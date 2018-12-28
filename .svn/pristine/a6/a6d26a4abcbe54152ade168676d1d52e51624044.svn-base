<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\services\PurchaseOrderServices;
use app\services\BaseServices;
use kartik\daterange\DateRangePicker;
use yii\web\JsExpression;
use kartik\select2\Select2;
$url = \yii\helpers\Url::to(['/supplier/search-supplier']);
/* @var $this yii\web\View */
/* @var $model app\models\PurchaseOrderPaySearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="purchase-order-pay-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <div class="col-md-1"><?= $form->field($model, 'pay_status')->dropDownList(['0' =>'作废',
                                                                                '1' =>'未申请付款',
                                                                                '2' =>'已申请付款(待审批)',
                                                                                '3' =>'审批不通过',
                                                                                '4' =>'已审批(待付款)',
                                                                                '5' =>'已付款',],['prompt' => '请选择'])->label('支付状态') ?></div>
    <div class="col-md-1"><?= $form->field($model, 'pur_number') ?></div>

    <div class="col-md-1"><?= $form->field($model, 'buyer')->widget(Select2::classname(), [
            'options' => ['placeholder' => '请输入采购员 ...'],
            'data' =>BaseServices::getEveryOne('','name'),
            'pluginOptions' => [
                'allowClear' => true,
                'language' => [
                    'errorLoading' => new JsExpression("function () { return 'Waiting...'; }"),
                ],
                'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
                'templateResult' => new JsExpression('function(res) { return res.text; }'),
                'templateSelection' => new JsExpression('function (res) { return res.text; }'),
            ],
        ])->label('采购员');
        ?>
    </div>

    <div class="col-md-1">
        <?= $form->field($model, 'supplier_code')->widget(Select2::classname(), [
            'options' => ['placeholder' => '请输入供应商 ...','value' =>!empty($name)?$name:''],
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

    <div class="col-md-1"><?= $form->field($model, 'requisition_number') ?></div>
    <div class="col-md-1"><?= $form->field($model, 'applicant')->dropDownList(BaseServices::getEveryOne(),['prompt' => '请选择']) ?></div>
    <div class="col-md-2"><label class="control-label" for="purchaseorderpaysearch-applicant">申请时间：</label><?php
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
//                'startInputOptions' => ['value' => date('Y-m-d H:i:s',strtotime("last month"))],
//                'endInputOptions' => ['value' => date('Y-m-d H:i:s',time())],
                'startInputOptions' => ['value' => !empty($model->start_time) ? date('Y-m-d',strtotime($model->start_time)) : date('Y-m-d',strtotime("last month"))],
                'endInputOptions' => ['value' => !empty($model->end_time) ? date('Y-m-d',strtotime($model->end_time)) : date('Y-m-d',time())],
                'pluginOptions'=>[
                    'locale'=>['format' => 'Y-m-d'],
                ]
            ]).$addon ;
        echo '</div>';
        ?></div>
    <div class="form-group col-md-1" style="margin-top: 24px;">
        <?= Html::submitButton(Yii::t('app', '搜索'), ['class' => 'btn btn-primary']) ?>
        <?= Html::a('重置', ['index'],['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
