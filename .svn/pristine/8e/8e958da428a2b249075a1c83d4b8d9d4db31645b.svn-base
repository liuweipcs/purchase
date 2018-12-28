<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;
use app\services\BaseServices;
use yii\web\JsExpression;

/* @var $this yii\web\View */
/* @var $model app\models\OverseasWarehouseGoodsTaxRebateSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="overseas-warehouse-goods-tax-rebate-search purchase-order-search">
    <?php $form = ActiveForm::begin([
        'action' => ['test'],
        'method' => 'get',
    ]); ?>

    <div class="col-md-1">
        <?= $form->field($model, 'sku') ?>
    </div>
    <div class="col-md-3">
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
            ],
        ])->label('仓库');
        ?>
    </div>
    <div class="form-group col-md-2" style="margin-top: 24px;float:left">
        <?= Html::submitButton('搜索', ['class' => 'btn btn-primary']) ?>
        <?= Html::a('重置', ['index'],['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
