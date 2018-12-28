<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
$url = \yii\helpers\Url::to(['/supplier/search-supplier']);
use app\services\BaseServices;
use kartik\select2\Select2;
use yii\web\JsExpression;
use kartik\daterange\DateRangePicker;
use app\models\PurchaseOrderSearch;
/* @var $this yii\web\View */
/* @var $model app\models\PurchaseSuggestSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="purchase-suggest-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index?source=2'],
        'method' => 'get',
    ]); ?>



    <?php // echo $form->field($model, 'supplier_code') ?>

    <?php // echo $form->field($model, 'supplier_name') ?>


    <div class="col-md-1"> <?=$form->field($model, 'buyer')->dropDownList(PurchaseOrderSearch::getBuyer('','name'),['prompt'=> '请选择采购员'])->label('采购员') ?></div>

    <div class="col-md-3">
        <label class="control-label" for="purchaseorderpaysearch-applicant">完成率时间</label>
        <?php
        $addon = <<< HTML
<span class="input-group-addon">
    <i class="glyphicon glyphicon-calendar"></i>
</span>
HTML;
        echo '<div class="input-group drp-container">';
        echo DateRangePicker::widget([
                'name' => 'application_time',
               // 'value' => $model->application_time,
                'useWithAddon'   => false,
                'convertFormat'  => true,
                'initRangeExpr'  => true,
                'startAttribute' => 'start_time',
                'endAttribute'   => 'end_time',
                'startInputOptions' => ['value' => !empty($model->start_time) ? $model->start_time : date('Y-m-d 00:00:00',strtotime("-1 day"))],
                'endInputOptions' => ['value' => !empty($model->end_time) ? $model->end_time : date('Y-m-d 23:59:59', strtotime("-1 day"))],
                'pluginOptions' => [
                    'locale' => ['format' => 'Y-m-d H:i:s'],
                    'ranges' => [
                        '最近7天' => ["moment().startOf('day').subtract(6, 'days')", "moment()"],
                        '最近15天' => ["moment().startOf('day').subtract(14, 'days')", "moment()"],
                        '最近30天' => ["moment().startOf('day').subtract(29, 'days')", "moment()"],
                    ]
                ],
            ]).$addon;
        echo '</div>';
        ?>
    </div>

    <div class="form-group col-md-2" style="margin-top: 24px;float:left">
        <?= Html::submitButton('搜索', ['class' => 'btn btn-primary']) ?>
        <?= Html::a('重置', ['index?source=2'],['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
