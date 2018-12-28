<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\daterange\DateRangePicker;
use app\services\BaseServices;
use kartik\select2\Select2;
use yii\web\JsExpression;
/* @var $this yii\web\View */
/* @var $model app\models\TodayListSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="purchase-order-search">
    <?php $form = ActiveForm::begin([
        'action' => ['list'],
        'method' => 'get',
    ]);?>

    <div class="col-md-2"><?= $form->field($model, 'group_id')->widget(Select2::classname(), [
            'options' => ['placeholder' => '请输入亚马逊分组...','id'=>'status'],
            'data' =>BaseServices::getAmazonGroup(),
            'pluginOptions' => [
                'multiple' => true,
                'allowClear' => true,
                'language' => [
                    'errorLoading' => new JsExpression("function () { return 'Waiting...'; }"),
                ],
                'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
                'templateResult' => new JsExpression('function(res) {return res.text; }'),
                'templateSelection' => new JsExpression('function (res) {return res.text; }'),
            ],
        ])->label('亚马逊分组');
        ?>
    </div>
    <div class="col-md-1"><?= $form->field($model, 'dimension')->dropDownList(['1'=>'全部','2'=>'销售维度','3'=>'分组维度'],['value'=>$model->dimension])->label('数据维度')?></div>
    <div class="col-md-3" ><label class="control-label" for="purchaseorderpaysearch-applicant">需求审核时间</label><?php
        $addon = <<< HTML
<span class="input-group-addon">
    <i class="glyphicon glyphicon-calendar"></i>
</span>
HTML;
        echo '<div class="input-group drp-container">';
        echo DateRangePicker::widget([
                'name'=>'PlatformSummarySearch[create_time]',
                'useWithAddon'=>true,
                'convertFormat'=>true,
                'startAttribute' => 'PlatformSummarySearch[start_time]',
                'endAttribute' => 'PlatformSummarySearch[end_time]',
                'startInputOptions' => ['value' => !empty($model->start_time) ? $model->start_time : date('Y-m-d H:i:s',1496246400)],
                'endInputOptions' => ['value' => !empty($model->end_time) ? $model->end_time :date('Y-m-d H:i:s',time())],
                'pluginOptions'=>[
                    'locale'=>['format' => 'Y-m-d H:i:s'],
                ]
            ]).$addon ;
        echo '</div>';
        ?></div>

    <div class="form-group col-md-2" style="margin-top: 24px;">
        <?= Html::submitButton('搜索', ['class' => 'btn btn-primary']) ?>
        <a class="btn btn-default" href="index">重置</a>
    </div>
    <?php ActiveForm::end(); ?>
</div>
