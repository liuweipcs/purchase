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


    <div class="col-md-1"><?= $form->field($model, 'warehouse_code')->label('仓库编码') ?></div>

    <div class="col-md-1"><?= $form->field($model, 'warehouse_name')->label('仓库名称') ?></div>

    <div class="col-md-1"><?= $form->field($model, 'sku')->label('Sku') ?></div>

    <div class="col-md-1"><?= $form->field($model, 'name')->label('产品名称') ?></div>
    <div class="col-md-1"> <?=$form->field($model, 'product_category_id')->dropDownList(\app\services\BaseServices::getCategory(),['prompt'=> '请选择产品分类'])->label('产品分类') ?></div>
    <div class="col-md-2">
        <?= $form->field($model, 'supplier_code')->widget(Select2::classname(), [
            'options' => ['placeholder' => '请输入供应商 ...',],
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
        ])->label('供应商');
        ?>
    </div>
    <div class="col-md-1"><?=$form->field($model, "product_is_new")->dropDownList([''=>'全部',1=>'是',0=>'否'])->label('是否为新品')?></div>
    <div class="col-md-1"><?= $form->field($model, 'buyer')->widget(Select2::classname(), [
            'options' => ['placeholder' => '请输入采购员 ...'],
            'data' =>BaseServices::getEveryOne('','name'),
            'pluginOptions' => [
                'allowClear'=>'true',
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
        ])->label('采购员');
        ?>
    </div>
    <div class="form-group col-md-3" style="margin-top: 24px;">
        <?= Html::submitButton('搜索', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('重置', ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
