<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\daterange\DateRangePicker;
use kartik\select2\Select2;
use yii\web\JsExpression;


$url = \yii\helpers\Url::to(['/supplier/search-supplier?q=&id=&status=']);
?>

<div class="purchase-order-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <div class="col-md-2">
        <label class="control-label" for="supplierauditresults-audit_time">审核日期：</label>
        <?php
        $addon = <<< HTML
<span class="input-group-addon">
    <i class="glyphicon glyphicon-calendar"></i>
</span>
HTML;
        echo '<div class="input-group drp-container">';
        echo DateRangePicker::widget([
                 'name'=>'SupplierAuditResults[audit_time]',
                 'useWithAddon'=>true,
                 'convertFormat'=>true,
                 'startAttribute' => 'SupplierAuditResults[start_time]',
                 'endAttribute' => 'SupplierAuditResults[end_time]',
                 'startInputOptions' => ['value' => !empty($model->start_time)?$model->start_time:date('Y-m-d',strtotime('- 6 months'))],
                 'endInputOptions' => ['value' => !empty($model->end_time)?$model->end_time:date('Y-m-d')],
                 'pluginOptions'=>[
                     'locale'=>['format' => 'Y-m-d'],
                 ]
             ]).$addon ;
        echo '</div>';
        ?>
    </div>

    <div class="col-md-1"><?=$form->field($model, 'audit_user')->label('审核人') ?></div>
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
        ])->label('供应商');
        ?>
    </div>
    <div class="col-md-1">
        <?= $form->field($model, 'audit_status')->dropDownList(\app\models\SupplierAuditResults::getStatusList(),['prompt'=>'请选择']) ?>
    </div>
    <div class="form-group col-md-2" style="margin-top: 24px;float:left">
        <?= Html::submitButton('搜索', ['class' => 'btn btn-primary']) ?>
    </div>
    <?php ActiveForm::end(); ?>
</div>
