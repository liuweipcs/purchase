<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\daterange\DateRangePicker;
use app\services\BaseServices;
use app\services\SupplierServices;
use app\services\PurchaseOrderServices;
use kartik\select2\Select2;
use yii\web\JsExpression;

?>
<div class="purchase-order-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= Html::input('hidden', 'PurchaseTacticsSearch[tactics_type]', $model->tactics_type) ?>
    <div class="col-md-2"><?= $form->field($model, 'warehouse_code')->widget(\kartik\select2\Select2::classname(), [
            'options' => ['placeholder' => '请选仓库 ...'],
            'data' =>\app\services\BaseServices::getWarehouseCode(),
            'pluginOptions' => [
                'allowClear' => true,
                'language' => [
                    'errorLoading' => new JsExpression("function () { return 'Waiting...'; }"),
                ],
                'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
                'templateResult' => new JsExpression('function(res) { return res.text; }'),
                'templateSelection' => new JsExpression('function (res) { return res.text; }'),
            ],
        ])->label('仓库')?>
    </div>
    <?php if($model->tactics_type == 2){ ?>
    <div class="col-md-1"><?=$form->field($model, 'sku')->label('SKU') ?></div>
    <div class="col-md-1">
        <?= $form->field($model, 'type')->dropDownList(['1' => '定期备货','2' => '定量备货','3' => '最大最小值备货'],['prompt'=>'请选择'])->label('备货逻辑') ?>
    </div>
    <?php } ?>
    <div class="col-md-1">
        <?= $form->field($model, 'status')->dropDownList(['1' => '启用','2' => '禁用'],['prompt'=>'请选择'])->label('是否启用') ?>
    </div>

    <div class="form-group col-md-2" style="margin-top: 24px;float:left">
        <?= Html::submitButton('搜索', ['class' => 'btn btn-primary']) ?>
    </div>
    <?php ActiveForm::end(); ?>
</div>
