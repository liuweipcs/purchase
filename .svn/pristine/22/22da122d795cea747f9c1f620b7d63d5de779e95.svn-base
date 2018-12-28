<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
$url = \yii\helpers\Url::to(['/supplier/search-supplier']);
use app\services\BaseServices;
use kartik\select2\Select2;
use yii\web\JsExpression;
/* @var $this yii\web\View */
/* @var $model app\models\PurchaseSuggestSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="purchase-suggest-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>



    <div class="col-md-1"><?= $form->field($model, 'sku') ?></div>

    <div class="col-md-1"><?= $form->field($model, 'name')->label('产品名') ?></div>

    <?php // echo $form->field($model, 'supplier_code') ?>

    <?php // echo $form->field($model, 'supplier_name') ?>

    <div class="col-md-1"><?php echo $form->field($model, 'buyer')->label('采购员') ?></div>
    <div class="col-md-1"> <?=$form->field($model, 'product_category_id')->dropDownList(\app\services\BaseServices::getCategory(),['prompt'=> '请选择产品分类'])->label('产品分类') ?></div>
    <div class="col-md-4">
        <?= $form->field($model, 'supplier_code')->widget(Select2::classname(), [
            'options' => ['placeholder' => '请输入供应商 ...','value' =>!empty($name)?$name:''],
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
        ]);
        ?>
    </div>
    <?php // echo $form->field($model, 'replenish_type') ?>

    <?php // echo $form->field($model, 'category_id') ?>

    <?php // echo $form->field($model, 'qty') ?>

    <?php // echo $form->field($model, 'price') ?>

    <?php // echo $form->field($model, 'ship_method') ?>

    <?php // echo $form->field($model, 'is_purchase') ?>

    <?php // echo $form->field($model, 'created_at') ?>

    <?php // echo $form->field($model, 'creator') ?>

    <?php // echo $form->field($model, 'product_category_id') ?>

    <?php // echo $form->field($model, 'category_cn_name') ?>

    <?php // echo $form->field($model, 'on_way_stock') ?>

    <?php // echo $form->field($model, 'available_stock') ?>

    <?php // echo $form->field($model, 'stock') ?>

    <?php // echo $form->field($model, 'left_stock') ?>

    <?php // echo $form->field($model, 'days_sales_3') ?>

    <?php // echo $form->field($model, 'days_sales_7') ?>

    <?php // echo $form->field($model, 'days_sales_15') ?>

    <?php // echo $form->field($model, 'days_sales_30') ?>

    <div class="form-group col-md-3" style="margin-top: 24px;">
        <?= Html::submitButton('搜索', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('重置', ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
