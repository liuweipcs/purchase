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

    <!--<div class="col-md-1" >
        <label class="control-label" for="purchaseorderpaysearch-applicant">创建时间：</label>
        <?php
/*        $addon = <<< HTML
<span class="input-group-addon">
    <i class="glyphicon glyphicon-calendar"></i>
</span>
HTML;
        echo '<div class="input-group drp-container">';
        echo DateRangePicker::widget([
                'name'=>'PurchaseSuggestSearch[time]',
                'useWithAddon'=>true,
                'convertFormat'=>true,
                'startAttribute' => 'PurchaseSuggestSearch[start_time]',
                'endAttribute' => 'PurchaseSuggestSearch[end_time]',
                'startInputOptions' => ['value' => date('Y-m-d H:i:s',strtotime("last month"))],
                'endInputOptions' => ['value' => date('Y-m-d H:i:s',time())],
                'pluginOptions'=>[
                    'locale'=>['format' => 'Y-m-d H:i:s'],
                ]
            ]).$addon ;
        echo '</div>';
        */?>
    </div>-->

    <div class="col-md-1"> <?=$form->field($model, 'state')->dropDownList(PurchaseOrderServices::getProcesStatus(),['value'=>$model->state]) ?></div>

    <div class="col-md-1"><?= $form->field($model, 'sku') ?></div>

<!--    <div class="col-md-1"><?/*= $form->field($model, 'name')->label('产品名') */?></div>
-->
    <?php // echo $form->field($model, 'supplier_code') ?>

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
        'data'=>PurchaseUser::getBuyerAndGroup(),
        'pluginOptions' => ['width'=>'130px','allowClear' => true,],
    ])->label('采购员') ?>
    </div>
    <div class="col-md-1"><?= $form->field($model, 'sales_import')->dropDownList([ '1' => '是'], ['prompt' => '请选择'])->label('是否销售导入') ?></div>

    <!--    <div class="col-md-1"> <?/*=$form->field($model, 'product_category_id')->dropDownList(\app\services\BaseServices::getCategory(),['prompt'=> '请选择产品分类'])->label('产品分类') */?></div>
    -->
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

    <div class="form-group col-md-1" style="margin-top: 24px;">
        <?= Html::submitButton('搜索', ['class' => 'btn btn-primary']) ?>
        <?= Html::a('重置', ['index'],['class' => 'btn btn-default']) ?>
    </div>
    <?php ActiveForm::end(); ?>

</div>
