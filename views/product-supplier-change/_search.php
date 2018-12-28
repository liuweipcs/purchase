<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\daterange\DateRangePicker;
use app\services\BaseServices;
use app\services\SupplierServices;
use app\services\PurchaseOrderServices;
use kartik\select2\Select2;
use yii\web\JsExpression;

use app\models\ProductSupplierChangeSearch;

$url = \yii\helpers\Url::to(['/supplier/search-supplier']);
?>

<div class="purchase-order-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <div class="col-md-1"><?=$form->field($model, 'sku')->label('SKU') ?></div>
    <div class="col-md-2">
        <?= $form->field($model, 'supplier_code')->widget(Select2::classname(), [
            'options' => ['placeholder' => '请输入供应商 ...'],
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
        ])->label('供货商');
        ?>
    </div>
    <div class="col-md-1" style="width: 200px;">
        <?= $form->field($model, 'status')->dropDownList(ProductSupplierChangeSearch::getStatusList(),['prompt'=>'请选择'])->label('状态') ?>
    </div>
    <div class="col-md-2" ><label class="control-label" for="purchaseorderpaysearch-applicant">申请时间：</label><?php
        $addon = <<< HTML
<span class="input-group-addon">
    <i class="glyphicon glyphicon-calendar"></i>
</span>
HTML;
        echo '<div class="input-group drp-container">';
        echo DateRangePicker::widget([
                 'name' => 'ProductSupplierChangeSearch[apply_time]',
                 'attribute' => 'apply_time',
                 'value' => $model->apply_time,
                 'useWithAddon' => true,
                 'convertFormat' => true,
                 'pluginOptions' => [
                     'locale' => ['format' => 'Y-m-d'],
                 ]
             ]) . $addon;
        echo '</div>';
        ?></div>
    <div class="col-md-2" ><label class="control-label" for="purchaseorderpaysearch-applicant">审核时间：</label><?php
        $addon = <<< HTML
<span class="input-group-addon">
    <i class="glyphicon glyphicon-calendar"></i>
</span>
HTML;
        echo '<div class="input-group drp-container">';
        echo DateRangePicker::widget([
                 'name' => 'ProductSupplierChangeSearch[erp_oper_time]',
                 'attribute' => 'erp_oper_time',
                 'value' => $model->erp_oper_time,
                 'useWithAddon' => true,
                 'convertFormat' => true,
                 'pluginOptions' => [
                     'locale' => ['format' => 'Y-m-d'],
                 ]
             ]) . $addon;
        echo '</div>';
        ?></div>
    <div class="col-md-1"><?=$form->field($model, 'apply_user')->label() ?></div>
    <div class="col-md-1"><?=$form->field($model, 'create_id')->label() ?></div>
    <div class="form-group col-md-1" style="margin-top: 24px;float:left">
        <?= Html::submitButton('搜索', ['class' => 'btn btn-primary']) ?>
    </div>
    <?php ActiveForm::end(); ?>
</div>
