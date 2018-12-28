<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\daterange\DateRangePicker;
use app\services\BaseServices;
use app\services\SupplierServices;
use app\services\PurchaseOrderServices;
use kartik\select2\Select2;
use yii\web\JsExpression;
$url = \yii\helpers\Url::to(['/supplier/search-supplier']);
/* @var $this yii\web\View */
/* @var $model app\models\PurchaseOrderSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="purchase-order-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',

    ]); ?>
    <div class="col-md-1">
        <?= $form->field($model,'sku')->input('text')->label('SKU')?>
    </div>
    <div class="col-md-1">
        <?= $form->field($model,'product_name')->input('text')->label('产品名称')?>
    </div>
    <div class="col-md-2"><?= $form->field($model, 'product_line')->widget(Select2::classname(), [
            'options' => ['placeholder' => '请输入产品线 ...'],
            'data' =>BaseServices::getProductLine(),
            'pluginOptions' => [
                'allowClear' => true,
                'language' => [
                    'errorLoading' => new JsExpression("function () { return 'Waiting...'; }"),
                ],
                'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
                'templateResult' => new JsExpression('function(res) { return res.text; }'),
                'templateSelection' => new JsExpression('function (res) { return res.text; }'),
            ],
        ])->label('产品线')?></div>
     <div class="col-md-2">
        <?= $form->field($model, 'create_supplier_code')->widget(Select2::classname(), [
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
        ])->label('报价供应商');
        ?>
    </div>
    <div class="col-md-1">
        <?= $form->field($model,'status')->dropDownList([''=>'请选择',0=>'待审核',1=>'待拿样',2=>'样品检测中',3=>'已取消',4=>'完成',5=>'审核失败',6=>'样品检测失败'
        ])->label('状态')?>
    </div>
    <div class="form-group col-md-1" style="margin-top: 24px;float:left">
        <?= Html::submitButton('搜索', ['class' => 'btn btn-primary']) ?>
        <?= Html::a('重置', ['index'], ['class' => 'btn btn-default']) ?>


    </div>
    <?php ActiveForm::end(); ?>

</div>
