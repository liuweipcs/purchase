<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\daterange\DateRangePicker;
?>

<?php $form = ActiveForm::begin(['action' => ['index'], 'method' => 'get']); ?>

<div class="col-md-1">
    <?= $form->field($model, 'express_single')->label('快递单号') ?>
</div>

<div class="col-md-1">
    <?= $form->field($model, 'relation_order_no')->label('采购单号') ?>
</div>

<div class="col-md-1">
    <?= $form->field($model, 'status')->dropDownList(['1'=>'正常', '2'=>'删除'], ['prompt' => '请选择'])->label('状态') ?>
</div>

<div style="width: 293px; float: left; position: relative; min-height: 1px; padding-right: 15px;">
    <label class="control-label" for="purchaseorderpaysearch-applicant">签收时间</label>
    <?php
    $addon = <<< HTML
<span class="input-group-addon">
    <i class="glyphicon glyphicon-calendar"></i>
</span>
HTML;
    echo '<div class="input-group drp-container">';
    echo DateRangePicker::widget([
            'name' => 'add_time',
            'value' => $model->add_time,
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


