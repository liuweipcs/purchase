<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;
use yii\web\JsExpression;
use app\services\BaseServices;

/* @var $this yii\web\View */
/* @var $model app\models\StockinSearch */
/* @var $form yii\widgets\ActiveForm */
?>
<style>
    .col-md-1{
        padding-left: 0px;
    }
</style>
<div class="stockin-search">
    <?php
    $url = \yii\helpers\Url::to(['/supplier/search-supplier']);
    $supplier_code = \app\models\Supplier::find()->where(['supplier_code'=>$model->getAttribute('quotes.suppliercode')])->one();
    ?>

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>
<!--    <div class="col-md-2"><?/*= $form->field($model, 'product_category_id')->widget(Select2::classname(), [
            'options' => ['placeholder' => '请输入产品分类 ...'],
            'data' =>BaseServices::getCategory(),
            'pluginOptions' => [

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
        ])*/?></div>-->
    <div class="col-md-2"><?= $form->field($model, 'product_line')->widget(Select2::classname(), [
            'options' => ['placeholder' => '请输入产品线 ...'],
            'data' =>BaseServices::getProductLine(),
            'pluginOptions' => [
                'allowClear' => true,
                'language' => [
                    'errorLoading' => new JsExpression("function () { return 'Waiting...'; }"),
                ],
                /*'ajax' => [
                    'url' => $url,
                    'dataType' => 'json',
                    'data' => new JsExpression('function(params) { return {q:params.term}; }')
                ],*/
                'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
                'templateResult' => new JsExpression('function(res) { return res.text; }'),
                'templateSelection' => new JsExpression('function (res) { return res.text; }'),
            ],
        ])->label('产品线')?></div>
    <div class="col-md-1"><?= $form->field($model, 'sku')->textInput(['placeholder'=>'支持模糊搜索']) ?></div>
    <div class="col-md-1"><?= $form->field($model, 'product_name')->textInput(['placeholder'=>'支持模糊搜索'])->label('产品名称') ?></div>
    <div class="col-md-1"><?=$form->field($model, "product_status")->dropDownList(\app\services\SupplierGoodsServices::getProductStatus(),['class' => 'form-control','prompt' => '请选择'])?></div>
<!--    <div class="col-md-1">--><?php $form->field($model, 'quotes.default_buyer')->dropDownList(\app\services\BaseServices::getEveryOne(), ['prompt' => '请选择'])->label('默认采购员') ?><!--</div>-->
    <div class="col-md-2">
    <?= $form->field($model, 'quotes.suppliercode')->widget(Select2::classname(), [
        'options' => ['placeholder' => '请输入供应商 ...'],
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
    ])->label('供货商');
    ?>
    </div>
    <div class="col-md-1">
        <?=$form->field($model,'sourcing_status')->dropDownList(\app\services\SupplierGoodsServices::getProductSourceStatus(),['prompt'=>'请选择'])->label('货源状态')?>
    </div>
    <div class="col-md-1">
        <?= $form->field($model, 'supplier_special_flag')->dropDownList(\app\services\SupplierServices::supplierSpecialFlag(),['prompt'=>'请选择'])->label('跨境宝供应商') ?>
    </div>

    <div class="form-group col-md-2" style="margin-top: 24px;">
        <?= Html::submitButton(Yii::t('app', '搜索'), ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton(Yii::t('app', '重置'), ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
