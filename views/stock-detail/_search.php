<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\services\BaseServices;
use kartik\select2\Select2;
use yii\web\JsExpression;
use kartik\daterange\DateRangePicker;
?>

<div class="purchase-order-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>
    <div class="col-md-3" id="warehouse_category">
        <?= $form->field($model, 'warehouse_category_id')->widget(Select2::classname(), [
            'options' => ['placeholder' => '请选大库 ...','id'=>'warehouse_category_id'],
            'data'=>\app\models\LargeWarehouse::getWarehouseCode(),
            'pluginOptions' => [
                'multiple' => true,
                'allowClear' => true,
                'language' => [
                    'errorLoading' => new JsExpression("function () { return 'Waiting...'; }"),
                ],
                'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
                'templateResult' => new JsExpression('function(res) { return res.text; }'),
                'templateSelection' => new JsExpression('function (res) { return res.text; }'),
                'style' => 'width: 448px;background: transparent;padding: 0 12px;height: 32px;line-height: 1.428571429;margin-top: 0;min-width: 5em;box-sizing: border-box;border: none;font-size: 100%;'
            ],
        ])->label('大仓');
        ?>
    </div>

    <div class="col-md-3" id="warehouse">
        <?= $form->field($model, 'warehouse_code')->widget(Select2::classname(), [
            'options' => ['placeholder' => '请选仓库 ...','id'=>'warehouse_code'],
            'data'=>BaseServices::getWarehouseCode(),
            'pluginOptions' => [
                'multiple' => true,
                'allowClear' => true,
                'language' => [
                    'errorLoading' => new JsExpression("function () { return 'Waiting...'; }"),
                ],
                'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
                'templateResult' => new JsExpression('function(res) { return res.text; }'),
                'templateSelection' => new JsExpression('function (res) { return res.text; }'),
                'style' => 'width: 448px;background: transparent;padding: 0 12px;height: 32px;line-height: 1.428571429;margin-top: 0;min-width: 5em;box-sizing: border-box;border: none;font-size: 100%;'
            ],
        ])->label('仓库');
        ?>
    </div>

    <div class="col-md-2" ><label class="control-label" for="purchaseorderpaysearch-applicant">日期：</label><?php
        $addon = <<< HTML
<span class="input-group-addon">
    <i class="glyphicon glyphicon-calendar"></i>
</span>
HTML;
        echo '<div class="input-group drp-container">';
        echo DateRangePicker::widget([
                'name'=>'PurchaseOrderSearch[time]',
                'useWithAddon'=>true,
                'convertFormat'=>true,
                'startAttribute' => 'PurchaseOrderSearch[start_time]',
                'endAttribute' => 'PurchaseOrderSearch[end_time]',
//            'startInputOptions' => ['value' => date('Y-m-d H:i:s',strtotime("-6 month"))],
//            'endInputOptions' => ['value' => date('Y-m-d H:i:s',time())],
//            'startInputOptions' => ['value' => !empty($model->start_time) ? $model->start_time : date('Y-m-d',strtotime("-6 month"))],
                'startInputOptions' => ['value' => !empty($model->start_time) ? date('Y-m-d',strtotime($model->start_time)) : date('Y-m-d',time())],
                'endInputOptions' => ['value' => !empty($model->end_time) ? date('Y-m-d',strtotime($model->end_time)) : date('Y-m-d',time())],
                'pluginOptions'=>[
                    'locale'=>['format' => 'Y-m-d'],
                ]
            ]).$addon ;
        echo '</div>';
        ?></div>


    <div class="form-group col-md-2" style="margin-top: 24px;">
        <?= Html::submitButton('搜索', ['class' => 'btn btn-primary','style' => 'color: #fff;background-color: #337ab7;border-color: #2e6da4;display: inline-block;
            padding: 6px 12px;margin-bottom: 0;font-size: 14px;font-weight: normal;line-height: 1.42857143;text-align: center;white-space: nowrap;vertical-align: middle;
            -ms-touch-action: manipulation;touch-action: manipulation;cursor: pointer;-webkit-user-select: none;-moz-user-select: none;-ms-user-select: none;user-select: none;
            background-image: none;border: 1px solid transparent;border-radius: 4px;']) ?>
        <?= Html::a('重置', ['index'],['class' => 'btn btn-default','style' => 'color: #333;background-color: #fff;border-color: #ccc;display: inline-block;padding: 6px 12px;
            margin-bottom: 0;font-size: 14px;font-weight: normal;line-height: 1.42857143;text-align: center;white-space: nowrap;touch-action: manipulation;cursor: pointer;user-select: none;
            background-image: none;border-radius: 4px;']) ?>
        <?= Html::button('导出Excel',['class' => 'btn btn-success','id'=>'export-csv','style' => 'color:#fff;background-color: #5cb85c;border-color: #4cae4c;display: inline-block;
            padding: 6px 12px;margin-bottom: 0;font-size: 14px;font-weight: normal;line-height: 1.42857143;text-align: center;white-space: nowrap;vertical-align: middle;
            -ms-touch-action: manipulation;touch-action: manipulation;cursor: pointer;-webkit-user-select: none;-moz-user-select: none;-ms-user-select: none;user-select: none;
            background-image: none;border: 1px solid transparent;border-radius: 4px;']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>