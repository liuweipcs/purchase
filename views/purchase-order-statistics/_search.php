<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\services\BaseServices;
use kartik\select2\Select2;
use yii\web\JsExpression;
use kartik\daterange\DateRangePicker;
use app\models\PurchaseOrderSearch;

$url = \yii\helpers\Url::to(['/supplier/search-supplier']);

?>

<div class="purchase-suggest-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>


    <div class="col-md-1"> <?=$form->field($model, 'purchase_type')->dropDownList(['1' => '国内仓采购', '2' => '海外仓采购', '3' => 'FBA采购'])->label('用户类型') ?></div>

    <div class="col-md-1">
        <?= $form->field($model, 'supplier_name')->textInput(['placeholder' => '代码或全称'])->label('供应商名称') ?>
    </div>

    <div class="col-md-2">
        <?= $form->field($model, 'sku',[
            'inputOptions' => [
                'placeholder' => '多个用空格隔开',
                'class' => 'form-control',
            ],
        ])->label('SKU') ?>
    </div>

    <div class="col-md-3">
        <label class="control-label" for="purchaseorderpaysearch-applicant">审核时间</label>
        <?php
        $addon = <<< HTML
<span class="input-group-addon">
    <i class="glyphicon glyphicon-calendar"></i>
</span>
HTML;
        echo '<div class="input-group drp-container">';
        echo DateRangePicker::widget([
                'name' => 'PurchaseOrderSearch[audit_time]',
               // 'value' => $model->application_time,
                'useWithAddon'   => false,
                'convertFormat'  => true,
                'initRangeExpr'  => true,
                'startAttribute' => 'PurchaseOrderSearch[start_time]',
                'endAttribute'   => 'PurchaseOrderSearch[end_time]',
                'startInputOptions' => ['value' => !empty($model->start_time) ? $model->start_time : date('Y-m-d 00:00:00',strtotime("-0 year -6 month -0 day"))],
                'endInputOptions' => ['value' => !empty($model->end_time) ? $model->end_time : date('Y-m-d 23:59:59', strtotime("-1 day"))],
                'pluginOptions' => [
                    'locale' => ['format' => 'Y-m-d H:i:s'],
                    'ranges' => [
                        '最近7天' => ["moment().startOf('day').subtract(6, 'days')", "moment()"],
                        '最近15天' => ["moment().startOf('day').subtract(15, 'days')", "moment()"],
                        '最近30天' => ["moment().startOf('day').subtract(29, 'days')", "moment()"],
                    ]
                ],
            ]).$addon;
        echo '</div>';
        ?>
    </div>

    <div class="form-group col-md-2" style="margin-top: 24px;float:left">
        <?= Html::submitButton('搜索', ['class' => 'btn btn-primary']) ?>
        <?= Html::a('重置', ['index'],['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
