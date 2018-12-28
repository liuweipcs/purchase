<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\daterange\DateRangePicker;
use app\services\PurchaseOrderServices;
use app\services\BaseServices;
use kartik\select2\Select2;
use app\models\PurchaseUser;
use yii\web\JsExpression;
$url = \yii\helpers\Url::to(['/supplier/search-supplier']);
/* @var $this yii\web\View */
/* @var $model app\models\PurchaseSuggestSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="purchase-suggest-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>


    <!--    <div class="col-md-1">--><?php //$form->field($model, 'warehouse_code')->label('仓库编码') ?><!--</div>-->
    <!---->
    <div class="col-md-1">
        <?= $form->field($model, 'warehouse_code')->widget(Select2::classname(), [
            'options' => ['placeholder' => '请选仓库 ...',],
            'data'=>BaseServices::getWarehouseCode(),
            'pluginOptions' => [
                'placeholder' => 'search ...',
                'allowClear' => true,
                'language' => [
                    'errorLoading' => new JsExpression("function () { return 'Waiting...'; }"),
                ],
                /* 'ajax' => [
                     'url' => $url,
                     'dataType' => 'json',
                     'data' => new JsExpression('function(params) { return {q:params.term}; }')
                 ],*/
                'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
                'templateResult' => new JsExpression('function(res) { return res.text; }'),
                'templateSelection' => new JsExpression('function (res) { return res.text; }'),
            ],
        ])->label('仓库');
        ?>
    </div>
    <div class="col-md-1">
        <?= $form->field($model, 'product_category_id')->widget(Select2::classname(), [
            'options' => ['placeholder' => '请选类别 ...',],
            'data'=>BaseServices::getCategory(),
            'pluginOptions' => [
                'placeholder' => 'search ...',
                'allowClear' => true,
                'language' => [
                    'errorLoading' => new JsExpression("function () { return 'Waiting...'; }"),
                ],
                /* 'ajax' => [
                     'url' => $url,
                     'dataType' => 'json',
                     'data' => new JsExpression('function(params) { return {q:params.term}; }')
                 ],*/
                'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
                'templateResult' => new JsExpression('function(res) { return res.text; }'),
                'templateSelection' => new JsExpression('function (res) { return res.text; }'),
            ],
        ])->label('产品类别');
        ?>
    </div>
    <div class="col-md-1"> <?=$form->field($model, 'state')->dropDownList(PurchaseOrderServices::getProcesStatus(),['value'=>$model->state]) ?></div>

    <div class="col-md-1"><?= $form->field($model, 'sku') ?></div>
    <div class="col-md-1">
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
    <div class="col-md-1"><?= $form->field($model, 'left')->dropDownList([ '1' => '是', '2' => '否'], ['prompt' => '请选择'])->label('是否欠货') ?></div>
    <div class="col-md-1"><?= $form->field($model, 'sourcing_status')->dropDownList(\app\services\SupplierGoodsServices::getProductSourceStatus()+['all'=>'全部'], ['prompt' => '请选择'])->label('货源状态') ?></div>
    <div class="col-md-1"><?= $form->field($model, 'product_status')->dropDownList(\app\services\SupplierGoodsServices::getProductStatus(),['prompt' => '请选择',/*'value'=>$model->product_status*/])->label('产品状态') ?></div>

    <div class="col-md-1">
        <?= $form->field($model, 'amount_1')->textInput(['placeholder' => '20'])->label('数量1(区间)') ?>
    </div>
    <div class="col-md-1">
        <?= $form->field($model, 'amount_2')->textInput(['placeholder' => '50'])->label('数量2(区间)') ?>
    </div>
    <div class="col-md-1">
        <?= $form->field($model, 'buyer_id')->widget(Select2::classname(), [
            'options' => ['placeholder' => '请选择'],
            'data'=>BaseServices::getBuyer(),
            'pluginOptions' => ['width'=>'130px','allowClear' => true,],
        ])->label('采购员') ?>
    </div>
    <div class="col-md-1"><?= $form->field($model, 'sales_import')->dropDownList([ '1' => '是'], ['prompt' => '请选择'])->label('是否销售导入') ?></div>


    <div class="form-group col-md-1" style="margin-top: 24px;">
        <?= Html::submitButton('搜索', ['class' => 'btn btn-primary']) ?>
        <?= Html::a('重置', ['index'],['class' => 'btn btn-default']) ?>
    </div>
    <?php ActiveForm::end(); ?>

</div>
