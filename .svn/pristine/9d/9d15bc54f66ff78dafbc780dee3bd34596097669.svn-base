<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\daterange\DateRangePicker;
use app\services\BaseServices;
use app\services\SupplierServices;
use kartik\select2\Select2;
use yii\web\JsExpression;
/* @var $this yii\web\View */
/* @var $model app\models\TodayListSearch */
/* @var $form yii\widgets\ActiveForm */
$platform = \app\models\PlatformSummarySearch::overseasPlatformList(null,true);
?>

<div class="purchase-order-search">
    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]);?>
    <div class="col-md-1">
        <?= $form->field($model, 'sku')->textInput() ?>
    </div>
    <div class="col-md-1">
        <?= $form->field($model, 'demand_number')->textInput() ?>
    </div>
    <div class="col-md-2"><?= $form->field($model, 'product_category')->widget(Select2::classname(), [
            'options' => ['placeholder' => '请输入产品分类 ...'],
            'data' =>BaseServices::getCategory(),
            'pluginOptions' => [

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
        ])?></div>
    <div class="col-md-2"><?= $form->field($model, 'purchase_warehouse')->widget(Select2::classname(), [
            'options' => ['placeholder' => '请选仓库 ...'],
            'data' =>BaseServices::getWarehouseCode(),
            'pluginOptions' => [

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
        ])?></div>
    <div class="col-md-1"><?= $form->field($model, 'platform_number')->dropDownList($platform,['prompt' => '请选择'])?></div>
    <div class="col-md-1">
        <?= $form->field($model, 'is_purchase')->dropDownList(['1'=>'未生成','2'=>'已生成'],['prompt' => '请选择']) ?>
    </div>
    <div class="col-md-1">
        <?= $form->field($model, 'level_audit_status')->dropDownList(['0'=>'待同意','1'=>'同意','2'=>'驳回','3'=>'撤销','4'=>'采购驳回','5'=>'删除'],['prompt' => '请选择']) ?>
    </div>
    <div class="col-md-1" ><label class="control-label" for="purchaseorderpaysearch-applicant">需求时间：</label><?php
        $addon = <<< HTML
<span class="input-group-addon">
    <i class="glyphicon glyphicon-calendar"></i>
</span>
HTML;
        echo '<div class="input-group drp-container">';
        echo DateRangePicker::widget([
                'name'=>'PlatformSummarySearch[time]',
                'useWithAddon'=>true,
                'convertFormat'=>true,
                'startAttribute' => 'PlatformSummarySearch[start_time]',
                'endAttribute' => 'PlatformSummarySearch[end_time]',
                'startInputOptions' => ['value' => date('Y-m-d H:i:s',strtotime("-12 month"))],
                'endInputOptions' => ['value' => date('Y-m-d H:i:s',time())],
                'pluginOptions'=>[
                    'locale'=>['format' => 'Y-m-d H:i:s'],
                ]
            ]).$addon ;
        echo '</div>';
        ?></div>

    <div class="form-group col-md-1" style="margin-top: 24px;">
        <?= Html::submitButton('搜索', ['class' => 'btn btn-primary']) ?>
        <a class="btn btn-default" href="index">重置</a>
    </div>
    <?php ActiveForm::end(); ?>
</div>
