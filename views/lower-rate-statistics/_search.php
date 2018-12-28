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

<div class="lower-rate-statistics-search">
    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]);?>
    <div class="col-md-1">
        <?= $form->field($model, 'buyer_id')->widget(Select2::classname(), [
            'options' => ['placeholder' => '请选择'],
            'data'=>\app\models\PurchaseUser::getBuyerAndGroup(),
            'pluginOptions' => ['width'=>'140px',
                'allowClear' => true,
            ],
        ])->label('采购员') ?>
    </div>
    <div class="col-md-3" ><label class="control-label" for="purchaseorderpaysearch-applicant">创建时间</label><?php
        $addon = <<< HTML
<span class="input-group-addon">
    <i class="glyphicon glyphicon-calendar"></i>
</span>
HTML;
        echo '<div class="input-group drp-container">';
        echo DateRangePicker::widget([
                'name'=>'LowerRateStatisticsSearch[create_time]',
                'useWithAddon'=>true,
                'convertFormat'=>true,
                'startAttribute' => 'LowerRateStatisticsSearch[start_time]',
                'endAttribute' => 'LowerRateStatisticsSearch[end_time]',
//                'startInputOptions' => ['value' => !empty($model->start_time) ? $model->start_time : date('Y-m-d H:i:s',1496246400)],
                'startInputOptions' => ['value' => !empty($model->start_time) ? $model->start_time : date('Y-m-d 00:00:00',time()-86400)],
//                'endInputOptions' => ['value' => !empty($model->end_time) ? $model->end_time :''],
                'endInputOptions' => ['value' => !empty($model->end_time) ? $model->end_time :date('Y-m-d 23:59:59',time()-86400)],
                'pluginOptions'=>[
                    'locale'=>['format' => 'Y-m-d'],
                ]
            ]).$addon ;
        echo '</div>';
        ?></div>

    <div class="form-group col-md-2" style="margin-top: 24px;">
        <?= Html::submitButton('搜索', ['class' => 'btn btn-primary']) ?>
        <a class="btn btn-default" href="index">重置</a>
        &nbsp;&nbsp;<?= Html::button('导出Excel',['class' => 'btn btn-success','id'=>'export-csv']) ?>
    </div>
    <?php ActiveForm::end(); ?>
</div>
