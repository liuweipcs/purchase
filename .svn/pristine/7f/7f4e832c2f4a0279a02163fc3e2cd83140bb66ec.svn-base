<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\daterange\DateRangePicker;
use yii\web\JsExpression;
use kartik\select2\Select2;
use app\services\BaseServices;
use app\models\PurchaseUser;

/* @var $this yii\web\View */
/* @var $model app\models\GroupAuditConfigSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="group-audit-config-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>
    <div class="col-md-1">
        <?= $form->field($model, 'time_limit')->label('时间节点(>)(H)') ?>
    </div>
    <div class="col-md-1">
    <?= $form->field($model, 'sku')->label('SKU') ?>
    </div>
    <div class="col-md-1"><?= $form->field($model, 'warehouse_code')->widget(\kartik\select2\Select2::classname(), [
        'options' => ['placeholder' => '请选仓库 ...'],
        'data' =>\app\services\BaseServices::getWarehouseCode(),
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
    ])->label('仓库')?></div>
    <div class="col-md-1">
        <?= $form->field($model, 'default_buyer')->widget(Select2::classname(), [
            'options' => ['placeholder' => '请选择'],
            'data'=>PurchaseUser::getBuyerAndGroup(),
            'pluginOptions' => ['width'=>'130px'],
        ])->label('采购员') ?>
    </div>
    <div class="col-md-2" ><label class="control-label" for="purchaseorderpaysearch-applicant">缺货开始时间：</label><?php
        $addon = <<< HTML
<span class="input-group-addon">
    <i class="glyphicon glyphicon-calendar"></i>
</span>
HTML;
        echo '<div class="input-group drp-container">';
        echo DateRangePicker::widget([
                'name'=>'StockOwesSearch[earlest_outofstock_date]',
                'useWithAddon'=>true,
                'convertFormat'=>true,
                'startAttribute' => 'StockOwesSearch[start_time]',
                'endAttribute' => 'StockOwesSearch[end_time]',
                'startInputOptions' => ['value' => !empty($model->start_time) ? date('Y-m-d',strtotime($model->start_time)) : date('Y-m-d',strtotime("-6 month"))],
                'endInputOptions' => ['value' => !empty($model->end_time) ? date('Y-m-d',strtotime($model->end_time)) : date('Y-m-d',time())],
                'pluginOptions'=>[
                    'locale'=>['format' => 'Y-m-d'],
                ]
            ]).$addon ;
        echo '</div>';
        ?></div>

    <div class="form-group col-md-2"  style="margin-top: 24px;float:left">
        <?= Html::submitButton(Yii::t('app', '搜索'), ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton(Yii::t('app', '重置'), ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
