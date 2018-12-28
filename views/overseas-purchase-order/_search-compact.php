<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\services\BaseServices;
use kartik\daterange\DateRangePicker;
use kartik\select2\Select2;
use yii\web\JsExpression;
$url = \yii\helpers\Url::to(['/supplier/search-supplier']);
?>
<?php $form = ActiveForm::begin([
    'action' => ['compact-list'],
    'method' => 'get',
]); ?>
<div class="col-md-1">
    <?= $form->field($model,'compact_number') ?>
</div>

<div class="col-md-2">
    <?= $form->field($model, 'create_person_name')->widget(Select2::classname(), [
        'options' => ['placeholder' => '请输入创建人 ...', 'id' => 'create_person_name', 'value' => $model->create_person_name],
        'data' => BaseServices::getBuyer('name'),
        'pluginOptions' => [
            'allowClear' => true,
            'language' => [
                'errorLoading' => new JsExpression("function () { return 'Waiting...'; }"),
            ],
            'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
            'templateResult' => new JsExpression('function(res) { return res.text; }'),
            'templateSelection' => new JsExpression('function (res) { return res.text; }'),
        ],
    ])->label('创建人');
    ?>
</div>

<div class="col-md-2">
    <?= $form->field($model, 'supplier_code')->widget(Select2::classname(), [
        'options' => ['placeholder' => '请输入供应商 ...', 'value' => $model->supplier_code],
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

<div class="col-md-3">
    <label class="control-label">创建时间</label>
    <?php
    $addon = <<< HTML
<span class="input-group-addon">
    <i class="glyphicon glyphicon-calendar"></i>
</span>
HTML;
    echo '<div class="input-group drp-container">';
    echo DateRangePicker::widget([
            'name' => 'create_time',
            'value' => $model->create_time,
            'useWithAddon'=> false,
            'convertFormat'=> true,
            'initRangeExpr' => true,
            'startAttribute' => 'start_time',
            'endAttribute' => 'end_time',
            'startInputOptions' => ['value' => !empty($model->start_time) ? $model->start_time : date('Y-m-d H:i',strtotime("last month"))],
            'endInputOptions' => ['value' => !empty($model->end_time) ? $model->end_time : date('Y-m-d 23:59', time())],
            'pluginOptions' => [
                'locale' => ['format' => 'Y-m-d H:i']
            ],
        ]).$addon;
    echo '</div>';
    ?>
</div>

<div class="form-group col-md-2" style="margin-top: 24px;">
    <?= Html::submitButton(Yii::t('app', '搜索'), ['class' => 'btn btn-primary']) ?>
    <?= Html::a('重置', ['compact-list'], ['class' => 'btn btn-default']) ?>
</div>
<?php ActiveForm::end(); ?>

