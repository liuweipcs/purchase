<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\services\BaseServices;
use kartik\select2\Select2;
use kartik\daterange\DateRangePicker;
use yii\web\JsExpression;
$url = \yii\helpers\Url::to(['/supplier/search-supplier']);
?>

<?php $form = ActiveForm::begin(['action' => ['index'], 'method' => 'get']); ?>

<?= Html::input('hidden', 'source', $model->source) ?>

<div class="col-md-1">
    <?= $form->field($model, 'compact_number')->label('合同号') ?>
</div>

<div class="col-md-1"><?= $form->field($model, 'items.sku')->label('sku') ?></div>
<div class="col-md-1"><?= $form->field($model, 'pur_number') ?></div>

<div class="col-md-2">
    <?= $form->field($model, 'supplier_code')->widget(Select2::classname(), [
        'options' => ['placeholder' => '请输入供应商 ...', 'value' => !empty($model->supplier_code) ? $model->supplier_code : ''],
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
    <?= $form->field($model, 'buyer')->widget(Select2::classname(), [
        'options' => ['placeholder' => '请输入采购员 ...','value' => !empty($model->buyer) ? $model->buyer : ''],
        'data' => BaseServices::getEveryOne('','name'),
        'pluginOptions' => [
            'placeholder' => 'search ...',
            'allowClear' => true,
            'language' => [
                'errorLoading' => new JsExpression("function () { return 'Waiting...'; }"),
            ],
           /* 'ajax' => [
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

<div class="col-md-2">
    <label class="control-label" for="purchaseorderpaysearch-applicant">创建时间</label>
    <?php
    $addon = <<< HTML
<span class="input-group-addon">
    <i class="glyphicon glyphicon-calendar"></i>
</span>
HTML;
    echo '<div class="input-group drp-container">';
    echo DateRangePicker::widget([
            'name' => 'PurchaseOrderSearch[create_time]',
            'useWithAddon' => true,
            'convertFormat' => true,
            'startAttribute' => 'PurchaseOrderSearch[start_time]',
            'endAttribute' => 'PurchaseOrderSearch[end_time]',
            'startInputOptions' => ['value' => date('Y-m-d H:i:s', strtotime("last month"))],
            'endInputOptions' => ['value' => date('Y-m-d H:i:s', time())],
            'pluginOptions' => [
                'locale' => ['format' => 'Y-m-d H:i:s'],
            ]
        ]).$addon ;
    echo '</div>';
    ?>
</div>

<div class="col-md-2"><?= $form->field($model, 'warehouse_code')->widget(Select2::classname(), [
        'options' => ['placeholder' => '请选仓库'],
        'data' =>BaseServices::getWarehouseCode(),
        'pluginOptions' => [],
    ])?>
</div>

<div  class="form-group col-md-2" style="margin-top: 24px;">
    <?= Html::submitButton('搜索', ['class' => 'btn btn-primary']) ?>
    <?= Html::a('重置', ['index', 'source' => $model->source], ['class' => 'btn btn-default']) ?>
</div>

<?php ActiveForm::end(); ?>


