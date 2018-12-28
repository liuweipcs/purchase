<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\daterange\DateRangePicker;
use app\services\BaseServices;
use kartik\select2\Select2;
use yii\web\JsExpression;
?>

<?php $form = ActiveForm::begin(['action' => ['index'], 'method' => 'get']); ?>

<div class="col-md-1">
    <?= $form->field($model, 'account')->label('账号'); ?>
</div>

<div class="col-md-1">
    <?= $form->field($model, 'level')->dropDownList(['-1' => '全部', '0' => '出纳', '1' => '非出纳'])->label('类型'); ?>
</div>

<div class="col-md-2">
    <?= $form->field($model, 'user')->widget(Select2::classname(), [
        'options' => ['placeholder' => '请选择使用人 ...', 'id' => 'user', 'value' => $model->user],
        'data' => BaseServices::getBuyer(),
        'pluginOptions' => [
            'allowClear' => true,
            'language' => [
                'errorLoading' => new JsExpression("function () { return 'Waiting...'; }"),
            ],
            'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
            'templateResult' => new JsExpression('function(res) { return res.text; }'),
            'templateSelection' => new JsExpression('function (res) { return res.text; }'),
        ],
    ])->label('使用人');
    ?>
</div>

<div class="col-md-2">
    <?= $form->field($model, 'pid')->widget(Select2::classname(), [
        'options' => ['placeholder' => '请选择付款人 ...', 'id' => 'pid', 'value' => $model->pid],
        'data' => $model::getPayer(),
        'pluginOptions' => [
            'allowClear' => true,
            'language' => [
                'errorLoading' => new JsExpression("function () { return 'Waiting...'; }"),
            ],
            'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
            'templateResult' => new JsExpression('function(res) { return res.text; }'),
            'templateSelection' => new JsExpression('function (res) { return res.text; }'),
        ],
    ])->label('付款人');
    ?>
</div>

<div class="form-group col-md-2" style="margin-top: 24px;float:left">
    <?= Html::submitButton('搜索', ['class' => 'btn btn-primary']) ?>
    <?= Html::a('重置', ['index'], ['class' => 'btn btn-default']) ?>
</div>

<?php ActiveForm::end(); ?>


