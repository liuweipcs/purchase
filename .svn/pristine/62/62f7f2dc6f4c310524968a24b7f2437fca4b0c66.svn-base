<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\daterange\DateRangePicker;
use app\services\BaseServices;
use app\services\SupplierServices;
use app\services\PlatformSummaryServices;
use kartik\select2\Select2;
use yii\web\JsExpression;

/* @var $this yii\web\View */
/* @var $model app\models\TodayListSearch */
/* @var $form yii\widgets\ActiveForm */

$platform = \app\models\PlatformSummarySearch::overseasPlatformList(null,true);
$url = \yii\helpers\Url::to(['/supplier/search-supplier']);
?>

<div class="purchase-order-search">
    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]);?>
    <input type="hidden" name="PlatformSummarySearch[tab]" value="0">
    <div class="col-md-1">
        <?= $form->field($model, 'sku')->textInput() ?>
    </div>
    <?php if(!in_array(Yii::$app->user->id,$authData)){ ?>
    <div class="col-md-1">
        <?= $form->field($model, 'demand_number')->textInput() ?>
    </div>
    <div class="col-md-1"><?= $form->field($model, 'product_category')->widget(Select2::classname(), [
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
    <div class="col-md-1"><?= $form->field($model, 'purchase_warehouse')->widget(Select2::classname(), [
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
        <?= $form->field($model, 'order.purchas_status')->dropDownList(['1'=>'未生成','2'=>'已生成'],['prompt' => '请选择'])->label('是否生成采购单') ?>
    </div>
    <div class="col-md-1">
        <?= $form->field($model, 'level_audit_status')->dropDownList(PlatformSummaryServices::getLevelAuditStatus(),['prompt' => '请选择']) ?>
    </div>
    <div class="col-md-2">
        <?= $form->field($model, 'supplier_code')->widget(Select2::classname(), [
            'options' => ['placeholder' => '请输入供应商 ...',],
            'pluginOptions' => [
                'placeholder' => 'search ...',
                'allowClear' => true,
                'language' => [
                    'errorLoading' => new JsExpression("function () { return 'Waiting...'; }"),
                ],
                'ajax' => [
                    'url' => $url,
                    'dataType' => 'json',
                    'data' => new JsExpression('function(params) { return {q:params.term}; }')
                ],
                'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
                'templateResult' => new JsExpression('function(res) { return res.text; }'),
                'templateSelection' => new JsExpression('function (res) { return res.text; }'),
            ],
        ])->label('供应商');
        ?>
    </div>
    <div style="clear: both"></div>

    <div class="col-md-3" ><label class="control-label" for="purchaseorderpaysearch-applicant">需求时间：</label><?php
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
                'startInputOptions' => ['value' => isset($model->start_time)?$model->start_time:date('Y-m-d 00:00:00',strtotime("-6 month"))],
                'endInputOptions' => ['value' => isset($model->end_time)?$model->end_time:date('Y-m-d 23:59:59',time())],
                'pluginOptions'=>[
                    'locale'=>['format' => 'Y-m-d H:i:s'],
                ]
            ]).$addon ;
        echo '</div>';
        ?></div>
    <?php }?>
    <div class="form-group col-md-1" style="margin-top: 24px;">
        <?= Html::submitButton('搜索', ['class' => 'btn btn-primary']) ?>
        <a class="btn btn-default" href="index">重置</a>
    </div>
    <?php ActiveForm::end(); ?>
</div>
