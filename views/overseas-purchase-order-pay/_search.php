<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\services\PurchaseOrderServices;
use app\services\BaseServices;
use app\services\SupplierServices;
use kartik\daterange\DateRangePicker;
use kartik\select2\Select2;
use yii\web\JsExpression;
$url = \yii\helpers\Url::to(['/supplier/search-supplier']);
?>
<?php
    $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]);
?>

<?= Html::input('hidden', 'source', $model->source); ?>

<div class="col-md-3">
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
    <?= $form->field($model,'pay_status')->dropDownList(PurchaseOrderServices::getPayStatus(),['prompt' => '请选择']) ?>
</div>
<div class="col-md-1">
    <?= $form->field($model,'pur_number') ?>
</div>
<div class="col-md-1">
    <?= $form->field($model, 'requisition_number') ?>
</div>
<div class="col-md-1">
    <?= $form->field($model, 'settlement_method')->dropDownList(SupplierServices::getSettlementMethod(), ['prompt' => '请选择']) ?>
</div>
<div class="col-md-1">
    <?= $form->field($model, 'pay_type')->dropDownList(SupplierServices::getDefaultPaymentMethod(), ['prompt' => '请选择']) ?>
</div>


<div class="col-md-2">
    <?= $form->field($model, 'applicant')->widget(Select2::classname(), [
        'options' => ['placeholder' => '请输入采购员...'],
        'data'    => BaseServices::getEveryOne(),
        'pluginOptions' => [
            'language'  => [
                'errorLoading'  => new JsExpression("function () { return 'Waiting...'; }"),
            ],
            'escapeMarkup'      => new JsExpression('function (markup) { return markup; }'),
            'templateResult'    => new JsExpression('function(res) { return res.text; }'),
            'templateSelection' => new JsExpression('function (res) { return res.text; }'),
        ],
    ]);
    ?>
</div>

<div class="clearfix"></div>

<div class="col-md-3">
    <label class="control-label">申请时间</label>
    <?php
    $addon = <<< HTML
<span class="input-group-addon">
    <i class="glyphicon glyphicon-calendar"></i>
</span>
HTML;
    echo '<div class="input-group drp-container">';
    echo DateRangePicker::widget([
            'name' => 'application_time',
            'value' => $model->application_time,
            'useWithAddon'   => true,
            'convertFormat'  => true,
            'initRangeExpr'  => true,
            'startAttribute' => 'start_time',
            'endAttribute'   => 'end_time',
            'startInputOptions' => ['value' => !empty($model->start_time) ? $model->start_time : date('Y-m-d H:i:s', strtotime("last month"))],
            'endInputOptions' => ['value' => !empty($model->end_time) ? $model->end_time : date('Y-m-d 23:59:s', time())],
            'pluginOptions' => [
                'locale' => ['format' => 'Y-m-d H:i:s'],
                'ranges' => [
                    Yii::t('app', "今天") => ["moment().startOf('day')", "moment()"],
                    Yii::t('app', "昨天") => ["moment().startOf('day').subtract(1,'days')", "moment().endOf('day').subtract(1,'days')"],
                    Yii::t('app', "最近7天") => ["moment().startOf('day').subtract(6, 'days')", "moment()"],
                    Yii::t('app', "最近30天") => ["moment().startOf('day').subtract(29, 'days')", "moment()"],
                    Yii::t('app', "本月") => ["moment().startOf('month')", "moment().endOf('month')"],
                    Yii::t('app', "上月") => ["moment().subtract(1, 'month').startOf('month')", "moment().subtract(1, 'month').endOf('month')"],
                ]
            ],
        ]).$addon;
    echo '</div>';
    ?>
</div>

<div class="form-group col-md-2" style="margin-top: 24px;">
    <?= Html::submitButton(Yii::t('app', '搜索'), ['class' => 'btn btn-primary']) ?>
    <?= Html::a('重置', ['index', 'source' => $model->source], ['class' => 'btn btn-default']) ?>
</div>
<?php ActiveForm::end(); ?>

