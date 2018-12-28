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
    <?= $form->field($model, 'sku') ?>
</div>

<div class="col-md-1">
    <?= $form->field($model, 'defective_id') ?>
</div>

<div class="col-md-1">
    <?= $form->field($model, 'purchase_order_no') ?>
</div>

<div class="col-md-1">
    <?= $form->field($model, 'express_code') ?>
</div>

<div class="col-md-1">
    <?= $form->field($model, 'is_handler')->dropDownList(['0' => '未处理', '1' => '已处理', '2' => '处理错误'], ['prompt' => '请选择']) ?>
</div>
    <div class="col-md-1">
        <?php

        $types = [
            '2'=>'次品退货',
            '3'=>'次品转正',
            '5'=>'不做处理',
            '15'=> '处理中'
        ];

        echo $form->field($model, 'handler_type')->dropdownList($types,['prompt' => '请选择', 'value' => $model->handler_type]);

        ?>
    </div>

<div class="col-md-1">
    <?= $form->field($model, 'buyer')->widget(Select2::classname(), [
        'options' => ['placeholder' => '请输入采购员 ...', 'id'=>'buyer', 'value' => $model->buyer],
        'data' =>BaseServices::getBuyer('name'),
        'pluginOptions' => [
            'allowClear' => true,
            'language' => [
                'errorLoading' => new JsExpression("function () { return 'Waiting...'; }"),
            ],
            'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
            'templateResult' => new JsExpression('function(res) { return res.text; }'),
            'templateSelection' => new JsExpression('function (res) { return res.text; }'),
        ],
    ])->label('采购员');
    ?>
</div>

<div class="col-md-3">
    <label class="control-label" for="purchaseorderpaysearch-applicant">拉取时间</label>
    <?php
    $addon = <<< HTML
<span class="input-group-addon">
    <i class="glyphicon glyphicon-calendar"></i>
</span>
HTML;
    echo '<div class="input-group drp-container">';
    echo DateRangePicker::widget([
            'name' => 'pull_time',
            'value' => $model->pull_time,
            'useWithAddon'=> false,
            'convertFormat'=> true,
            'initRangeExpr' => true,
            'startAttribute' => 'start_time',
            'endAttribute' => 'end_time',
            'startInputOptions' => ['value' => !empty($model->start_time) ? $model->start_time : date('Y-m-d H:i',strtotime("last month"))],
            'endInputOptions' => ['value' => !empty($model->end_time) ? $model->end_time : date('Y-m-d 23:59', time())],
            'pluginOptions' => [
                'locale' => ['format' => 'Y-m-d H:i'],
                'ranges' => [
                    '今天' => ["moment().startOf('day')", "moment()"],
                    '昨天' => ["moment().startOf('day').subtract(1,'days')", "moment().endOf('day').subtract(1,'days')"],
                    '最近7天' => ["moment().startOf('day').subtract(6, 'days')", "moment()"],
                    '最近30天' => ["moment().startOf('day').subtract(29, 'days')", "moment()"],
                    '本月' => ["moment().startOf('month')", "moment().endOf('month')"],
                    '上月' => ["moment().subtract(1, 'month').startOf('month')", "moment().subtract(1, 'month').endOf('month')"],
                ]
            ],
        ]).$addon;
    echo '</div>';
    ?>
</div>

<div class="form-group col-md-2" style="margin-top: 24px;float:left">
    <?= Html::submitButton('搜索', ['class' => 'btn btn-primary']) ?>
    <?= Html::a('重置', ['index'], ['class' => 'btn btn-default']) ?>
</div>

<?php ActiveForm::end(); ?>